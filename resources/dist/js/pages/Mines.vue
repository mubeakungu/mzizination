<template>
    <div>
        <Ellipsis v-if="isLoading && !$root.isLoading" />
        <div class="content cards col-12 ml-auto mr-auto p-0" v-else>
            <div class="mines">
                <div class="content-body col-12">
                    <div class="container rows">
                        <div class="one col-4">
                            <div class="controlPanel">
                                <label>Количество бомб</label>
                                <div class="buttons bombs rows">
                                    <button :disabled="game.state == 2" :class="['amount-number', { 'active': bomb == 3 }]" @click="bomb = 3">
                                        3
                                    </button>
                                    <button :disabled="game.state == 2" :class="['amount-number', { 'active': bomb == 5 }]" @click="bomb = 5">
                                        5
                                    </button>
                                    <button :disabled="game.state == 2" :class="['amount-number', { 'active': bomb == 10 }]" @click="bomb = 10">
                                        10
                                    </button>
                                    <button :disabled="game.state == 2" :class="['amount-number', { 'active': bomb == 24 }]" @click="bomb = 24">
                                        24
                                    </button>
                                    <button :disabled="game.state == 2" class="amount-number" style="position: relative;" @click="changeBomb = true">
                                        Изменить
                                        <div :class="['redBox', { 'show': changeBomb }]">
                                            <input type="number" class="redinp" v-model="bomb" @change="changeBomb = false" />
                                        </div>
                                    </button>
                                </div>
                            </div>
                            <div class="controlPanel mt-4 mb-3 mines-bets">
                                <label>Сумма игры</label>
                                <div class="mines-amount">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <button :disabled="game.state == 2" class="input-group-text amount-number amounts" @click="typeBet('min')">Min</button>
                                            <button :disabled="game.state == 2" class="input-group-text amount-number amounts" @click="typeBet('/2')">x/2</button>
                                        </div>
                                        <input 
                                            type="number" 
                                            class="form-control form" 
                                            v-model="bet" 
                                            @change="typeBet('default')"
                                            :disabled="game.state == 2"
                                        />
                                        <div class="input-group-append">
                                            <button :disabled="game.state == 2" class="input-group-text amount-number amounts" @click="typeBet('x2')">x2</button>
                                            <button :disabled="game.state == 2" class="input-group-text amount-number amounts" @click="typeBet('max')">Max</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button 
                                class="btn-play blue mt-3 col-12" 
                                @click="play" 
                                v-if="game.state != 2"
                            >
                                Начать игру
                            </button>
                            <button 
                                class="btn-play blue mt-3 col-12 mt-2 flex" 
                                @click="take" 
                                v-if="game.state == 2"
                            >
                                Забрать 
                                <ICountUp
                                    class="ml-1 font-weight-500"
                                    :endVal="game.total"
                                    :options="options"
                                />
                            </button>
                            <button class="btn-play ser mt-2" v-if="game.state == 2" @click="autoSelect">Автовыбор</button>
                        </div>
                        <div class="two col-6 flex spaceEvenly">
                            <div class="minefields">
                                <div class="winBox"  v-if="game.state == 1">
                                    <div class="popup">
                                        <div class="popup__title">Гениально!</div>
                                        <div class="popup__money">{{ parseFloat(game.total).toFixed(2) }} ₽</div>
                                        <div class="popup__chance">{{ parseFloat(game.coeff).toFixed(2) }} x</div>
                                    </div>
                                </div>
                                <div
                                    v-for="(item, key) in grid"
                                    :key="key"
                                    @click="openPath(key + 1)"
                                    :class="[
                                    'cell',
                                    { 'lose': item.bomb },
                                    { 'win': item.diamond },
                                    { 'opacity': click.indexOf(item.index + 1) === -1},
                                    { 'wait': loaderCell == key + 1}
                                ]"
                                />
                            </div>
                        </div>
                        <div class="three col-2">
                            <div class="stepsPrev" @click="$refs.slick.prev()">
                                <img
                                    src="data:image/svg+xml;charset=utf-8,%3Csvg width='16' height='16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M13.066 11.536a.5.5 0 00.708 0l.473-.473a.5.5 0 000-.707L8.354 4.464a.5.5 0 00-.708 0l-5.892 5.892a.5.5 0 000 .707l.472.473a.5.5 0 00.708 0l4.712-4.712a.5.5 0 01.708 0l4.713 4.712z' fill='%23777792'/%3E%3C/svg%3E"
                                    width="20px"
                                />
                            </div>
                            <Slick ref="slick" :options="splideOptions" class="stepsCoffBox" v-if="showSlick">
                                <div 
                                    v-for="(item, key) in (25 - bomb)" 
                                    :key="key"
                                    :class="[
                                        'swiper-slide',
                                        'stepsCoffs',
                                        {'win': click.length >= key + 1},
                                        {'active': click.length == key && game.state == 2},
                                        {'lose': game.state == 3 && click.length == key + 1}
                                    ]"
                                    style="width: 100%; display: inline-block;" 
                                >
                                    <div class="rows justify-content-between">
                                        <div class="step-number">{{ key + 1 }} ход</div>
                                        <div class="step-coff">
                                            {{ getPrefix( getCoff(key + 1, bomb) ) }}x
                                        </div>
                                    </div>
                                    <h6 class="winSum">
                                        {{ parseFloat(getCoff(key + 1, bomb) * bet).toFixed(2) }}
                                    </h6>
                                </div>
                            </Slick>
                            <div class="stepsPrev" @click="$refs.slick.next()">
                                <img
                                    src="data:image/svg+xml;charset=utf-8,%3Csvg width='16' height='16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M13.066 11.536a.5.5 0 00.708 0l.473-.473a.5.5 0 000-.707L8.354 4.464a.5.5 0 00-.708 0l-5.892 5.892a.5.5 0 000 .707l.472.473a.5.5 0 00.708 0l4.712-4.712a.5.5 0 01.708 0l4.713 4.712z' fill='%23777792'/%3E%3C/svg%3E"
                                    width="20px"
                                    style="transform: rotate(180deg);"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Slick from 'vue-slick';
