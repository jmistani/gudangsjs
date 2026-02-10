import axios from '@axios'

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        listFormData(ctx, qParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('accounts/listFormData')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAllAccounts(ctx, qParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('accounts', { params: qParams })
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAccountOptions(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('accounts/all')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAccount(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`accounts/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAccountEdit(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`accounts/edit/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addAccount(ctx, bankData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('accounts', bankData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        updateAccount(ctx, { id, bankaccount }) {
            return new Promise((resolve, reject) => {
                axios
                    .put(`accounts/${id}`, bankaccount)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        removeAccount(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`accounts/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchBankNameOptions(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('accounts/bankNames')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        pageData(ctx, qParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('accounts/pageData', { params: qParams })
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
    },
}