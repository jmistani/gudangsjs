import axios from '@axios'

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        fetchAllUsers(ctx, qParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('users', { params: qParams })
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchUserOptions(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('users/all')
                    .then(response => resolve(response))
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
        fetchUserEdit(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`users/edit/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addUser(ctx, bankData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('users', bankData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        updateUser(ctx, { id, bankaccount }) {
            return new Promise((resolve, reject) => {
                axios
                    .put(`users/${id}`, bankaccount)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        removeUser(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`users/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchBankNameOptions(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('users/bankNames')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        pageData(ctx, qParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('users/pageData', { params: qParams })
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
    },
}