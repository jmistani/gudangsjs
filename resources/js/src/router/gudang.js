export default [{
        path: '/inventory/dashboard',
        name: 'inventory-dashboard',
        component: () =>
            import ('@/views/gudang/barang/Dashboard.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Dashboard Inventory',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory',
                    active: true,
                },
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/inventory/list',
        name: 'inventory-list',
        component: () =>
            import ('@/views/gudang/barang/InventoryList.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Inventory List',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                {
                    text: 'List',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/inventory/receive/add',
        name: 'inventory-receive-add',
        component: () =>
            import ('@/views/gudang/terima-add/ReceiveAdd.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Inventory Receive',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                {
                    text: 'Receive',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/inventory/view/:id',
        name: 'inventory-view',
        component: () =>
            import ('@/views/gudang/barang/InventoryView.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Inventory View',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                {
                    text: 'List',
                    to: { name: 'inventory-list' },
                },
                {
                    text: 'View',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/inventory/consume/add',
        name: 'inventory-consume-add',
        component: () =>
            import ('@/views/gudang/consume-add/ConsumeAdd.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Inventory Consume',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                {
                    text: 'Consume',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/inventory/display-inventory-receive/:id',
        name: 'display-inventory-receive',
        component: () =>
            import ('@/views/gudang/barang/DisplayReceiveJournal.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Tampilkan Penerimaan Barang',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                // {
                //     text: 'Receive',
                //     to: { name: 'inventory-receive' },
                // },
                {
                    text: 'Display',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/inventory/display-inventory-consume/:id',
        name: 'display-inventory-consume',
        component: () =>
            import ('@/views/gudang/barang/DisplayConsumeJournal.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Tampilkan Penerimaan Barang',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                // {
                //     text: 'Receive',
                //     to: { name: 'inventory-receive' },
                // },
                {
                    text: 'Display',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/inventory/display-ticket/:id',
        name: 'display-ticket',
        component: () =>
            import ('@/views/gudang/barang/DisplayTicket.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Inventory Ticket Display',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                // {
                //     text: 'Receive',
                //     to: { name: 'inventory-receive' },
                // },
                {
                    text: 'Display',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/inventory/ticket/add',
        name: 'inventory-ticket-add',
        component: () =>
            import ('@/views/gudang/ticket/TicketAdd.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Ticket Add',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                {
                    text: 'Add Ticket',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/inventory/ticket/view/:id',
        name: 'inventory-ticket-view',
        component: () =>
            import ('@/views/gudang/ticket/TicketView.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Ticket View',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                {
                    text: 'View Ticket',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/setting/inventory/group',
        name: 'setting-inventory-group',
        component: () =>
            import ('@/views/pengaturan/GrupBarang.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Pengaturan Grup Barang',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                {
                    text: 'Pengaturan Grup Barang',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
    {
        path: '/setting/inventory/unit',
        name: 'setting-inventory-unit',
        component: () =>
            import ('@/views/pengaturan/UnitBarang.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Pengaturan Unit Barang',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Inventory Dashboard',
                    to: { name: 'inventory-dashboard' },
                },
                {
                    text: 'Pengaturan Unit Barang',
                    active: true
                }
            ],
            resource: 'Inventory',
            action: 'manage',
        },
    },
]