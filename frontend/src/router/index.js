import { createRouter, createWebHistory } from 'vue-router'
import AuthView from '@/views/Auth/AuthView.vue'
import FirstLoginChangePassword from '@/views/Auth/FirstLoginChangePassword.vue'

const requireAuth = (to, from, next) => {
  const token = localStorage.getItem('jwt_token')
  if (token) {
    const user = JSON.parse(localStorage.getItem('user_data'))
    if (to.meta.roles && !to.meta.roles.includes(user.role)) {
      next({ path: '/', replace: true })
    } else {
      next()
    }
  } else {
    next({ path: '/', replace: true })
  }
}


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'auth',
      component: AuthView
    },
    {
      path: '/first-login-change-password',
      name: 'firstLogin',
      component: FirstLoginChangePassword
    },
    {
      path: '/management',
      component: () => import('../views/MainPage.vue'),
      beforeEnter: [requireAuth] /* requireAdmin */,
      children: [
        {
          path: '',
          name: 'DashboardAdmin',
          component: () => import('../views/Admin/DashboardAdmin.vue'),
          meta: {
            forAdmin: true
          }
        },
        {
          path: 'documents',
          name: 'Documents',
          component: () => import('../views/Admin/DocumentsAdmin.vue'),
          meta: {
            forAdmin: true
          }
        },
        {
          path: 'administration',
          name: 'Administration',
          component: () => import('../views/Admin/AdministrationView.vue'),
          meta: {
            forAdmin: true
          }
        }
      ]
    },
    {
      path: '/home',
      component: () => import('../views/MainPage.vue'),
      beforeEnter: requireAuth,
      children: [
        {
          path: '',
          name: 'Dashboard',
          component: () => import('../views/DashBoard.vue'),
          meta: {
            forAdmin: false
          }
        },
        {
          path: 'documents',
          name: 'Mes Documents',
          component: () => import('../views/DocumentsView.vue'),
          meta: {
            forAdmin: false
          }
        },
        {
          path: 'activity',
          name: 'Journal',
          component: () => import('../views/JournalView.vue'),
          meta: {
            forAdmin: false
          }
        },
        {
          path: 'requests',
          name: 'Mes demandes',
          component: () => import('../views/RequestView.vue'),
          meta: {
            forAdmin: false
          }
        },
        {
          path: 'myteam',
          name: 'Mon équipe',
          component: () => import('../views/MyTeam.vue'),
          meta: {
            forAdmin: false,
            roles: ['Manager']
          }
        },
        {
          path: 'settings',
          name: 'Paramètres',
          component: () => import('../views/SettingsView.vue'),
          meta: {
            forAdmin: false
          }
        }
      ]
    },
    {
      path: '/:pathMatch(.*)',
      component: () => import('../views/NotFoundView.vue')
    }
  ]
})

router.beforeEach((to, from, next) => {
  if (to.path !== '/') {
    requireAuth(to, from, next)
  } else {
    next()
  }
})

export default router
