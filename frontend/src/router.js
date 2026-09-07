import { createRouter, createWebHistory } from 'vue-router'
import Home from './pages/Home.vue'
import Halaman from './pages/Halaman.vue'

const routes = [
  // {
  //   path: '/',
  //   redirect: '/home'
  // },
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
  history: createWebHistory(),
  routes
})

router.afterEach((to) => {
  document.title = to.meta.title ? `${to.meta.title} - Outdoor Rental` : 'Outdoor Rental'
})

export default router