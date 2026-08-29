<template>
    <div class="col-12 p-0 bonuses rows">
        <div class="content cards col-4 p-0 mr-4">
            <div class="content-body col-12"><img src="/assets/image/reward2.png" class="reward1">
                <div class="container rows justify-content-center">
                    <div class="controlPanel col-12">
                        <h5>Активировать промокод:</h5>
                        <input 
                            type="text" 
                            placeholder="Введите промокод" 
                            class="form-control form promo mt-4" 
                            v-model="promocode"
                        />
                        <button class="blue mt-2 col-12" @click="activate">Активировать</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="content cards col-4 p-0 mr-4">
            <div class="content-body col-12"><img src="/assets/image/reward1.png" class="reward1">
                <div class="container rows justify-content-center">
                    <div class="controlPanel col-12 ">
                        <h5>Ежедневный бонус (24ч)</h5>
                        <div class="desc">
                            <div class="desc_text">Выполните условия</div>
                            <div class="mt-2">
                                <img src="/assets/image/vk.svg" width="23px" />
                                <span class="ml-1">Подписка на 
                                    <a :href="$root.config.vk_url" target="_blank">группу ВКонтакте</a>
                                </span>
                            </div>
                            <div class="mt-3">
                                <img src="/assets/image/tg.svg" width="23px" />
                                <span class="ml-1">Подпишитесь на наш 
                                    <a :href="$root.config.tg_channel" target="_blank">TG Канал</a>
                                </span>
                            </div>
                        </div>
                        <button 
                            class="blue mt-3 col-12" 
                            @click="takeBonus('daily')" 
                            :disabled="!bonus.daily.active"
                        >
                            {{ bonus.daily.active ? 'Получить бонус' : bonus.daily.finishView }}
                        </button>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="content cards col-4 p-0">
            <div class="content-body col-12 reward33"><img src="/assets/image/reward3.png" class="reward3">
                <div class="container rows justify-content-center">
                    <div class="controlPanel col-12">
                        <h5>Одноразовый бонус</h5>
                        <div class="desc">
                            <div class="desc_text">Выполните условия</div>
                            <div class="mt-2">
                                <img src="/assets/image/vk.svg" width="23px" />
                                <span class="ml-1">Подписка на 
                                    <a :href="$root.config.vk_url" target="_blank">группу ВКонтакте</a>
                                </span>
                            </div>
                            <div class="mt-3">
                                <img src="/assets/image/tg.svg" width="23px" />
                                <span class="ml-1">Подпишитесь на наш 
                                    <a :href="$root.config.tg_channel" target="_blank">TG Канал</a>
                                </span>
                            </div>
                        </div>
                        <button 
                            class="blue mt-3 col-12"
                            @click="takeBonus('one')"
                            :disabled="!bonus.one.active"
                        >
                            {{ bonus.one.active ? 'Получить бонус ' + onetime + 'Р' : 'Вы уже получили' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
export default {
    props: ['bonus', 'onetime'],
    data() {
        return {
            promocode: null,
            create: {
                code: '',
                activation: '',
                sum: ''
            },
            isLoading: false
        }
    },
    methods: {
        takeBonus(type) {
            this.$root.axios.post('/bonus/take', {
                type
            })
            .then(response => {
                const {data} = response

                if(data.showModal) this.$root.$emit('openConnectTg')
                if(data.error) {
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        type: 'error',
                        text: data.message
                    })
                }

                this.bonus[data.type].active = false

                if(data.type !== 'one') {
                    this.$root.$emit('bonusStartTimer', {
                        remaining: data.remaining,
                        type: data.type
                    })
                }

                this.$root.user.balance = data.balance
                this.$root.$emit('noty', {
                    title: 'Успешно',
                    type: 'success',
                    text: data.text
                })
            })
        },
        createPromo() {
            if(this.create.code == '' || this.create.activation == '' || this.create.sum == '') {
                return this.$root.$emit('noty', {
                    title: 'Ошибка',
                    text: 'Заполните все поля',
                    type: 'error'
                })
            }

            this.$root.axios.post('/promo/create', {
                code: this.create.code,
                activate: this.create.activation,
                sum: this.create.sum
            })
            .then(response => {
                const {data} = response

                if(data.error) {
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        text: data.message,
                        type: 'error'
                    })
                }

                this.promocode = ''
                this.$root.user.balance = data.balance

                this.$root.$emit('noty', {
                    title: 'Успешно',
                    text: data.text,
                    type: 'success'
                })
            })
        },
        activate() {
            if(!this.promocode) {
                return this.$root.$emit('noty', {
                    title: 'Ошибка',
                    text: 'Заполните все поля',
                    type: 'error'
                })
            }
            this.$root.axios.post('/promo/activate', {
                code: this.promocode,
            })
            .then(response => {
                const {data} = response

                if(data.error) {
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        text: data.message,
                        type: 'error'
                    })
                }

                this.promocode = ''
                this.$root.user.balance = data.balance

                this.$root.$emit('noty', {
                    title: 'Успешно',
                    text: data.text,
                    type: 'success'
                })
            })
        }
    }
} 
</script>

