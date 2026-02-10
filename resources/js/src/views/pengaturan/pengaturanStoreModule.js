import axios from '@axios'

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        fetchAllGroups(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventory-groups/all')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        removeGroup(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`inventory-groups/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addGroup(ctx, groupData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('inventory-groups', groupData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAllUnits(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventory-units/all')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        removeUnit(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`inventory-units/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addUnit(ctx, unitData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('inventory-units', unitData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
    },
}