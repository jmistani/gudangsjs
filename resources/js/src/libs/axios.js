import Vue from 'vue'
import axios from 'axios'

const axiosIns = axios.create({
  baseURL: 'https://gudangsjs.local/api/',
  timeout: 1200000,

  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },

  withCredentials: false,
})

// Attach JWT Bearer token to every API request
axiosIns.interceptors.request.use(
  config => {
    const token = localStorage.getItem('accessToken')

    if (token && token !== 'undefined' && token !== 'null') {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  error => Promise.reject(error),
)

Vue.prototype.$http = axiosIns

export default axiosIns
