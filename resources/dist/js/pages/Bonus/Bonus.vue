<template>
    <div>
        <Ellipsis v-if="isLoading" />
        <div class="container p-0" v-else>
            <BonusList 
                :bonus="bonus"
                :onetime="onetime"
            />
            <RepostBonus 
                :total="repostInfo.total"
                :levels="repostInfo.levels"
                :bonusBalance="repostInfo.bonusBalance"
            />
        </div>
        <ConnectTG />
    </div>
</template>

<script>
import RepostBonus from "./Repost"
import Ellipsis from '../../components/ui/loader/Ellipsis'
import ConnectTG from '../../components/modals/ConnectTG'
import BonusList from '../Bonus/BonusList'

import { parseTime } from '../../utils/parseTime'

export default {
    components: {
        RepostBonus,
        Ellipsis,
        ConnectTG,
        BonusList
    },
    data() {
        return {
            isLoading: true,
            repostInfo: null,
            onetime: 0,
            bonus: {
                one: {
                    active: null
                },
                daily: {
                    finishAt: null,
                    finishView: null,
                    active: null
                },
            },
            interval: {}
        }
    },
    methods: {
        startTimer(end, type) {
            var now = Math.round(new Date().getTime()/1000);
            var seconds = end - now;
            var interval = this.interval[type]

            this.bonus[type].finishView = parseTime(seconds)

            interval = setInterval(() => {
                if(seconds == 1) {
                    clearInterval(interval);
                    this.bonus[type].active = true
                    return;
                }
                seconds--
                this.bonus[type].finishView = parseTime(seconds)
            }, 1000)
        },
        init() {
            this.$root.axios.post('/bonus/init')
            .then(response => {
                const {data} = response

                this.onetime = data.onetime
                Object.entries(data.bonuses).forEach(item => {
                    var name = item[0]
                    var finishAt = item[1]

                    if(finishAt > (Date.now() / 1000) && name !== 'one') {
                        return this.startTimer(finishAt, name)
                    }

                    if(name == 'one' && data.bonuses.one) {
                        this.bonus[name].active = false
                    } else {
                        this.bonus[name].active = true
                    }
                })

                this.repostInfo = data.repostInfo
                this.isLoading = false
            })
        }
    },
    mounted() {
        this.init()
        this.$root.$on('bonusStartTimer', data => {
            this.startTimer(data.remaining, data.type)
        })
    },
    beforeDestroy() {
        for(let i = 0; i < 100; i++) {
            window.clearInterval(i);
        }
    }
}
</script>