import axios from '@axios'

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        fetchSuppliers(ctx, qParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('suppliers', { params: qParams })
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAllSuppliers(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('suppliers/all')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchSupplier(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`suppliers/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchSupplierEdit(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`suppliersEdit/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addSupplier(ctx, supplierData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('suppliers', supplierData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        updateSupplier(ctx, { id, supplier }) {
            return new Promise((resolve, reject) => {
                axios
                    .put(`suppliers/${id}`, supplier)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        removeSupplier(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`suppliers/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAllAccount(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('journals/all')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAccountExpenses(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('journals/expenses')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
    },
}