import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from '../views/LoginPage.vue'
import DashboardPage from '../views/DashboardPage.vue'
import ReportsPage from '../views/ReportsPage.vue'
import ReportDetail from '../views/ReportsDetail.vue'
import CreateReportPage from '@/views/CreateReportPage.vue'
import ReportsEdit from '@/views/ReportsEdit.vue'
import SupervisorReports from '@/views/SupervisorReports.vue'

const routes = [
  { path: '/', component: LoginPage },
  { path: '/dashboard/:role', component: DashboardPage },
  { path: '/dashboard', redirect: () => {
      const role = localStorage.getItem('role')
      return role ? `/dashboard/${role}` : '/'
    }
  },
  { path: '/reports', component: ReportsPage },
  { path: '/reports/create', component: CreateReportPage },
  { path: '/reports/:id/edit', component: ReportsEdit, props: true },
  { path: '/reports/:id', component: ReportDetail, props: true },
  { path: '/supervisor-reports', component: SupervisorReports },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
