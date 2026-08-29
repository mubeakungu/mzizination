<template>
    <div class="container p-0" users="[object Object]">
        <div class="col-12 p-0 promo rows">
            <div class="content-body col-12 p-0">
                <div class="col-12 p-0 promocode">
                    <div class="content cards col-6 p-0 mr-4">
                        <div class="content-body col-12">
                            <div class="container rows justify-content-center">
                                <div class="controlPanel col-12">
                                    <h5>Активировать промокод:</h5>
                                    <div class="desc">
                                        <div>Выполните условия</div>
                                        <div class="mt-3">
                                            <img src="/assets/image/vk.svg" width="23px" /> 
                                            <span class="ml-1">Подписка на <a target="_blank" :href="$root.config.vk_url">группу ВКонтакте</a></span>
                                        </div>
                                        <div class="mt-2">
                                            <img src="/assets/image/tg.svg" width="23px" /> 
                                            <span class="ml-1">Подпишитесь на наш <a target="_blank" :href="$root.config.tg_channel">TG Канал</a></span>
                                        </div>
                                        <div class="mt-2">
                                            <img src="/assets/image/success.svg" width="23px" /> 
                                            <span class="ml-1">Отыгрыш от 100</span>
                                        </div>
                                    </div>
                                    <input 
                                        type="text" 
                                        placeholder="Введите промокод" 
                                        class="form-control form promo mt-4" 
                                        v-model="promocode"
                                    /> 
                                    <button class="blue mt-2 col-12" @click="activate">Активировать</button>
                                    <a :href="$root.config.tg_channel" target="_blank" class="btn mt-2 sends">Подписаться на рассылку промокодов</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content cards col-6 p-0 two">
                        <div class="content-body col-12">
                            <div class="container rows justify-content-center">
                                <div class="controlPanel col-12">
                                    <h5>Создать свой промокод</h5>
                                    <div class="desc">
                                        <div>Выполните условия</div>
                                        <div class="mt-3">
                                            <img src="/assets/image/vk.svg" width="23px" /> <span class="ml-1">Подписка на <a target="_blank" :href="$root.config.vk_url">группу ВКонтакте</a></span>
                                        </div>
                                        <div class="mt-2">
                                            <img src="/assets/image/tg.svg" width="23px" /> 
                                            <span class="ml-1">Подпишитесь на наш <a target="_blank" :href="$root.config.tg_channel">TG Канал</a></span>
                                        </div>
                                        <div class="mt-2"><img src="/assets/image/success.svg" width="23px" /> <span class="ml-1">Совершить депозитов на сумму от 1000</span></div>
                                    </div>
                                    <input 
                                        type="text" 
                                        placeholder="Название промокода" 
                                        class="form-control form promo mt-4" 
                                        v-model="create.code"
                                    />
                                    <div class="rows">
                                        <input 
                                            type="number" 
                                            placeholder="Количество активаций" 
                                            :disabled="create.code == ''"
                                            class="form-control form mt-2 promo" 
                                            v-model="create.activation"
                                        />
                                        <input 
                                            type="number" 
                                            placeholder="Награда" 
                                            :disabled="create.activation == ''"
                                            class="form-control form mt-2 promo ml-2" 
                                            v-model="create.sum"
                                        />
                                    </div>
                                    <button 
                                        class="blue mt-2 col-12" 
                                        @click="createPromo"
                                    >
                                        Создать
                                    </button>
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
export default {
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
.bonuses {
    display: flex;
}
.promo {
    font-size: 14px;
    text-align: start !important;
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
.bonuses .content,
.promocode .content {
    flex: auto;
}
.controlPanel .desc {
    font-size: 15px;
}
.sends {
    background-color: #2483a7 !important;
    width: 100%;
}
.sends,
.sends:hover {
    color: #fff !important;
}
.sends:hover {
    background-color: #197294 !important;
}

</style>