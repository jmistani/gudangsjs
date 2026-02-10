import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useTicketView(ticketID) {
    // Use toast
    const toast = useToast()

    const refTicketData = ref(null)
    const refMutationData = ref(null)
    const refMutationListTable = ref(null)

    // Table Handlers
    const tableColumns = [
        { key: 'created_at', label: 'Tanggal', sortable: true },
        { key: 'barang', label: 'Barang', sortable: true },
        { key: 'jumlah', label: 'Jumlah', sortable: true },
        { key: 'harga', label: '@', sortable: true },
        { key: 'subtotal', label: 'subtotal', sortable: true },
    ]

    const perPage = ref(25)
    const totalMutations = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const statusFilter = ref(null)
    const refIsFetching = ref(true)

    store.dispatch('app-inventory/fetchTicket', { id: ticketID })
        .then(response => {
            refTicketData.value = response.data.payload
            refMutationData.value = refTicketData.value.stock_mutations
            totalMutations.value = refTicketData.value.stock_mutations.length
            refIsFetching.value = false
        })
        .catch(error => {
            if (error.response.code === 404) {
                refTicketData.value = undefined
            }
        })

    const dataMeta = computed(() => {
        const localItemsCount = refMutationListTable.value ? refMutationListTable.value.localItems.length : 0
        return {
            from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
            to: perPage.value * (currentPage.value - 1) + localItemsCount,
            of: totalMutations.value,
            subtotal: refMutationData.value.reduce((runningsubTotalCount, i) => runningsubTotalCount + (i.price.amount * i.amount * -1), 0),
            total: refMutationData.value.reduce((runningsubTotalCount, i) => runningsubTotalCount + (i.price.amount * i.amount * -1), 0)
        }
    })

    const refetchData = () => {
        refMutationListTable.value.refresh()
    }

    watch([currentPage, perPage, statusFilter], () => {
        refetchData()
    })

    const resolveMutationIn = mutation => {
        if (mutation > 0) return mutation
        return 0
    }
    const resolveMutationVariant = mutation => {
        if (mutation > 0) return { class: 'success' }
        return { class: 'danger' }
    }
    const resolveMutationOut = mutation => {
        if (mutation < 0) return mutation * -1
        return 0
    }

    return {
        perPage,
        currentPage,
        totalMutations,
        dataMeta,
        perPageOptions,
        sortBy,
        isSortDirDesc,
        refMutationListTable,
        tableColumns,
        statusFilter,

        refIsFetching,
        refTicketData,
        resolveMutationIn,
        resolveMutationOut,
        resolveMutationVariant,
        refetchData,
    }
}