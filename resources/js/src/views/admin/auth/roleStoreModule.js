import axios from '@axios'

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        rolePermission(ctx, username) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`users/roles-permissions/${username}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        saveUser(ctx, userdata) {
            return new Promise((resolve, reject) => {
                axios
                    .post('users/admin-save-user', userdata)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        pageData(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('users/pageData')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },

    },
}