import Vue from 'vue'

// axios
import axios from 'axios'


const axiosIns = axios.create({
    // You can add your headers here ===
    // === === === === === === === === === ==
    baseURL: 'https://gudangsjs.serasijayasejati.com/api/',
    timeout: 1200000,
    // headers: {'X-Custom-Header': 'foobar'}
    withCredentials: true,
})
axiosIns.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
Vue.prototype.$http = axiosIns

export default axiosIns