<style scoped>
.reward33 {
    border-radius: 15px;
}

.desc_text,.h5,h5 {
    text-align: center;
}

.h5,h5 {
    font-size: 1.12rem;
}

.reward4 {
    background-image: url(/image/reward4.png);
    background-position: calc(100% + 10px) calc(100% + 7px);
    background-repeat: no-repeat;
    background-size: contain;
    background-size: 115px;
    font-size: 12px;
}

.reward1,.reward2 {
    width: 46px;
}

.reward1,.reward2,.reward3 {
    height: 46px;
    left: 155px;
    position: absolute;
    top: -22px;
}

.reward3 {
    width: 40px;
}

.bonuses {
    display: flex;
    margin-bottom: 20px;
}

.promo {
    font-size: 14px;
    text-align: start;
}

.promocode {
    display: flex;
    justify-content: space-between;
}

.controlPanel.active {
    max-height: 170px;
    overflow: auto;
}

.activePromo .title {
    font-weight: 500;
}

.activePromo .date {
    overflow: hidden;
    text-overflow: ellipsis;
}

.bonuses .content,.promocode .content {
    flex: auto;
}

.sends {
    background-color: #2483a7!important;
    width: 100%;
}

.sends,.sends:hover {
    color: #fff!important;
}

.sends:hover {
    background-color: #197294!important;
}

.controlPanel .desc {
    font-size: 15px;
}

.like {
    align-items: center;
    display: flex;
    justify-content: space-between;
}

.k1,.like {
    width: 100%;
}

.k1 {
    padding: 30px;
}

.balanceBox {
    padding-left: 10%;
    text-align: center;
}

.likeBonusBox {
    padding-right: 10%;
}

.balance {
    text-align: center;
}

.like-item {
    margin-top: 4px;
}

.likeBonus {
    color: #235ed7;
    font-weight: 600;
}

.like-levels {
    margin-top: 2rem;
}

.sumDep {
    color: #4b556f;
    font-size: 13px;
    margin-top: .1rem;
    text-align: right;
}

.likeLevlBox {
    background-color: #f1f2fd52;
    border-radius: 5px;
    flex: auto;
    margin-right: 2rem;
    padding: 8px;
}

.likeBonus {
    margin-left: 2rem;
}

.progressBar {
    background-color: #f0f1f7;
    border-radius: 3px;
    height: 8px;
    width: 100%;
}

.progressB {
    border-radius: 3px;
    height: 100%;
    transition: .5s;
}

.lvl {
    color: #4b556f;
    font-size: 12px;
}

.likeLevlBox .rows {
    justify-content: space-between;
}

.progressB.purple {
    background-color: #a528a7;
}

.progressB.red {
    background-color: #a72828;
}

.progressB.blues {
    background-color: #2872a7;
}

.progressB.green {
    background-color: #28a745;
}

.progressB.pink {
    background-color: #407bff;
}

.likeBonus.purple {
    color: #a528a7;
}

.likeBonus.red {
    color: #a72828;
}

.likeBonus.blues {
    color: #2872a7;
}

.likeBonus.green {
    color: #28a745;
}

.likeBonus.pink {
    color: #407bff;
}

.odometer {
    font-weight: 600;
}

@media(max-width:992px) {
    .balanceBox {
        padding-left: 0;
    }

    .like-item {
        font-size: 13px;
        margin-bottom: 5px;
        margin-top: 5px;
    }

    .reward1,.reward2,.reward3 {
        display: none;
    }

    .like {
        flex-direction: column-reverse;
    }

    .likeBonusBox {
        width: 100%;
    }

    .like-levels .rows {
        flex-wrap: wrap;
    }

    .balanceBox,.balanceBox button {
        width: 100%;
    }

    .balanceBox .title {
        font-size: 20px;
        font-weight: 800;
    }
}
</style>