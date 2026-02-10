export default [{
        path: '/inventory/report/balance-perdate',
        name: 'report-balance-perdate',
        component: () =>
            import ('@/views/gudang/report/InventoriesBalance.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Saldo Barang',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Gudang',
                    to: { name: 'inventory-dashboard' }
                },
                {
                    text: 'Laporan',
                    active: true
                },
            ],
            resource: 'report',
            action: 'manage',
        },
    },
    {
        path: '/inventory/report/mutation',
        name: 'report-mutation',
        component: () =>
            import ('@/views/gudang/report/InventoriesMutationReport.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Mutasi Barang',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Gudang',
                    to: { name: 'inventory-dashboard' }
                },
                {
                    text: 'Laporan',
                    active: true
                },
            ],
            resource: 'report',
            action: 'manage',
        },
    },
]