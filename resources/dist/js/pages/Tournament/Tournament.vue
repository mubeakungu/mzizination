<template>
    <div class="container">
        <Ellipsis v-if="isLoading" />
        <div class="pt-4 pb-4 col-12 p-none" v-else>
            <div class="header__tour" :style="'background-image: url(' + data.banner + ')'">
                <div class="header__backdrop"></div>
                <div class="header__description">
                    <span>{{ data.title }}</span> 
                    <span class="header__description-small"></span>
                </div>
                <div class="header__end">
                    <span>{{ getStatusText }}</span> 
                    <span class="header__end-timer">
                        {{ time }}
                    </span>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-12 col-lg-4">
                    <div class="content cards">
                        <div class="content-body col-12">
                            <div class="container rows justify-content-center">
                                <div class="controlPanel col-12">
                                    <h5 class="text-center">Призовой фонд</h5>
                                    <div class="tournaments-card-info__fund">
                                        <div class="tournaments-card-info__fund-cup">
                                            <img src="/assets/image/tournament/reward.png" alt="Cup" />
                                        </div>
                                        <div class="tournaments-card-info__fund-amount">
                                            <span>{{ total }}</span>
                                        </div>
                                    </div>
                                    <div class="desc">
                                        <h6>Условия турнира</h6>
                                        <div class="mt-2">
                                            <b>1</b> 
                                            <span class="ml-1">Для того, чтобы принять участие, у Вас должен быть как минимум 1 депозит за последние 7 дней.</span>
                                        </div>
                                        <div class="mt-2">
                                            <b>2</b> 
                                            <span class="ml-1">Ставки учитываются только из указанных в списке игр.</span>
                                        </div>
                                    </div>
                                    <button 
                                        class="mt-3 col-12"
                                        :class="!joined ? 'blue' : 'green'"
                                        :disabled="joined"
                                        @click="join"
                                    >
                                        {{ !joined ? 'Принять участие' : 'Вы участвуете' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content cards mt-4">
                        <div class="content-body col-12">
                            <div class="container rows justify-content-center">
                                <div class="controlPanel col-12">
                                    <h5 style="text-align: center;">Призовые места</h5>
                                    <div class="table-holder">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead class="rang-table">
                                                    <tr class="rang-table">
                                                        <th class="rang-table">Место</th>
                                                        <th class="rang-table">Выигрыш</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(award, position) in data.awards">
                                                        <th scope="row" class="rank">{{ position + 1 }}</th>
                                                        <td>{{ award }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-8">
                    <div class="tournament-alert">
                        <div class="tournament-alert__text">
                            Примите участие в турнире и играйте в игры из списка ниже! При соблюдении условий турнира от суммы ставок будет начислено <strong>10%</strong> в очки!
                        </div>
                    </div>
                    <div class="tournament-games">
                        <h2>Игры</h2>
                        <div class="col-12 row">
                            <div class="game_list tournament">
                                <div class="game_">
                                    <a href="/slots/game/3" class="">
                                        <div class="game_image">
                                            <div class="vue-load-image" src="/image/slots/aviatrix.webp" center-type="cover">
                                                <img src="/image/slots/aviatrix.webp" />
                                            </div>
                                        </div>
                                        <div class="provider_container"><p class="small_provider provider_container2"></p></div>
                                        <div class="game_badges">
                                            <img src="/image/icon_hot.svg" class="game_badge" />
                                        </div>
                                        <div class="info">
                                            <div class="title">Aviatrix</div>
                                            <div class="go"></div>
                                            <a href="/slots/game/3/demo" class=""><div class="title2">DEMO</div></a>
                                            <div class="title3"></div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tournament-block tournament-block_users">
                        <Ellipsis v-if="listLoading" />
                        <div class="content cards col-14 p-1 mr-0" v-else>
                            <header class="rang-table"><h6 class="rang-table">Рейтинг</h6></header>
                            <div class="table-holder">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="rang-table">
                                            <tr class="rang-table">
                                                <th class="rang-table">Место</th>
                                                <th class="rang-table">Игрок</th>
                                                <th class="rang-table">Очки</th>
                                                <th class="rang-table">Приз</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr 
                                                class="bg_gold"
                                                v-if="$root.user !== null && position.number > 10"
                                            >
                                                <th scope="row" class="rank">{{ position.number }}</th>
                                                <td>{{ $root.user.username }}</td>
                                                <td>{{ position.points }}</td>
                                                <td><b>{{ awards[position.number] || 0 }}</b></td>
                                            </tr>
                                            <tr 
                                                :class="$root.user !== null && player.player_id === $root.user.id && 'bg_gold'"
                                                v-for="(player, position) in data.players"
                                            >
                                                <th scope="row" class="rank">{{ position + 1 }}</th>
                                                <td>{{ player.username }}</td>
                                                <td>{{ player.points }}</td>
                                                <td><b>{{ data.awards[position] }}</b></td>
                                            </tr>
                                            <tr v-if="!data.players.length">
                                                <td colspan="4">Нет участников</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Ellipsis from '../../components/ui/loader/Ellipsis'

export default {
    components: {
        Ellipsis,
    },
    data() {
        return {
            data: {},
            players: [],
            isLoading: true,
            listLoading: false,
            joined: false,
            position: {}
        }
    },
    methods: {
        init() {
            this.$root.axios.post('/tournament/' + this.$route.params.id + '/init')
            .then(response => {
                const {data} = response

                if(data.error) {
                    this.$router.push('/tournaments')
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        text: data.message,
                        type: 'error'
                    })
                }

                if(data.joined) {
                    this.joined = true
                }

                this.data = data.info
                this.position = data.position
                
                this.isLoading = false
                this.listLoading = false
            })
        },
        join() {
            this.listLoading = true
            this.$root.axios.post('/tournament/' + this.$route.params.id + '/join')
            .then(response => {
                const {data} = response

                this.listLoading = false

                if(data.error) {
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        text: data.message,
                        type: 'error'
                    })
                }

                this.joined = true
                this.init()

                this.$root.$emit('noty', {
                    title: 'Успешно',
                    text: 'Вы присоединились к турниру',
                    type: 'success'
                })
            })
            .catch(error => {
                this.listLoading = false
                console.error('Join tournament error:', error)
                this.$root.$emit('noty', {
                    title: 'Ошибка',
                    text: error.response && error.response.data && error.response.data.message 
                        ? error.response.data.message 
                        : 'Произошла ошибка при присоединении к турниру',
                    type: 'error'
                })
            })
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString("ru", {
                month: "long",
                day: "numeric",
                hour: "numeric",
                minute: "numeric"
            })
        },
    },
    computed: {
        status() {
            const { id } = this.$route.params
            const status = id.split(':')[1] || false

            if(!['active', 'soon', 'finished'].includes(status)) {
                return false;
            }

            return status;
        },
        getStatusText() {
            switch(this.status) {
                case 'active': 
                    return 'Конец турнира';
                case 'soon':
                    return 'Турнир начнется';
                case 'finished':
                    return 'Турнир завершен';
            }
        },
        time() {
            switch(this.status) {
                case 'active':
                case 'finished':
                    return this.formatDate(this.data.finished_at)
                case 'soon':
                    return this.formatDate(this.data.started_at)
            }
        },
        total() {
            return this.data.awards.reduce((total, amount) => total + amount)
        }
    },
    mounted() {
        if(!this.status) {
            this.$router.push('/tournaments')
            return this.$root.$emit('noty', {
                title: 'Ошибка',
                text: 'Тип турнира не определен',
                type: 'error'
            })
        }

        this.init()
    }
}
</script>

