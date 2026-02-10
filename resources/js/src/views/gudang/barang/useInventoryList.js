import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useInventoryList() {
    // Use toast
    const toast = useToast()

    const refInventoryListTable = ref(null)
    const newTagData = ref([])

    // Table Handlers
    const tableColumns = [
        { key: 'code', label: 'Kode', sortable: true },
        { key: 'name', label: 'Nama', sortable: true },
        { key: 'category', label: 'Grup', sortable: true },
        { key: 'unit', label: 'Satuan', sortable: true },
        { key: 'tags', label: 'Tags', sortable: true },
        { key: 'actions', label: 'Actions', sortable: false },
    ]

    const perPage = ref(25)
    const totalInventories = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const bagianFilter = ref(null)
    const tagFilter = ref(null)
    const searchQuery = ref('')

    const dataMeta = computed(() => {
        const localItemsCount = refInventoryListTable.value ? refInventoryListTable.value.localItems.length : 0
        return {
            from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
            to: perPage.value * (currentPage.value - 1) + localItemsCount,
            of: totalInventories.value,
        }
    })

    const sidebarResponse = (success) => {
        let variant = ''
        if (success) {
            variant = 'success'
        } else {
            variant = 'danger'
        }

        toast({
            component: ToastificationContent,
            props: {
                title: 'Success',
                icon: 'BellIcon',
                text: 'success',
                variant,
            },
        })
    }

    const refetchData = () => {
        refInventoryListTable.value.refresh()
    }

    watch([currentPage, perPage, searchQuery, bagianFilter, tagFilter], () => {
        refetchData()
    })

    const fetchAllInventory = (ctx, callback) => {
        store
            .dispatch('app-inventory/fetchAllInventory', {
                q: searchQuery.value,
                perPage: perPage.value,
                page: currentPage.value,
                sortBy: sortBy.value,
                sortDesc: isSortDirDesc.value,
                bagian: bagianFilter.value,
                tag: tagFilter.value
            })
            .then((response) => {
                callback(response.data.payload.data)
                totalInventories.value = response.data.payload.total
            })
            .catch(() => {
                toast({
                    component: ToastificationContent,
                    props: {
                        title: "Error fetching inventories' list",
                        icon: 'AlertTriangleIcon',
                        variant: 'danger',
                    },
                })
            })
    }

    const removeInventory = (id) => {
        if (confirm("Yakin akan menghapus data ?")) {
            store.
            dispatch('app-inventory/removeInventory', { id })
                .then((response) => {
                    if (response.data.payload == "OK") {
                        toast({
                            component: ToastificationContent,
                            props: {
                                title: "Deleted Inventory with id=" + id,
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
                            title: "Error fetching inventories' list",
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

    const resolveInventoryCategoryAndIcon = category => {
        if (category === 'Pabrik') return { variant: 'warning', icon: 'TrelloIcon' }
        if (category === 'Pengangkutan') return { variant: 'success', icon: 'TruckIcon' }
        if (category === 'Lansung Pakai') return { variant: 'info', icon: 'CodeIcon' }
        return { variant: 'secondary', icon: 'DiscIcon' }
    }
    const resolveInventoryTags = status => {
        if (status === 'Partial Payment') return 'primary'
        if (status === 'Paid') return 'danger'
        if (status === 'Downloaded') return 'secondary'
        if (status === 'Draft') return 'warning'
        if (status === 'Sent') return 'info'
        if (status === 'Past Due') return 'success'
        return 'primary'
    }

    return {
        tableColumns,
        newTagData,
        perPage,
        currentPage,
        totalInventories,
        dataMeta,
        perPageOptions,
        searchQuery,
        sortBy,
        isSortDirDesc,
        refInventoryListTable,
        bagianFilter,
        tagFilter,

        removeInventory,
        fetchAllInventory,
        resolveInventoryCategoryAndIcon,
        resolveInventoryTags,

        refetchData,
        sidebarResponse,
    }
}