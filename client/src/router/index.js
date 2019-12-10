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
    },
    {
        path: '/admin/new',
        name: 'admin-new',
        component: () => import(/* webpackChunkName: "about" */ '../views/admin/New.vue')
    }
]

const router = new VueRouter({
    mode: 'history',
    base: process.env.BASE_URL,
    routes
})

export default router
