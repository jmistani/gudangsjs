import axios from '@axios'

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        fetchAllInventory(ctx, qParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventories', { params: qParams })
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchTickets(ctx, qParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('tickets', { params: qParams })
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchInventoryOptions(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventories/all')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchInventory(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`inventories/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        recalculateStock(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`inventories/recalculateStock/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchInventoryEdit(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`inventoriesEdit/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addInventoryImage(ctx, { id, Image }) {
            return new Promise((resolve, reject) => {
                axios
                    .post(`inventories/${id}/add-image/`, Image)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addInventory(ctx, inventoryData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('inventories', inventoryData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        updateInventory(ctx, { id, inventory }) {
            return new Promise((resolve, reject) => {
                axios
                    .put(`inventories/${id}`, inventory)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        removeInventory(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`inventories/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAllVendor(ctx, queryParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('vendors', { params: queryParams })
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchVendorOptions(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('vendors/all')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAllTickets(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('tickets/all')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAccountOptions(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('journals/all')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchAccountExpenseOptions(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('journals/expenses')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchTicketOptions(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('tickets/all')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addAccount(ctx, ledgerData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('journals', ledgerData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        removeAccount(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`journals/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addVendor(ctx, vendorData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('vendors', vendorData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        removeVendor(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`vendors/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        receiveInventory(ctx, receiveData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('inventories/receive', receiveData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        consumeInventory(ctx, consumeData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('inventories/consume', consumeData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },

        fetchInventoryWithMutation(ctx, queryParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventories/transaction', { params: queryParams })
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addTagInventory(ctx, tagData) {
            return new Promise((resolve, reject) => {
                axios
                    .post(`inventories/tag/add`, tagData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        removeTagInventory(ctx, tagData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('inventories/tag/remove', tagData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        formReceiveData(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventories/formReceiveData')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        listFormData(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventories/listFormData')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        consumeFormData(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventories/consumeFormData')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        assignTicketToStockMutation(ctx, aParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`inventories/assignTicket/${aParams.stockMutationID}/${aParams.ticketID}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        removeTicketFromStockMutation(ctx, aParams) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`inventories/removeTicket/${aParams.stockMutationID}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchReceivesDB(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventories/receivesDB')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchConsumesDB(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventories/consumesDB')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchReceives(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventories/receives')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchConsumes(ctx) {
            return new Promise((resolve, reject) => {
                axios
                    .get('inventories/consumes')
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        inventoryReceivePDF(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    ({
                        url: `inventory-receives/showPDF/${id}`,
                        method: 'GET',
                        // { responseType: 'blob' }
                        // responseType: "application/pdf"
                        // responseType: 'blob'
                    })
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        fetchTicket(ctx, { id }) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`tickets/${id}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        addTicket(ctx, ticketData) {
            return new Promise((resolve, reject) => {
                axios
                    .post('tickets', ticketData)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
        closeTicket(ctx, ticketID) {
            return new Promise((resolve, reject) => {
                axios
                    .get(`tickets/close/${ticketID}`)
                    .then(response => resolve(response))
                    .catch(error => reject(error))
            })
        },
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