export default [{
    title: 'Admin',
    icon: 'UsersIcon',
    tagVariant: 'light-warning',
    resource: 'user',
    action: 'manage',
    children: [{
            title: 'Users',
            route: 'admin-users',
            resource: 'user',
            action: 'manage',
        },
        {
            title: 'Assign Role',
            route: 'admin-users-role',
            resource: 'user',
            action: 'manage',
        },
        {
            title: 'Accounts',
            route: 'admin-accounts',
            resource: 'account',
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