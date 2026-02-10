import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUserList() {
    // Use toast
    const toast = useToast()

    const refUserListTable = ref(null)
    const newTagData = ref([])

    // Table Handlers
    const tableColumns = [
        { key: 'name', label: 'Name', sortable: true },
        { key: 'roles', label: 'Role', sortable: true },
        { key: 'username', label: 'Username', sortable: true },
        { key: 'actions', label: 'Actions', sortable: false },
    ]

    const perPage = ref(25)
    const totalUsers = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const bagianFilter = ref(null)
    const roleFilter = ref(null)
    const searchQuery = ref('')

    const dataMeta = computed(() => {
        const localItemsCount = refUserListTable.value ? refUserListTable.value.localItems.length : 0
        return {
            from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
            to: perPage.value * (currentPage.value - 1) + localItemsCount,
            of: totalUsers.value,
        }
    })

    const refetchData = () => {
        refUserListTable.value.refresh()
    }

    watch([currentPage, perPage, searchQuery, bagianFilter], () => {
        refetchData()
    })

    const fetchAllUsers = (ctx, callback) => {
        store
            .dispatch('app-users/fetchAllUsers', {
                q: searchQuery.value,
                perPage: perPage.value,
                page: currentPage.value,
                sortBy: sortBy.value,
                sortDesc: isSortDirDesc.value,
                role: bagianFilter.value,
            })
            .then((response) => {
                callback(response.data.payload.data)
                totalUsers.value = response.data.payload.total
            })
            .catch(() => {
                toast({
                    component: ToastificationContent,
                    props: {
                        title: "Error fetching user list",
                        icon: 'AlertTriangleIcon',
                        variant: 'danger',
                    },
                })
            })
    }

    const removeUser = (id) => {
        if (confirm("Yakin akan menghapus data ?")) {
            store.
            dispatch('app-users/removeUser', { id })
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
        totalUsers,
        dataMeta,
        perPageOptions,
        searchQuery,
        sortBy,
        isSortDirDesc,
        refUserListTable,
        bagianFilter,
        roleFilter,

        removeUser,
        fetchAllUsers,

        refetchData,
    }
}