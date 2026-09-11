import { createRouter, createWebHistory } from 'vue-router'
import Home from './pages/Home.vue'
import Halaman from './pages/Halaman.vue'

const routes = [
  {
    path: '/',
    redirect: '/home'
  },
  {
    path: '/home',
    name: 'home',
    component: Home,
    meta: { title: 'Beranda' }
  },
  {
    path: '/halaman',
    name: 'halaman',
    component: Halaman,
    meta: { title: 'Halaman Lain' }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

router.afterEach((to) => {
  document.title = to.meta.title ? `${to.meta.title} - Mada Adventure` : 'Mada Adventure - Outdoor Rental'
})

export default router