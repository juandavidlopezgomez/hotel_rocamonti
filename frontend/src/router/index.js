import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../views/HomePage.vue'
import ReservarPage from '../views/ReservarPage.vue'
import HabitacionesPage from '../views/HabitacionesPage.vue'
import ResumenPage from '../views/ResumenPage.vue'
import PagoPage from '../views/PagoPage.vue'
import ConfirmacionPage from '../views/ConfirmacionPage.vue'
import LoginAdmin from '../views/admin/LoginAdmin.vue'
import DashboardAdminPro from '../views/admin/DashboardAdminPro.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomePage
    },
    {
      path: '/reservar',
      name: 'reservar',
      component: ReservarPage
    },
    {
      path: '/habitaciones',
      name: 'habitaciones',
      component: HabitacionesPage
    },
    {
      path: '/resumen',
      name: 'resumen',
      component: ResumenPage
    },
    {
      path: '/pago',
      name: 'pago',
      component: PagoPage
    },
    {
      path: '/confirmacion',
      name: 'confirmacion',
      component: ConfirmacionPage
    },
    {
      path: '/admin/login',
      name: 'admin-login',
      component: LoginAdmin
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: DashboardAdminPro,
      meta: { requiresAuth: true }
    }
  ]
})

export default router