import 'slick-carousel/slick/slick.css';
import { getEmptyArr } from '../utils/getEmptyArr'
import Ellipsis from '../components/ui/loader/Ellipsis'
import ICountUp from 'vue-countup-v2';

export default {
    components: {
        Slick,
        Ellipsis,
        ICountUp
    },
    data() {
        return {
            bomb: 3,
            bet: "1.00",
            changeBomb: false,
            grid: [],
            click: [],
            showSlick: true,
            isLoading: true,
            loaderCell: null,
            noty: {
                mess: null,
                type: null
            },
            game: {
                state: 0, // 0 - inactive, 1 - win, 2 - process, 3 - lose
                total: 0,
                coeff: 0,
                step: 0
            },
            splideOptions: {
                slidesToShow: 5,
                vertical: true,
                slidesToScroll: 5,
                arrows: false,
                infinite: false,
                responsive: [
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 2,
                            vertical: false
                        }
                    }
                ]
            },
            options: {
                useEasing: true,
                useGrouping: true,
                separator: " ",
                decimal: ".",
                prefix: "",
                suffix: "",
                decimalPlaces: 2
            }
        }
    },
    mounted() {
        this.grid = getEmptyArr(25, { bomb: false, diamond: false })
        this.init()
    },
    methods: {
        init() {
            this.$root.axios.post('/mines/init')
            .then(response => {
                const {data} = response
                
                this.isLoading = false
                
                if(typeof data.click !== 'undefined') {
                    this.bet = data.amount
                    this.bomb = data.bombs
                    this.game = {
                        ...this.game,
                        state: 2,
                        total: data.total
                    }
                    this.click = data.click
                    this.grid = this.grid.map(item => 
                        data.click.indexOf(item.index + 1) !== -1
                            ? { ...item, diamond: true }
                            : item
                    )
                }
            })
        },
        play() {
            if(!this.$root.user) {
                return this.$root.$emit('noty', {
                    title: 'Ошибка',
                    text: 'Вы не авторизованы',
                    type: 'error'
                })
            }

            this.$root.axios.post('/mines/start', {
                amount: this.bet,
                bombs: this.bomb
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
                
                this.$refs.slick.goTo(0, 1e3),
                this.click = []
                this.grid = getEmptyArr(25, { bomb: false, diamond: false })
                this.game = {
                    ...this.game,
                    total: 0,
                    state: 2
                }
                this.$root.user.balance = data.balance
            })
        },
        openPath(index) {
            if(this.click.indexOf(index) !== -1) return;
            if(this.game.state != 2) return;

            this.loaderCell = index
            this.$root.axios.post('/mines/open', {
                path: index
            })
            .then(response => {
                const {data} = response
                this.loaderCell = -1

                if(data.error) {
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        text: data.message,
                        type: 'error'
                    })
                }

                this.click.push(index)
                if(data.continue) {
                    this.grid = this.grid.map(item => 
                        item.index == index - 1
                            ? {...item, diamond: true}
                            : item
                    )

                    this.game = {
                        ...this.game,
                        state: 2,
                        total: data.total
                    }
                    this.$refs.slick.goTo(this.click.length, 1e3)
                } else {
                    this.game = {
                        ...this.game,
                        state: 3,
                        total: 0
                    }
                    this.grid = this.grid.map(item =>
                        data.bombs.indexOf(item.index + 1) !== - 1
                            ? { ...item, bomb: true }
                            : { ...item, diamond: true }
                    )
                }

                if(data.instwin !== null) {
                    this.game = {
                        ...this.game,
                        state: 1,
                        total: data.instwin.total,
                        coeff: data.instwin.coeff
                    }

                    this.grid = this.grid.map(item =>
                        data.instwin.bombs.indexOf(item.index + 1) !== - 1
                            ? { ...item, bomb: true }
                            : { ...item, diamond: true }
                    )

                    this.$root.user.balance = data.instwin.balance
                }
            })
        },
        take() {
            this.$root.axios.post('/mines/take')
            .then(response => {
                const {data} = response

                if(data.error) {
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        text: data.message,
                        type: 'error'
                    })
                }

                this.game = {
                    ...this.game,
                    state: 1,
                    total: data.total,
                    coeff: data.coeff
                }

                this.grid = this.grid.map(item =>
                    data.bombs.indexOf(item.index + 1) !== - 1
                        ? { ...item, bomb: true }
                        : { ...item, diamond: true }
                )

                this.$root.user.balance = data.balance
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
        autoSelect() {
            var noActive = document.querySelectorAll('.cell:not(.win)')
            noActive[Math.floor(Math.random() * noActive.length)].click();
        },
        getCoff(step, bombs) {
            !step ? step = 1 : step
            for (var n = 1, a = 0; a < 25 - bombs && step > a; a++)
                n *= (25 - a) / (25 - bombs - a);
        
            return parseFloat(n).toFixed(2)
        },
        getPrefix(n) {
            if(n >= 1000000) return parseFloat(n/1000000).toFixed(0) + 'M';
            if(n >= 1000) return parseFloat(n/1000).toFixed(0) + 'K';
            return parseFloat(n).toFixed(2)
        },
    },
    watch: {
        bomb: function () {

            this.bomb < 0 ? this.bomb = 3 : this.bomb
            this.bomb > 24 ? this.bomb = 24 : this.bomb

            this.showSlick = false
            this.$nextTick(() => {
                this.showSlick = true
            });
        },
        bet: function () {

            this.bet < 0 ? this.bet = '1.00' : this.bet
            this.bet > 1000000 ? this.bet = '1000000.00' : this.bet

            this.bet = this.$valid(this.bet)
        }
    }
}
</script>

