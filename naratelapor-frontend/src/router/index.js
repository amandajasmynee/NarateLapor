import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from '../views/LoginPage.vue'
import DashboardPage from '../views/DashboardPage.vue'
import ReportsPage from '../views/ReportsPage.vue' // ⬅️ Tambahin ini

const routes = [
  { path: '/', component: LoginPage },
  { path: '/dashboard', component: DashboardPage },
  { path: '/reports', component: ReportsPage }, // ⬅️ Tambahin ini
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
