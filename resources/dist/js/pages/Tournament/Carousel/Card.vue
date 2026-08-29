<template>
    <div class="tournaments-card">
        <div 
            class="tournaments-card__poster" 
            :style="'background-image: url(' + item.preview + ')'"
        />
        <div class="tournaments-card-info">
            <div class="tournaments-card-info__name">
                <a tabindex="0">{{ item.title }}</a>
            </div>
            <div class="tournaments-card-info__fund">
                <div class="tournaments-card-info__fund-cup">
                    <img src="/assets/image/tournament/reward.png" />
                </div>
                <div class="tournaments-card-info__fund-amount">
                    <span>{{ total }}</span>
                </div>
            </div>
            <a 
                class="tournaments-card-info__button tournaments-card-info__button_join blue" 
                tabindex="0"
                @click="$router.push('/tournament/' + item.id + ':' + type)"
            >
                <span>Участвовать</span>
            </a>
            <div class="tournaments-card-info__settings">
                <div v-if="type === 'active'" class="tournaments-card-info__settings-item tournaments-card-info__settings-item_blue-timer">
                    <img src="/assets/image/tournament/clock.svg" />
                    <span>
                        <span>{{ timer }}</span>
                    </span>
                </div>
                <div class="tournaments-card-info__settings-item tournaments-card-info__settings-item_places">
                    <span>{{ item.awards_count }}</span>
                    <img src="/assets/image/tournament/trophy.svg" />
                </div>
                <div class="tournaments-card-info__settings-item tournaments-card-info__settings-item_users">
                    <span>{{ getPlayersCount }}</span>
                    <img src="/assets/image/tournament/users.svg" />
                </div>
            </div>
            <div class="tournaments-card-info__date" v-if="type !== 'active'">
                <img src="/assets/image/tournament/clock.svg"> 
                &nbsp;
                <span>{{ timer }}</span>
            </div>
        </div>
        <div class="tournaments-card-rating">
            <div class="tournaments-card-rating__item" v-for="(_, position) in 5">
                <div class="tournaments-card-rating__item-place">{{ position + 1 }}</div>
                <div class="tournaments-card-rating__item-user">
                    <div class="user-info">
                        <div class="user-info__name">{{ getUserByPosition(position) }}</div>
                    </div>
                </div>
                <div class="tournaments-card-rating__item-points">
                    <span>{{ item.awards[position] }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { parseTime } from '../../../utils/parseTime.js'

export default {
    props: ['item', 'type'],
    data() {
        return {
            timer: null,
            interval: null
        }
    },
    beforeRouteLeave(to, from, next) {
        clearInterval(this.interval)
        next()
    },
    mounted() {
        switch(this.type) {
            case 'active':
                this.startTimer()
            break;
            case 'soon':
                this.timer = `Начало ${this.formatDate(this.item.started_at)}`
            break;
            case 'finished':
                this.timer = `${this.formatDate(this.item.started_at)} — ${this.formatDate(this.item.finished_at)}`
            break;
        }
    },
    methods: {
        startTimer() {
            let seconds = this.getSeconds(this.item.finished_at)
            this.timer = `${Object.values(parseTime2(seconds)).join(':')}`

            this.interval = setInterval(() => {
                if(seconds <= 0) {
                    clearInterval(this.interval)
                    return;
                }
                seconds--

                this.timer = `${Object.values(parseTime2(seconds)).join(':')}`
            }, 1000)
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString("ru", {
                month: "long",
                day: "numeric",
                hour: "numeric",
                minute: "numeric"
            })
        },
        getSeconds(finish) {
            return (Date.parse(new Date(finish)) - Date.parse((new Date).toLocaleString("en-US", {
                timeZone: "Europe/Moscow"
            }))) / 1e3
        },
        getUserByPosition(position) {
            return this.item.leaders[position]?.username || 'Пусто'
        }
    },
    computed: {
        total() {
            return this.item.awards.reduce((total, amount) => total + amount)
        },
        getPlayersCount() {
            return this.item.count[0]?.players || 0
        }
    }
}
</script>