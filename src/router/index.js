import { defineAsyncComponent } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import AuthView from '@/views/AuthView.vue'
import LoginView from '../views/auth/LoginView.vue'
import RegisterView from '../views/auth/RegisterView.vue'
import ForgotPasswordView from '../views/auth/ForgotPasswordView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/auth' },
    {
      path: '/auth',
      name: 'auth',
      component: AuthView,
      children: [
        { path: 'login', name: 'login', component: LoginView },
        { path: 'register', name: 'register', component: RegisterView },
        {
          path: 'forgot-password',
          name: 'forgot-password',
          component: ForgotPasswordView,
        },
        { path: '', redirect: '/auth/login' },
      ],
    },
    {
      path: '/home',
      name: 'home',
      redirect: '/documents',
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true, requiresRole: 'Director' },
    },
    {
      path: '/documents',
      name: 'documents',
      component: HomeView,
      meta: { requiresAuth: true },
    },
    {
      path: '/team',
      name: 'team',
      component: () => import('@/views/TeamView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/requests',
      name: 'requests',
      component: () => import('@/views/RequestsView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/models',
      name: 'models',
      component: () => import('@/views/ModelsView.vue'),
      meta: { requiresAuth: true, requiresRole: ['Director', 'Manager'] },
    },
    {
      path: '/settings/configuration',
      name: 'configuration',
      component: () => import('@/views/settings/ConfigurationView.vue'),
      meta: { requiresAuth: true, requiresRole: 'Director' },
    },
    {
      path: '/settings/user-management',
      name: 'user-management',
      component: () => import('@/views/settings/UserManagementView.vue'),
      meta: { requiresAuth: true, requiresRole: 'Director' },
    },
    {
      path: '/settings/request-reasons',
      name: 'request-reasons',
      component: () => import('@/views/settings/RequestReasonsView.vue'),
      meta: { requiresAuth: true, requiresRole: 'Director' },
    },
    {
      path: '/settings/team-management',
      name: 'team-management',
      component: () => import('@/views/settings/TeamManagementView.vue'),
      meta: { requiresAuth: true, requiresRole: 'Director' },
    },
    {
      path: '/settings/support',
      name: 'support',
      component: () => import('@/views/settings/SupportView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/myprofil',
      name: 'profile',
      component: () => import('@/views/ProfilView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/folder/:id',
      name: 'folder',
      component: () => import('@/views/FolderView.vue'),
      props: true,
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to, from, next) => {
  if (to.path.startsWith('/folder/')) {
    const isPageReload = sessionStorage.getItem('pageReloaded')

    if (isPageReload) {
      sessionStorage.removeItem('pageReloaded') // Supprimer le flag
      next({ name: 'documents' })
    } else {
      sessionStorage.setItem('pageReloaded', 'true')
      next() // Permet l'accès à la page /folder/:id
    }
    return
  }

  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiredRoles = to.matched
    .filter(record => record.meta.requiresRole)
    .map(record => record.meta.requiresRole)
    .flat()

  const user = localStorage.getItem('user')
  const token = localStorage.getItem('token')
  const expiration = localStorage.getItem('expiration')
  const userRole = user ? JSON.parse(user).role : null

  if (requiresAuth) {
    if (!token || !expiration || new Date() > new Date(expiration)) {
      next({ name: 'login' })
    } else if (requiredRoles.length && !requiredRoles.includes(userRole)) {
      next({ name: 'home' }) // Redirigez vers la page d'accueil ou une autre page
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
