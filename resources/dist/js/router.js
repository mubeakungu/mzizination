import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from './pages/Home/Home'
import Dice from './pages/Dice'
import Mines from './pages/Mines'
import Bubbles from './pages/Bubbles'
import Wheel from './pages/Wheel'
import Plinko from './pages/Plinko'
import Slots from './pages/Slots'
import Lives from './pages/Lives'
import SlotsGame from './pages/SlotsGame'
import LivesGame from './pages/LivesGame'
import Bonus from './pages/Bonus/Bonus'
import Ref from './pages/Ref'
import Faq from './pages/Faq'
import Profile from './pages/Profile'
import Payment from './pages/Payment'
import Withdraw from './pages/Withdraw'
import Tournament from './pages/Tournament/Tournament'
import Tournaments from './pages/Tournament/Tournaments'

Vue.use(VueRouter)

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home
    },
    {
        path: '/profile',
        name: 'profile',
        component: Profile
    },
    {
        path: '/dice',
        name: 'dice',
        component: Dice
    },
    {
        path: '/mines',
        name: 'mines',
        component: Mines
    },
    {
        path: '/bubbles',
        name: 'bubbles',
        component: Bubbles
    },
    {
        path: '/wheel',
        name: 'wheel',
        component: Wheel
    },
    {
        path: '/plinko',
        name: 'plinko',
        component: Plinko
    },
    {
        path: '/slots',
        name: 'slots',
        component: Slots
    },
    {
        path: '/live',
        name: 'live',
        component: Lives
    },
    {
        path: '/slots/game/:id/:type?',
        name: 'slots',
        component: SlotsGame
    },
    {
        path: '/live/game/:id',
        name: 'live',
        component: LivesGame
    },
    {
        path: '/ref',
        name: 'ref',
        component: Ref
    },
    {
        path: '/bonus',
        name: 'bonus',
        component: Bonus
    },
    {
        path: '/pay',
        name: 'payment',
        component: Payment
    },
    {
        path: '/withdraw',
        name: 'withdraw',
        component: Withdraw
    },
    {
        path: '/faq',
        name: 'faq',
        component: Faq
    },
    {
        path: '/tournaments',
        name: 'tournaments',
        component: Tournaments
    },
    {
        path: '/tournament/:id',
        name: 'tournament',
        component: Tournament
    }
]

const router = new VueRouter({
    mode: 'history',
    base: process.env.BASE_URL,
    routes
})

export default router
