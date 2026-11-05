import Vue from 'vue'
import axios from 'axios'

const axiosIns = axios.create({
  // Use the current domain.
  // This works for both local and V2 production.
//  baseURL: 'https://gudangsjsv2.serasijayasejati.com/api',
  baseURL: '/api',

  timeout: 1200000,

  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },

  withCredentials: false,
})

// ---------------------------------------------------------
// REQUEST INTERCEPTOR
// Attach JWT access token to every API request
// ---------------------------------------------------------
axiosIns.interceptors.request.use(
  config => {
    const token =
      localStorage.getItem('accessToken') ||
      sessionStorage.getItem('accessToken')

    if (token && token !== 'undefined' && token !== 'null') {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  error => Promise.reject(error),
)

// ---------------------------------------------------------
// RESPONSE INTERCEPTOR
// Handle expired / invalid JWT sessions globally
// ---------------------------------------------------------
axiosIns.interceptors.response.use(
  response => response,

  error => {
    const status = error.response && error.response.status

    if (status === 401) {
      // Remove expired/invalid authentication data
      localStorage.removeItem('accessToken')
      localStorage.removeItem('refreshToken')
      localStorage.removeItem('token_type')
      localStorage.removeItem('userData')

      // Prevent redirect loop if we are already on login
      if (window.location.pathname !== '/login') {
        window.location.replace('/login')
      }
    }

    return Promise.reject(error)
  },
)

Vue.prototype.$http = axiosIns

export default axiosIns
