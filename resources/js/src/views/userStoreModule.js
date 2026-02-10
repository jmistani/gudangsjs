import axios from '@axios'

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        addUser(ctx, userData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('register', userData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchUsers(ctx, queryParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('users/all', { params: queryParams })
                    .then(response => resolve(response.data))
                    .catch(error => reject(error))
            })
        },
        fetchUser(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`users/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        deleteUser(ctx, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`users/${payload}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        userExist(ctx, userName) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`users/exist/${userName}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
    },
}