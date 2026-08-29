import Vue from 'vue'
import Layout from './components/Layout'
import router from './router'
import axios from 'axios'
import VueAxios from 'vue-axios'
import Notifications from 'vue-notification'
import VueSocketIO from 'vue-socket.io'
import Clipboard from 'v-clipboard'
import MQ from 'vue-match-media/src'
import { ModalPlugin, BPaginationNav } from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import { VBTooltip } from 'bootstrap-vue'

const moment = require('moment')
require('moment/locale/ru')

import { directive as onClickaway } from 'vue-clickaway'
Vue.directive('on-clickaway', onClickaway)

import VueObserveVisibility from "vue-observe-visibility";
Vue.use(VueObserveVisibility);

Vue.component('b-pagination-nav', BPaginationNav)
Vue.use(VueAxios, axios)
Vue.use(Notifications)
Vue.use(ModalPlugin)
Vue.use(Clipboard)
Vue.use(MQ)
Vue.use(require('vue-moment'), {
    moment
})

Vue.directive('b-tooltip', VBTooltip)

Vue.config.productionTip = false

let port = '8443';
Vue.use(new VueSocketIO({
    connection: `${window.location.protocol}//${window.location.hostname}:${port}`
}));

Vue.prototype.$valid = function (t) {
    return t.toString().replace(/[,]/g, ".").replace(/[^\d,.]*/g, "").replace(/([,.])[,.]+/g, "$1").replace(/^[^\d]*(\d+([.,]\d{0,2})?).*$/g, "$1")
}

axios.defaults.withCredentials = true;
axios.defaults.baseURL = process.env.VUE_APP_API;

const app = new Vue({
    data() {
        return {
            user: null,
            config: {},
            isLoading: true,
            animate: {
                countup: 0,
                odometer: 0
            },
            online: 0,
            games: []
        }
    },
    async mounted() {
        localStorage.animateBalance == 'countup'
            ? this.animate.countup  = 1
            : this.animate.odometer = 1

        await this.init()
    },
    methods: {
        init() {
            this.$root.axios.post('/user/init')
            .then(response => {
                const {data} = response;
                this.isLoading = false;
                
                if(data.user) {
                    this.config = data.config
                    this.user = data.user
                }
            })
        }
    },
    sockets: {
        online(data) {
            this.online = data
        },
        history(data) {
            this.games = data
        }
    },
    mq: {
        phone: '(max-width: 768px)',
        tablet: '(max-width: 1024px)',
        desktop: '(min-width: 992px)'
    },
    router,
    render: h => h(Layout)
})

app.$mount('#root')
