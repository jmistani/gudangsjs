import axios from '@axios'

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        pageData(ctx, userData) {
            return new Promise((resolve, reject) => {
                axios
                    .get('rolesPageData')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },

    },
}