<style scoped>
.header__tour {
    align-items: center;
    background-position: 100% 0;
    background-repeat: no-repeat;
    background-size: 100% 100%;
    border-radius: 10px 10px 0 0;
    display: flex;
    height: 159px;
    justify-content: space-between;
    margin-bottom: 15px;
    padding: 25px 20px 22px 25px;
    position: relative;
}
.header__description {
    color: #fff;
    display: flex;
    flex-direction: column;
    font-size: 24px;
    font-weight: 600;
}
.header__description-small {
    word-wrap: break-word;
    font-size: 12px;
}
.p-none {
    padding: 0 !important;
}
.header__description,
.header__end {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
.header__end {
    background: #fff;
    border-radius: 10px;
    color: #333;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 200px;
    padding: 20px;
    text-align: center;
}
.header__end-timer {
    font-size: 18px;
    font-weight: 600;
    min-width: 200px;
    width: 200px;
}
.header__backdrop {
    display: none;
}
table {
    width: auto !important;
}
@media (max-width: 992px) {
    .header__backdrop {
        -webkit-backdrop-filter: blur(4px);
        backdrop-filter: blur(4px);
        display: block;
        height: 100%;
        left: 0;
        position: absolute;
        top: 0;
        width: 100%;
    }
    .header__description,
    .header__end {
        margin: auto;
        z-index: 2;
    }
    .header__description {
        display: none;
    }
}
.green {
    -webkit-animation: breath 15s linear infinite;
    background-image: linear-gradient(45deg, #28a745, #0d8128);
    box-shadow: 0 0 16px 0 #28a745ad;
    color: #fff;
    font-weight: 500;
}
.tournament-alert {
    align-items: center;
    background: #449686;
    border-radius: 6px;
    display: block;
    margin-bottom: 1em;
    padding: 20px 5px;
    position: relative;
}
.tournament-alert__text {
    color: #fff;
    font-size: 14px;
    line-height: 20px;
    text-align: center;
}
.game_list.tournament {
    max-height: 28em;
    overflow: auto;
    padding-bottom: 6px;
}

.rang-table {
    padding-top: 5px;
    text-align: center;
}

.bg_gold td,
.bg_gold th {
    background: #ffc107 !important;
    color: #000;
}

table {
    width: auto!important
}
</style>