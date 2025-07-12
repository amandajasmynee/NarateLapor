import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from '../views/LoginPage.vue'
import DashboardPage from '../views/DashboardPage.vue'
import ReportsPage from '../views/ReportsPage.vue'
import ReportDetail from '../views/ReportsDetail.vue'

const routes = [
  { path: '/', component: LoginPage },
  { path: '/dashboard', component: DashboardPage },
  { path: '/reports', component: ReportsPage },
  { path: '/reports/:id', component: ReportDetail, props: true },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
