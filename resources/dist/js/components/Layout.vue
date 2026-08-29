<template>
    <div class="main rows" :class="{'menu-collapsed': menuCollapsed}">
        <LeftMenu :page="page"/>
        <RingLoader v-if="$root.isLoading"/>
        <div class="right-main">
            <Header :page="page"/>
            <div class="panel container mt-4">
                <router-view></router-view>
                <div 
                    class="changeHistory" 
                    v-if="['dice', 'mines', 'bubbles', 'wheel', 'plinko'].indexOf(page) !== -1"
                    @click="changeHistory()"
                >▲</div>
                <History
                    v-show="show_last_wins"
                    v-if="['dice', 'mines', 'bubbles', 'wheel', 'plinko'].indexOf(page) !== -1"
                    :games="$root.games"
                />
            </div>
            <Footer/>
            <notifications position="bottom right"/>
        </div>
        <TonPaymentModal />
    </div>
</template>
<script>
import $ from "jquery";
import Header from "./Header";
import Footer from "./Footer";
import LeftMenu from "./LeftMenu";
import History from "./History";
import RingLoader from "./ui/loader/Ring";
import TonPaymentModal from "./modals/TonPaymentModal";

export default {
    components: {
        LeftMenu,
        Header,
        Footer,
        History,
        RingLoader,
        TonPaymentModal
    },
    data() {
        return {
            page: null,
            show_last_wins: 1,
            darkTheme: false,
            menuCollapsed: false
        }
    },
    beforeMount() {
        this.page = this.$router.currentRoute.name;
        // Load saved menu state
        const saved = localStorage.getItem('leftMenuCollapsed')
        if (saved !== null) {
            this.menuCollapsed = saved === 'true'
        }
    },
    beforeUpdate() {
        this.page = this.$router.currentRoute.name;
    },
    mounted() {
        this.$root.$on('open', this.openPage)
        this.$root.$on('noty', ({ title, text, type }) => {
            this.$notify({title, text, type});
        })
        this.$root.$on('menuToggle', (collapsed) => {
            this.menuCollapsed = collapsed
        })
    },
    methods: {
        openPage(name) {
            this.$router.push({name}).catch(err => {})
        },
        changeHistory() {
            1 == this.show_last_wins ? (this.show_last_wins = 0, localStorage.show_last_wins = 0, $(".changeHistory").html("▼")) : (this.show_last_wins = 1, localStorage.show_last_wins = 1,$(".changeHistory").html("▲"))
        }
    },
    sockets: {
        userMessage(data) {
            if(!this.$root.user) return;

            if(this.$root.user.id == data.user_id) {
                this.$notify({
                    title: data.title,
                    text: data.message,
                    type: data.type
                })
            }
        },
        userRank(data) {
            if(!this.$root.user) return;

            if(this.$root.user.id == data.user_id) {
                this.$root.user.rank = data.rank
                this.$root.user.nextRank = data.nextRank || data.rank
            }
        }
    }
}
</script>

<style>
.changeHistory {
    color: #00000040;
    cursor: pointer;
    margin-top: 15px;
    text-align: center;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
.changeHistory {
    color: #ffffff80!important;
}
</style>