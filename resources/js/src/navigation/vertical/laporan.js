export default [{
    title: 'Laporan',
    icon: 'ArchiveIcon',
    tagVariant: 'light-warning',
    resource: 'report',
    action: 'manage',
    children: [{
            title: 'Saldo',
            route: 'report-balance-perdate',
            resource: 'report',
            action: 'manage',
        }, {
            title: 'Mutasi',
            route: 'report-mutation',
            resource: 'report',
            action: 'manage',
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