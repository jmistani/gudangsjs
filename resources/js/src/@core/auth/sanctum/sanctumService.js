import sanctumDefaultConfig from './sanctumDefaultConfig'

export default class SanctumService {
    // Will be used by this service for making API calls
    axiosIns = null

    // sanctumConfig <= Will be used by this service
    sanctumConfig = {...sanctumDefaultConfig }

    // For Refreshing Token
    isAlreadyFetchingAccessToken = false

    // For Refreshing Token
    subscribers = []

    constructor(axiosIns, sanctumOverrideConfig) {
        this.axiosIns = axiosIns
        this.sanctumConfig = {...this.sanctumConfig, ...sanctumOverrideConfig }

        // Request Interceptor
        this.axiosIns.interceptors.request.use(
            config => {
                // Get token from localStorage
                const accessToken = this.getToken()

                // If token is present add it to request's Authorization Header
                if (accessToken) {
                    // eslint-disable-next-line no-param-reassign
                    config.headers.Authorization = `${this.sanctumConfig.tokenType} ${accessToken}`
                }
                return config
            },
            error => Promise.reject(error),
        )

        // Add request/response interceptor
        this.axiosIns.interceptors.response.use(
            response => response,
            error => {
                // const { config, response: { status } } = error
                const { config, response } = error
                const originalRequest = config

                // if (status === 401) {
                if (response && response.status === 401) {
                    window.location.replace("http://gudangsjs.serasijayasejati.com/login")
                }
                return Promise.reject(error)
            },
        )
    }

    onAccessTokenFetched(accessToken) {
        this.subscribers = this.subscribers.filter(callback => callback(accessToken))
    }

    addSubscriber(callback) {
        this.subscribers.push(callback)
    }

    getToken() {
        return localStorage.getItem(this.sanctumConfig.storageTokenKeyName)
    }

    getRefreshToken() {
        return localStorage.getItem(this.sanctumConfig.storageRefreshTokenKeyName)
    }

    setToken(value) {
        localStorage.setItem(this.sanctumConfig.storageTokenKeyName, value)
    }

    setRefreshToken(value) {
        localStorage.setItem(this.sanctumConfig.storageRefreshTokenKeyName, value)
    }

    login(...args) {
        return this.axiosIns.post(this.sanctumConfig.loginEndpoint, ...args)
    }

    register(...args) {
        return this.axiosIns.post(this.sanctumConfig.registerEndpoint, ...args)
    }

    refreshToken() {
        return this.axiosIns.get(this.sanctumConfig.refreshEndpoint)
            // return this.axiosIns.post(this.sanctumConfig.refreshEndpoint, {
            //     refreshToken: this.getRefreshToken(),
            // })
    }
}