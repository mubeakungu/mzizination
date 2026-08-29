<template>
    <div class="content cards col-12 ml-auto mr-auto p-0">
        <div class="dice">
            <div class="content-body">
                <div class="container rows align-items-center">
                    <div class="col-4">
                        <div class="controlPanel">
                            <label>Сумма игры</label> 
                            <input 
                                type="number" 
                                class="form-control form" 
                                v-model="bet"
                                @change="typeBet('default')"
                            />
                            <div class="buttons rows">
                                <button class="amount-number" @click="typeBet('min')">Min</button> 
                                <button class="amount-number" @click="typeBet('max')">Max</button> 
                                <button class="amount-number" @click="typeBet('x2')">x2</button> 
                                <button class="amount-number" @click="typeBet('/2')">x/2</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 position-relative">
                        <h2 class="text-center">{{ parseFloat(diceResult).toFixed(2) }}</h2>
                        <div class="vozTitle text-center">Возможный выигрыш</div>
                    </div>
                    <div class="col-4">
                        <div class="controlPanel">
                            <label>Шанс игры</label> 
                            <input 
                                type="number" 
                                class="form-control form" 
                                v-model="chance"
                                @change="typeChance('default')"
                            />
                            <div class="buttons rows">
                                <button class="amount-number" @click="typeChance('min')">Min</button> 
                                <button class="amount-number" @click="typeChance('max')">Max</button> 
                                <button class="amount-number" @click="typeChance('x2')">x2</button> 
                                <button class="amount-number" @click="typeChance('/2')">x/2</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container rows btns-dice mt-2">
                    <div class="col-4">
                        <div class="text-number mt-3">0 - {{ buttons.min }}</div>
                        <button 
                            class="btn-play blue mt-3 col-12"
                            @click="play('min')"
                        >
                            Меньше
                        </button>
                    </div>
                    <div class="col-4">
                        <div class="text-number mt-3">{{ buttons.center[0] }} - {{ buttons.center[1] }}</div>
                        <button 
                            class="btn-play blue mt-3 col-12"
                            @click="play('center')"
                        >
                            Середина
                        </button>
                    </div>
                    <div class="col-4">
                        <div class="text-number mt-3">{{ buttons.max }} - 999999</div>
                        <button 
                            class="btn-play blue mt-3 col-12"
                            @click="play('max')"
                        >
                            Больше
                        </button>
                    </div>
                </div>
                <div class="container mt-3 rows notifaction">
                    <div class="col-4">
                        <div class="notys" v-if="noty.btn == 'min'">
                            <div class="noty" :class="noty.type">{{ noty.mess }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="notys" v-if="noty.btn == 'center'">
                            <div class="noty" :class="noty.type">{{ noty.mess }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="notys" v-if="noty.btn == 'max'">
                            <div class="noty" :class="noty.type">{{ noty.mess }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Dice",
    data() {
        return {
            chance: "80.00",
            bet: "1.00",
            diceResult: 0,
            noty: {
                mess: null,
                btn: null,
                type: null
            },
            buttons: {
                min: null,
                center: [null, null],
                max: null
            },
        }
    },
    mounted() {
        this.updateResult()
    },
    methods: {
        updateResult() {
            this.diceResult = 100 / this.chance * this.bet
            this.buttons.min = Math.floor(this.chance / 100 * 999999)
            this.buttons.max = 999999 - Math.floor(this.chance / 100 * 999999)
            var t = Math.floor(this.chance / 100 * 999999) / 2
            this.buttons.center[0] = Math.floor(5e5 - t)
            this.buttons.center[1] = Math.floor(5e5 + t)
        },
        play(type) {
            if(!this.$root.user) {
                this.noty = {
                    btn: type,
                    type: 'error',
                    mess: 'Вы не авторизованы'
                }
                return;
            }
            this.noty = {}
            this.$root.axios.post('/dice/bet', {
                amount: this.bet,
                chance: this.chance,
                type
            })
            .then(response => {
                const {data} = response

                if(typeof data.error == 'undefined') {
                    this.$root.user.balance = data.balance;
                    this.noty = {
                        btn: type,
                        type: data.status == true ? 'success' : 'error',
                        mess: data.text
                    }
                    return;
                }

                this.noty = {
                    btn: type,
                    type: 'error',
                    mess: data.message
                }
            })
        },
        typeBet(type) {
            switch(type) {
                case 'min':
                    this.bet = '1.00'
                break;
                case 'max':
                    this.bet = this.$root.user == null ? 0 : (this.$root.user.balance).toFixed(2)
                break;
                case '/2':
                    this.bet = (this.bet / 2).toFixed(2)
                break;
                case 'x2':
                    this.bet = (this.bet * 2).toFixed(2)
                break;
                case 'default':
                    this.bet = (this.bet * 1).toFixed(2)
                break;
            }
        },
        typeChance(type) {
            switch(type) {
                case 'min':
                    this.chance = '1.00'
                break;
                case 'max':
                    this.chance = (95).toFixed(2)
                break;
                case '/2':
                    this.chance = (this.chance / 2).toFixed(2)
                break;
                case 'x2':
                    this.chance = (this.chance * 2).toFixed(2)
                break;
                case 'default':
                    this.chance = (this.chance * 1).toFixed(2)
                break;
            }
        },
    },
    watch: {
        chance: function () {
            if(this.chance > 95) {
                this.chance = "95.00"
            }
            this.updateResult()
            this.chance = this.$valid(this.chance)
        },
        bet: function () {
            if(this.bet > 1000000) {
                this.bet = "1000000.00"
            }
            this.updateResult()
            this.bet = this.$valid(this.bet)
        }
    }
}
</script>

<style scoped>
</style>