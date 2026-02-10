export default [{
    title: 'Pengaturan',
    icon: 'SettingsIcon',
    tagVariant: 'light-warning',
    resource: 'inventory',
    children: [{
            title: 'Grup Barang',
            route: 'setting-inventory-group',
            resource: 'inventory',
            action: 'manage grup',
        }, {
            title: 'Unit Barang',
            route: 'setting-inventory-unit',
            resource: 'inventory',
            action: 'manage satuan',
        },
        // {
        //     title: 'Daftar Barang',
        //     route: 'inventory-list',
        // },
        // {
        //     title: 'Terima Barang',
        //     route: 'inventory-receive-add',
        // },
        // {
        //     title: 'Keluar Barang',
        //     route: 'inventory-consume-add',
        // },
    ],

}, ]
