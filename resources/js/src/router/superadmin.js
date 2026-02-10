export default [{
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

]