export default [{
        header: 'Barang & Stok',
    },
    {
        title: 'Gudang',
        icon: 'TrelloIcon',
        tagVariant: 'light-warning',
        children: [{
                title: 'Dashboard',
                route: 'inventory-dashboard',
                resource: 'inventory',
                action: 'read',
            }, {
                title: 'Daftar Barang',
                route: 'inventory-list',
                resource: 'inventory',
                action: 'manage',
            },
            {
                title: 'Terima Barang',
                route: 'inventory-receive-add',
                resource: 'inventory',
                action: 'manage',
            },
            {
                title: 'Keluar Barang',
                route: 'inventory-consume-add',
                resource: 'inventory',
                action: 'manage',
            },
            {
                title: 'Tiket',
                route: 'inventory-ticket-add',
                resource: 'inventory',
                action: 'manage',
            },
        ],
    },
]