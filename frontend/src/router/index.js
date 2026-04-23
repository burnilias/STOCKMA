import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
    meta: { guest: true },
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginPage.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/RegisterPage.vue'),
    meta: { guest: true },
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/views/ForgotPasswordView.vue'),
    meta: { guest: true },
  },
  {
    path: '/forbidden',
    name: 'forbidden',
    component: () => import('@/views/ForbiddenView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/dashboard',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/views/DashboardView.vue'),
      },
      {
        path: '/stock-status',
        name: 'stock-status',
        component: () => import('@/views/StockStatusView.vue'),
      },
      {
        path: '/products',
        name: 'products',
        component: () => import('@/views/ProductListView.vue'),
      },
      {
        path: '/products/new',
        name: 'product-new',
        component: () => import('@/views/ProductFormView.vue'),
        meta: { admin: true },
      },
      {
        path: '/products/:id/edit',
        name: 'product-edit',
        component: () => import('@/views/ProductFormView.vue'),
        meta: { admin: true },
      },
      {
        path: '/stock-entry',
        name: 'stock-entry',
        component: () => import('@/views/StockEntryView.vue'),
      },
      {
        path: '/stock-exit',
        name: 'stock-exit',
        component: () => import('@/views/StockExitView.vue'),
      },
      {
        path: '/categories',
        name: 'categories',
        component: () => import('@/views/CategoriesPage.vue'),
        meta: { admin: true },
      },
      {
        path: '/alertes',
        name: 'alertes',
        component: () => import('@/views/AlertesPage.vue'),
        meta: { admin: true },
      },
      {
        path: '/movements',
        name: 'movements',
        component: () => import('@/views/MovementHistoryView.vue'),
      },
      {
        path: '/low-stock',
        name: 'low-stock',
        component: () => import('@/views/LowStockView.vue'),
      },
      {
        path: '/users',
        name: 'users',
        component: () => import('@/views/UserManagementView.vue'),
        meta: { admin: true },
      },
      {
        path: '/super-admin',
        name: 'super-admin',
        component: () => import('@/views/SuperAdminView.vue'),
        meta: { superAdmin: true },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  const ok = auth.isAuthenticated

  const requiresAuth = to.matched.some((r) => r.meta.requiresAuth)
  const requiresAdmin = to.matched.some((r) => r.meta.admin)
  const requiresSuperAdmin = to.matched.some((r) => r.meta.superAdmin)
  const isGuest = to.matched.some((r) => r.meta.guest)

  if (isGuest && ok) {
    return next({ name: 'dashboard' })
  }
  if (requiresAuth && !ok) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }
  if (requiresAdmin && !auth.isAdmin && !auth.isSuperAdmin) {
    return next({ name: 'forbidden' })
  }
  if (requiresSuperAdmin && !auth.isSuperAdmin) {
    return next({ name: 'forbidden' })
  }
  next()
})

export default router
