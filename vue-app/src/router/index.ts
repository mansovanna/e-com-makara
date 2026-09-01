import AdminLayout from '@/layouts/AdminLayout.vue'
import CategoryView from '@/views/admin/CategoryView.vue'
import DashboardView from '@/views/admin/DashboardView.vue'
import FoodView from '@/views/admin/FoodView.vue'
import OrderView from '@/views/admin/OrderView.vue'
import TablesView from '@/views/admin/TablesView.vue'
import LoginView from '@/views/auth/LoginView.vue'
import Card from '@/views/customer/Card.vue'
import CheckouteView from '@/views/customer/CheckouteView.vue'
import Favorite from '@/views/customer/Favorite.vue'
import HomeView from '@/views/customer/HomeView.vue'
import Notification from '@/views/customer/Notification.vue'
import OrderItems from '@/views/customer/OrderItems.vue'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'customer',
      component: () => import('@/layouts/layoutScreen.vue'),
      children: [
        {
          path: '',
          name: 'home',
          component: HomeView,
        },
        {
          path: '/favorite',
          name: 'favorite',
          component: Favorite,
        },
        {
          path: '/notification',
          name: 'notification',
          component: Notification,
        },
        {
          path: '/card',
          name: 'card',
          component: Card,
        },
        {
          path: '/orders',
          name: 'orders',
          component: OrderItems,
        },
        {
          path: '/checkout',
          name: 'checkout',
          component: CheckouteView,
        },
      ],
    },
    {
      path: '/admin/',
      name: 'admin',
      component: AdminLayout,
      meta: {
        requiresAuth: true,
        role: 'admin',
      },
      children: [
        {
          path: '',
          name: 'dashboard',
          component: DashboardView,
        },
        {
          path: '/food',
          name: 'food',
          component: FoodView,
        },
        {
          path: '/categories',
          name: 'categories',
          component: CategoryView,
        },
        {
          path: '/table',
          name: 'table',
          component: TablesView,
        },
        {
          path: '/order',
          name: 'order',
          component: OrderView,
        },
      ],
    },

    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
  ],
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const userString = localStorage.getItem('user')
  const user = userString ? JSON.parse(userString) : null

  if (to.meta.requiresAuth && !token) {
    return next('/login')
  }

  if (to.meta.role === 'admin' && user?.role !== 'admin') {
    return next('/')
  }

  next()
})

export default router
