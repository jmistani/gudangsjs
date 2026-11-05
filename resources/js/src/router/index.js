import axios from '@axios'
import Vue from 'vue'
import VueRouter from 'vue-router'
import admin from './admin.js'
import gudang from './gudang.js'
import laporan from './laporan.js'
import NProgress from 'nprogress'

Vue.use(VueRouter)

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,

  scrollBehavior() {
    return { x: 0, y: 0 }
  },

  routes: [
    {
      path: '/',
      name: 'home',
      component: () =>
        import('@/views/Home.vue'),
      meta: {
        requiresAuth: true,
        pageTitle: 'Home',
        breadcrumb: [
          {
            text: 'Home',
            active: true,
          },
        ],
      },
    },

    {
      path: '/second-page',
      name: 'second-page',
      component: () =>
        import('@/views/SecondPage.vue'),
      meta: {
        requiresAuth: true,
        pageTitle: 'Second Page',
        breadcrumb: [
          {
            text: 'Second Page',
            active: true,
          },
        ],
      },
    },

    {
      path: '/login',
      name: 'login',
      component: () =>
        import('@/views/Login.vue'),
      meta: {
        layout: 'full',
      },
    },

    {
      path: '/adduser',
      name: 'add-user',
      requiresAuth: true,
      component: () =>
        import('@/views/AddUser.vue'),
      meta: {
        layout: 'full',
      },
    },

    // {
    //     path: '/userrole',
    //     name: 'user-role',
    //     component: () =>
    //         import('@/views/UserRole.vue'),
    //     meta: {
    //         pageTitle: 'Home',
    //         breadcrumb: [
    //             {
    //                 text: 'Home',
    //                 to: { name: 'home' }
    //             },
    //             {
    //                 text: 'User',
    //             },
    //             {
    //                 text: 'Add Role',
    //                 active: true
    //             }
    //         ],
    //     },
    // },

    {
      path: '/error-404',
      name: 'error-404',
      component: () =>
        import('@/views/error/Error404.vue'),
      meta: {
        layout: 'full',
      },
    },

    {
      path: '*',
      redirect: 'error-404',
    },

    ...admin,
    ...gudang,
    ...laporan,
  ],
})

// ? For splash screen
// Remove afterEach hook if you are not using splash screen
router.afterEach(() => {
  // Remove initial loading
  const appLoading = document.getElementById('loading-bg')

  if (appLoading) {
    appLoading.style.display = 'none'
  }
})

// Authentication guard
router.beforeEach((to, from, next) => {

  // Get token from either persistent login or session login
  const token =
        localStorage.getItem('accessToken') ||
        sessionStorage.getItem('accessToken')

  if (to.meta.requiresAuth) {

    // No token at all
    if (!token) {
      return next({
        name: 'login',
        query: {
          redirect: to.fullPath,
        },
      })
    }

    // Validate JWT expiration
    try {
      const payload = JSON.parse(
        atob(
          token
            .split('.')[1]
            .replace(/-/g, '+')
            .replace(/_/g, '/')
        )
      )

      // Token expired
      if (payload.exp && payload.exp * 1000 <= Date.now()) {

        localStorage.removeItem('accessToken')
        sessionStorage.removeItem('accessToken')

        localStorage.removeItem('userData')
        localStorage.removeItem('token_type')
        localStorage.removeItem('refreshToken')

        return next({
          name: 'login',
          query: {
            redirect: to.fullPath,
          },
        })
      }

    } catch (error) {

      // Invalid JWT
      localStorage.removeItem('accessToken')
      sessionStorage.removeItem('accessToken')

      localStorage.removeItem('userData')
      localStorage.removeItem('token_type')
      localStorage.removeItem('refreshToken')

      return next({
        name: 'login',
        query: {
          redirect: to.fullPath,
        },
      })
    }
  }

  if (to.name) {
    NProgress.start()
  }

  next()
})

export default router
