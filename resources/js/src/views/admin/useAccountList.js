import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useAccountList() {
    // Use toast
    const toast = useToast()

    const refAccountListTable = ref(null)
    const newTagData = ref([])

    // Table Handlers
    const tableColumns = [
        { key: 'code', label: 'Kode', sortable: true },
        { key: 'name', label: 'Nama', sortable: true },
        { key: 'balance', label: 'Saldo', sortable: true },
        { key: 'actions', label: 'Actions', sortable: false },
    ]

    const perPage = ref(25)
    const totalAccounts = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const searchQuery = ref('')

    const dataMeta = computed(() => {
        const localItemsCount = refAccountListTable.value ? refAccountListTable.value.localItems.length : 0
        return {
            from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
            to: perPage.value * (currentPage.value - 1) + localItemsCount,
            of: totalAccounts.value,
        }
    })

    const refetchData = () => {
        refAccountListTable.value.refresh()
    }

    watch([currentPage, perPage, searchQuery], () => {
        refetchData()
    })

    const fetchAllAccounts = (ctx, callback) => {
        store
            .dispatch('app-accounts/fetchAllAccounts', {
                q: searchQuery.value,
                perPage: perPage.value,
                page: currentPage.value,
                sortBy: sortBy.value,
                sortDesc: isSortDirDesc.value,
            })
            .then((response) => {
                callback(response.data.payload.data)
                totalAccounts.value = response.data.payload.total
            })
            .catch(() => {
                toast({
                    component: ToastificationContent,
                    props: {
                        title: "Error fetching account list",
                        icon: 'AlertTriangleIcon',
                        variant: 'danger',
                    },
                })
            })
    }

    const removeAccount = (id) => {
        if (confirm("Yakin akan menghapus data ?")) {
            store.
            dispatch('app-accounts/removeAccount', { id })
                .then((response) => {
                    if (response.data.payload == "OK") {
                        toast({
                            component: ToastificationContent,
                            props: {
                                title: "Deleted",
                                icon: 'CheckIcon',
                                variant: 'success',
                            },
                        })
                    }
                    refetchData()
                })
                .catch(() => {
                    toast({
                        component: ToastificationContent,
                        props: {
                            title: "Error fetching bank accounts' list",
                            icon: 'AlertTriangleIcon',
                            variant: 'danger',
                        },
                    })
                })
        }
    }

    // *===============================================---*
    // *--------- UI ---------------------------------------*
    // *===============================================---*


    return {
        tableColumns,
        newTagData,
        perPage,
        currentPage,
        totalAccounts,
        dataMeta,
        perPageOptions,
        searchQuery,
        sortBy,
        isSortDirDesc,
        refAccountListTable,

        removeAccount,
        fetchAllAccounts,

        refetchData,
    }
}