export default [{
        path: '/admin/accounts',
        name: 'admin-accounts',
        component: () =>
            import ('@/views/admin/Account.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Account List',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Accounts',
                    active: true,

                },
            ],
            resource: 'account',
            action: 'manage',
        },

    },
    {
        path: '/admin/users',
        name: 'admin-users',
        component: () =>
            import ('@/views/admin/auth/User.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'User List',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Users',
                    active: true,

                },
            ],
            resource: 'user',
            action: 'manage',
        },

    },
    {
        path: '/admin/users/role',
        name: 'admin-users-role',
        component: () =>
            import ('@/views/admin/auth/UserRole.vue'),
        meta: {
            requiresAuth: true,
            pageTitle: 'Assign Role',
            breadcrumb: [{
                    text: 'Home',
                    to: { name: 'home' }
                },
                {
                    text: 'Assign Role',
                    active: true,

                },
            ],
            resource: 'user',
            action: 'manage',
        },
    }
]