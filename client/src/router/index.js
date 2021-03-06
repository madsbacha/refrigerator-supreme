import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import RealHome from '../views/RealHome'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home
  },
  {
    path: '/realhome',
    name: 'realhome',
    component: RealHome
  },
  {
    path: '/login',
    name: 'login',
    component: () => import(/* webpackChunkName: "login" */ '../views/Login.vue')
  },
  {
    path: '/logout',
    name: 'logout',
    beforeEnter (to, from, next) {
      window.localStorage.removeItem('apollo-token')
      next({ name: 'login' })
    }
  }
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})

export default router
