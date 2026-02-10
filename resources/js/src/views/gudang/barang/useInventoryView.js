import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useInventoryView() {
    // Use toast
    const toast = useToast()

    const refMutationListTable = ref(null)

    // Table Handlers
    const tableColumns = [
        { key: 'tanggal', label: 'Tanggal', sortable: true },
        { key: 'masuk', label: 'Masuk', sortable: true },
        { key: 'keluar', label: 'Keluar', sortable: true },
        { key: 'second_reference_type', label: 'Keterangan', sortable: true },
        { key: 'giver', label: 'Dari', sortable: true },
        { key: 'receiver', label: 'Diterima', sortable: true },
    ]

    const perPage = ref(20)
    const totalInventories = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const inventoryID = ref('inventoryID')
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const statusFilter = ref(null)

    const dataMeta = computed(() => {
        const localItemsCount = refMutationListTable.value ? refMutationListTable.value.localItems.length : 0
        return {
            from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
            to: perPage.value * (currentPage.value - 1) + localItemsCount,
            of: totalInventories.value,
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
        inventoryID,
        perPage,
        currentPage,
        totalInventories,
        dataMeta,
        perPageOptions,
        sortBy,
        isSortDirDesc,
        refMutationListTable,
        tableColumns,
        statusFilter,

        resolveMutationIn,
        resolveMutationOut,
        resolveMutationVariant,
        refetchData,
    }
}