<style scoped>
.winBox {
    align-items: center;
    display: flex;
    height: 100%;
    justify-content: center;
    position: absolute;
    width: 100%;
    z-index: 5;
}
.winT {
    background-color: #fff;
    border: 2px solid #1f51f6;
    border-radius: 5px;
    box-shadow: 2px 2px 2px 0 #0000004a;
    color: #1e50fa;
    padding: 5px 35px;
    position: relative;
    text-align: center;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
.font-weight-500 {
    font-weight: 500;
}
.winT .winsum {
    font-size: 17px;
}
.winT .wincoff {
    font-size: 12px;
}
.redBox {
    bottom: 0;
    height: 100%;
    left: 0;
    margin: 0;
    max-width: 100%;
    opacity: 0;
    pointer-events: none;
    position: absolute;
    right: 0;
    top: 0;
}
.redBox.show {
    opacity: 1;
    pointer-events: auto;
    visibility: visible;
}
.redinp {
    background-color: #dce0ed;
    border: 0;
    border-bottom-right-radius: 5px;
    border-top-right-radius: 5px;
    color: #38477c;
    height: 100%;
    outline: none;
    position: relative;
    text-align: center;
    width: 100%;
}
.stepsCoffs {
    margin-bottom: 9px;
}
@media (max-width: 992px) {
    .slick-slide {
        margin: 0;
    }
    .stepsCoffBox {
        width: 100%;
    }
}
.popup__title {
    color: #fff;
    font-size: 18px;
}
.popup__money {
    color: gold;
    font-size: 30px;
    font-weight: 700;
    margin-bottom: 10px;
}
.popup__chance {
    background-color: hsla(0,0%,100%,.1);
    border-radius: 15.5px;
    color: #fff;
    font-size: 16px;
    line-height: 31px;
    width: 82px;
}
</style>