<template>
    <div class="content cards col-12 ml-auto mr-auto p-0">
        <div class="bubbless">
            <div class="content-body">
                <div class="container p-0">
                    <div class="col-12 rows">
                        <div class="col-4 two">
                            <div class="col-12 p-0">
                                <div class="bodybubbles">
                                    <label>Сумма игры</label>
                                    <div class="mines-amount">
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <button @click="typeBet('min')" class="input-group-text amount-number amounts">Min</button>
                                                <button @click="typeBet('/2')" class="input-group-text amount-number amounts">x/2</button>
                                            </div>
                                            <input 
                                                type="number" 
                                                class="form-control form" 
                                                v-model="bet"
                                                @change="typeBet('default')"
                                            />
                                            <div class="input-group-append">
                                                <button @click="typeBet('x2')" class="input-group-text amount-number amounts">x2</button>
                                                <button @click="typeBet('max')" class="input-group-text amount-number amounts">Max</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button 
                                    class="btn-play blue mt-3 col-12"
                                    @click="play"
                                >
                                    Играть
                                </button>
                                <button 
                                    class="btn-play blue mt-3 col-12 play_dice"
                                    @click="BubblesAutoBet"
                                >
                                    {{ !AutoBetStart ? 'Автоставка' : 'Остановить' }}
                                </button>
                            </div>
                            <div class="game-sidebar-hide-desktop">
                                <div class="flex-position" style="position: relative;">
                                    <div class="game-sidebar_input-wrapper" style="flex: 1 1 0%; margin-right: 5px;">
                                        <label class="game-sidebar_input-label">Цель</label>
                                        <div class="msg-wrapper">
                                            <input
                                                type="number" 
                                                class="form-control form" 
                                                v-model="current"
                                                v-on:focus="coeffTableMobile = !0"
                                                v-on:blur="coeffTableMobile = !1"
                                                @keyup="updateResult()"
                                                @input="input(current, 'current')"
                                                @change="current = Number(current).toFixed(2);updateResult('current')"
                                            />
                                        </div>
                                    </div>
                                    <div class="game-sidebar_input-wrapper" style="flex: 1 1 0%;">
                                        <label class="game-sidebar_input-label">Шанс</label>
                                        <div class="msg-wrapper">
                                            <input 
                                                type="number" 
                                                class="form-control" 
                                                v-model="chance"
                                                @input="input(chance, 'chance')"
                                                @change="chance = Number(chance).toFixed(4);updateResult('chance')"
                                            />
                                        </div>
                                    </div>
                                    <div class="bubbles-pick-helper-mobile" v-show="coeffTableMobile" @mousedown.prevent="coeffTableMobile = !0">
                                        <button @click="setCurrent('1.05')" class="set_bubbles bubbles_btn">x1.05</button>
                                        <button class="set_bubbles bubbles_btn" @click="setCurrent('1.50')">x1.5</button>
                                        <button class="set_bubbles bubbles_btn" @click="setCurrent('2.00')">x2</button>
                                        <button class="set_bubbles bubbles_btn" @click="setCurrent('5.00')">x5</button>
                                        <button class="set_bubbles bubbles_btn" @click="setCurrent('10.00')">x10</button>
                                        <button class="set_bubbles bubbles_btn" @click="setCurrent('50.00')">x50</button>
                                        <button class="set_bubbles bubbles_btn" @click="setCurrent('100.00')">x100</button>
                                        <button class="set_bubbles bubbles_btn" @click="setCurrent('1000.00')">x1000</button>
                                    </div>
                                </div>
                                <div class="game-sidebar_input-wrapper">
                                    <label class="game-sidebar_input-label game-sidebar_input-label_switch">Выигрыш</label>
                                    <div class="game-sidebar_input-relative-wrapper_full-width">
                                        <input 
                                            type="text"
                                            disabled="disabled"
                                            autocomplete="off"
                                            class="form-control" 
                                            v-model="bubblesResult"
                                            @change="updateResult('chance')"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-8 one">
                            <div class="game-area game-area_bubbles">
                                <div class="game-area_bubbles-wrapper">
                                    <div class="game-area_bubbles-options">
                                        <div class="game-area_bubbles-option">
                                            <div class="game-sidebar_input-wrapper game-sidebar_input-wrapper_no-margin">
                                                <label class="game-sidebar_input-label">Цель</label>
                                                <div class="msg-wrapper"><span class="msg_error msg_error_bottom" v-show="current < 1.05 || current > 1000">Коэффициент от 1.05 до 1000.00</span>
                                                    <input
                                                        type="number" 
                                                        class="form-control form" 
                                                        v-model="current"
                                                        v-on:focus="coeffTable = !0"
                                                        v-on:blur="coeffTable = !1"
                                                        @keyup="updateResult()"
                                                        @input="input(current, 'current')"
                                                        @change="current = Number(current).toFixed(2);updateResult('current')"
                                                    />
                                                </div>
                                                <div class="bubbles-pick-helper" v-show="coeffTable" @mousedown.prevent="coeffTable = !0">
                                                    <button @click="setCurrent('1.05')" class="set_bubbles bubbles_btn">x1.05</button>
                                                    <button class="set_bubbles bubbles_btn" @click="setCurrent('1.50')">x1.5</button>
                                                    <button class="set_bubbles bubbles_btn" @click="setCurrent('2.00')">x2</button>
                                                    <button class="set_bubbles bubbles_btn" @click="setCurrent('5.00')">x5</button>
                                                    <button class="set_bubbles bubbles_btn" @click="setCurrent('10.00')">x10</button>
                                                    <button class="set_bubbles bubbles_btn" @click="setCurrent('50.00')">x50</button>
                                                    <button class="set_bubbles bubbles_btn" @click="setCurrent('100.00')">x100</button>
                                                    <button class="set_bubbles bubbles_btn" @click="setCurrent('1000.00')">x1000</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="game-area_bubbles-option">
                                            <div class="game-sidebar_input-wrapper game-sidebar_input-wrapper_no-margin">
                                                <label class="game-sidebar_input-label">Шанс</label>
                                                <div class="msg-wrapper"><span class="msg_error msg_error_bottom" v-show="chance < 0.099 || chance > 94.2857">Шанс от 0.099 до 94.28</span>
                                                    <input 
                                                        type="number" 
                                                        class="form-control" 
                                                        v-model="chance"
                                                        @input="input(chance, 'chance')"
                                                        @change="chance = Number(chance).toFixed(4);updateResult('chance')"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="game-area_bubbles-option">
                                            <div class="game-sidebar_input-wrapper game-sidebar_input-wrapper_no-margin">
                                                <label class="game-sidebar_input-label game-sidebar_input-label_switch">Выигрыш</label>
                                                <div class="game-sidebar_input-relative-wrapper_full-width">
                                                    <input 
                                                        type="text"
                                                        disabled="disabled"
                                                        autocomplete="off"
                                                        class="form-control" 
                                                        v-model="bubblesResult"
                                                        @change="updateResult('chance')"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="game-bubbles-coeff">
                                    <div class="game-bubbles-coeff-wrapper">
                                        <div class="game-bubbles-coeff-val bubbles-coeff-won">
                                            x<ICountUp
                                                :endVal="win"
                                                :options="options"
                                                ref="countup"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="bubbles_f bubbles_list">
                                    <div id="hline" class="double-footer-wrapper">
                                        <div class="simplebar-wrapper" style="margin: -15px 0px;">
                                            <div class="simplebar-height-auto-observer-wrapper">
                                                <div class="simplebar-height-auto-observer"></div>
                                            </div>
                                            <div class="simplebar-mask">
                                                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                                    <div class="simplebar-content-wrapper" style="height: auto; overflow-x: hidden;
    overflow-y: visible;
    min-height: 0;">
                                                        <div class="simplebar-content" style="padding: 15px 0px;">
                                                            <div class="bubbles-history-line"><span class="bubbles-history-stub bubbles-history-line-item" style="display: none;">?</span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="simplebar-placeholder" style="width: 857px; height: auto; min-height: 64px;"></div>
                                        </div>
                                        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                                            <div class="simplebar-scrollbar" style="width: 0px; display: none; transform: translate3d(0px, 0px, 0px);"></div>
                                        </div>
                                        <div class="simplebar-track simplebar-vertical" style="visibility: hidden;">
                                            <div class="simplebar-scrollbar" style="height: 0px; display: none;"></div>
                                        </div>
                                    </div>
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
import $ from "jquery";
import ICountUp from 'vue-countup-v2';

export default {
    components: {
        ICountUp
    },
    data() {
        return {
            requestSent3: !1,
            coeffTable: !1,
            coeffTableMobile: !1,
            current: '1.50',
            bet: '1.00',
            chance: "64.6667",
            bubblesResult: '1.50',
            win: 0,
            noty: {
                mess: null,
                type: null
            },
            delay: 500,
            options: {
                duration: .5,
                useEasing: !0,
                useGrouping: !0,
                separator: ",",
                decimal: ".",
                prefix: "",
                suffix: "",
                decimalPlaces: 2,
                startVal: 0
            },
            countUp: null,
            colors: ["rgb(215, 68, 74)", "rgb(65, 159, 77)"],
            AutoBetStart: 0,
            AutoBet: null
        }
    },
    methods: {
        BubblesAutoBet() {
            0 == this.AutoBetStart ? (this.AutoBetStart = 1, this.AutoBet = setInterval(this.play, 1000)) : (clearInterval(this.AutoBet), this.AutoBetStart = 0)
        },
        input(t, e) {
            "current" == e ? this.current = t : "chance" == e && (this.chance = t), this.updateResult(e)
        },
        updateResult(t) {
            "current" == t ? this.chance = (99 / this.current).toFixed(4) : "chance" == t && (this.current = (99 / this.chance).toFixed(2)), this.bubblesResult = (100 / (100 / this.current) * this.bet).toFixed(2)
        },
        setCurrent(t) {
            this.current = t,
            this.updateResult('current')
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
        play() {
            this.noty = {}
            this.requestSent3 || (this.requestSent3 = !0, null !== this.countUp && this.countUp.reset(), $(".game-bubbles-coeff-val.bubbles-coeff-won").attr("style", "color:#496db0"), this.$root.axios.post('/bubbles/play', {
                bet: this.bet,
                current: this.current
            })
            .then(response => {
                const {data} = response
                data.error && (this.requestSent3 = !1, this.$root.$emit("noty", {
                    title: 'Ошибка',
                    text: data.message,
                    type: 'error'
                }))
                this.requestSent3 = !1, this.win = data.game.win
                $('.bubbles-history-line').children().slice(30).remove();
                $(".bubbles-history-line").prepend('<a class="bubbles-history-line-item"><div class="bubbles-item" style="background-color: ' + this.colors[data.game.type] + "; border-color: " + this.colors[data.game.type] + ';">' + data.game.win.toFixed(2) + "x</div></a>")
                $(".game-bubbles-coeff-val.bubbles-coeff-won").attr("style", "color:" + this.colors[data.game.type])
                this.$root.user.balance = data.balance
            }))
        }
    },
    watch: {
        bet: function () {
            if(this.bet > 1000) {
                this.bet = "1000.00"
            }
            
            this.updateResult()
            this.bet = this.$valid(this.bet)
        }
    },
    mounted: function() {
        this.updateResult()
        this.countUp = this.$refs.countup
    }
}
</script>

<style>

.bubbles .buttons .amount-number {
    padding: 5px;
}

.bubbles .content-body {
    padding: 65px 0;
    overflow-x: hidden;
}

.game-area_bubbles {
    flex-direction: column;
    height: 100%;
    position: relative;
    overflow-x: hidden;
    overflow-y: visible;
    min-height: 0;
}

.game-area,.game-area_bubbles {
    overflow-x: hidden;
    overflow-y: visible;
    align-items: stretch;
    display: flex;
}

.game-area {
    min-width: 0;
}

.game-area_bubbles-options {
    border: 1px solid var(--color-main-border);
    border-radius: 7px;
    display: flex;
    padding: 0 10px;
    width: 100%;
}

.game-area_bubbles-option {
    flex: 1;
    margin: 0 5px;
}

.game-sidebar_input-wrapper_no-margin {
    margin-top: 0;
}

.game-sidebar_input-wrapper {
    position: relative;
}

.game-sidebar_input-label {
    display: block;
    margin-bottom: .5rem;
}

.msg-wrapper {
    position: relative;
}

.msg_error {
    background: #1f51f8;
    border-radius: 5px;
    color: #fff;
    font-size: 12px;
    padding: 5px 13px;
    position: absolute;
    white-space: nowrap;
    z-index: 5;
}

.msg_error_bottom {
    bottom: -29px;
    top: auto;
}

.bubbles-pick-helper {
    border: 1px solid #ced4da;
    border-radius: 8px;
    display: flex;
    position: absolute;
    top: -7px;
    transition: .1s;
    z-index: 100;
}

.set_bubbles {
    -webkit-appearance: none;
    background: #fff;
    border: none;
    border-right: 1px solid #ced4da;
    color: #495057;
    flex: auto;
    font-size: 12px;
    line-height: 30px;
    margin: 0;
    outline: none;
    padding: 0;
    text-align: center;
}

.set_bubbles:first-of-type {
    border-bottom-left-radius: 8px;
    border-top-left-radius: 8px;
}

.bubbles_btn {
    border-right: 1px solid #ced4da;
    min-width: 40px;
}

.set_bubbles:last-of-type {
    border-bottom-right-radius: 8px;
    border-right: none;
    border-top-right-radius: 8px;
}

.game-bubbles-coeff {
    align-items: center;
    display: flex;
    flex: auto;
    justify-content: center;
    padding: 30px 0;
    position: relative;
    overflow-x: hidden;
    overflow-y: visible;
    min-height: 0;
}

.game-bubbles-coeff-val {
    color: #496db0;
    font-family: open sans,sans-serif;
    font-size: 62px;
    font-weight: 800;
    max-width: 100%;
    overflow-x: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.bubbles_list {
    width: 100%;
    overflow-x: hidden;
    overflow-y: visible;
    padding: 0 15px;
    white-space: nowrap;
}

.simplebar-wrapper {
    height: inherit;
    max-height: inherit;
    max-width: inherit;
    overflow-x: hidden;
    overflow-y: visible;
    min-height: 0;
    width: inherit;
}

.simplebar-height-auto-observer-wrapper {
    box-sizing: inherit!important;
    flex-basis: 0;
    flex-grow: inherit;
    flex-shrink: 0;
    float: left;
    height: 100%;
    margin: 0;
    max-height: 1px;
    max-width: 1px;
    overflow-x: hidden;
    overflow-y: visible;
    min-height: 0;
    padding: 0;
    pointer-events: none;
    position: relative;
    width: 100%;
    z-index: -1;
}

.simplebar-mask {
    direction: inherit;
    height: auto!important;
    overflow-x: hidden;
    overflow-y: visible;
    min-height: 0;
    width: auto!important;
    z-index: 0;
}

.simplebar-mask,.simplebar-offset {
    bottom: 0;
    left: 0;
    margin: 0;
    padding: 0;
    position: absolute;
    right: 0;
    top: 0;
}

.simplebar-offset {
    -webkit-overflow-scrolling: touch;
    box-sizing: inherit!important;
    direction: inherit!important;
    resize: none!important;
}

[data-simplebar] {
    align-content: flex-start;
    align-items: flex-start;
    flex-direction: column;
    flex-wrap: wrap;
    justify-content: flex-start;
    position: relative;
}

.simplebar-content-wrapper {
    -ms-overflow-style: none;
    box-sizing: border-box!important;
    direction: inherit;
    display: block;
    height: 100%;
    max-height: 100%;
    max-width: 100%;
    position: relative;
    scrollbar-width: none;
    width: auto;
}

.bubbles-history-line {
    min-height: 33px;
    position: relative;
    transition: all .2s ease;
}

.bubbles-history-line .bubbles-history-line-item .bubbles-item {
    -webkit-animation-duration: .2s;
    animation-duration: .2s;
    -webkit-animation-fill-mode: forwards;
    animation-fill-mode: forwards;
    -webkit-animation-name: bubblesright;
    animation-name: bubblesright;
    -webkit-animation-timing-function: ease-out;
    animation-timing-function: ease-out;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    overflow: visible!important;
    perspective: 1000px;
    transform: translateZ(0);
    will-change: transform;
}

.bubbles-item {
    border: 2px solid;
    border-radius: 6px;
    color: #fff;
    display: inline-block;
    font-size: 12px;
    font-weight: 500;
    margin: 3px;
    padding: 5px 10px;
}

.bubbles-pick-helper-mobile {
    border: 1px solid #ced4da;
    border-radius: 5px;
    display: flex;
    position: absolute;
    top: 12px;
    z-index: 100;
}

.flex-position {
    -webkit-box-align: center;
    -ms-flex-position: center;
    -webkit-align-items: center;
    align-items: center;
    display: flex;
}

@-webkit-keyframes bubblesright {
    0% {
        transform: translate(-100%);
    }
}

@keyframes bubblesright {
    0% {
        transform: translate(-100%);
    }
}

@-webkit-keyframes bubblesright-stb {
    0% {
        transform: translate(-100%);
    }
}

@keyframes bubblesright-stb {
    0% {
        transform: translate(-100%);
    }
}

.btn-play.blue:disabled {
    background-image: linear-gradient(45deg,#5669a1,#5669a1);
    cursor: not-allowed;
}

.game-sidebar-hide-desktop {
    display: none;
}

@media (max-width:992px) {
    .flex-position {
        margin-right: 5px;
    }

    .bubbless .col-8 {
        min-width: 100%;
    }

    .bubbless .one {
        order: 1;
        padding: 0;
    }

    .bubbless .two {
        order: 2;
        padding: 1em 0;
    }

    .bubbless .content-body .rows.col-12 {
        display: flex!important;
        flex-direction: column;
    }

    .game-area_bubbles-wrapper {
        display: none;
    }

    .bubbless .bodybubbles,.bubbless .play {
        display: inline-block;
        padding: 0;
    }

    .game-sidebar-hide-desktop {
        display: flex;
    }

    .game-bubbles-coeff {
        padding: 0;
    }

    .game-sidebar_input-wrapper {
        padding-top: 1em;
    }

    .bubbles_list {
        padding: 0;
    }
}
.bubbles_list .simplebar-wrapper {
    overflow-x: hidden !important;
    overflow-y: visible !important;
    height: auto !important;
    max-height: none !important;
    overflow-y: visible !important;
}

.bubbles_list .simplebar-mask {
    overflow-x: hidden !important;
    overflow-y: visible !important;
    height: auto !important;
    max-height: none !important;
    overflow-y: visible !important;
}

.bubbles_list .simplebar-content-wrapper {
    overflow-x: hidden !important;
    overflow-y: visible !important;
    height: auto !important;
    max-height: none !important;
    overflow-y: visible !important;
}

.bubbles_list .simplebar-offset {
    overflow-x: hidden !important;
    overflow-y: visible !important;
    height: auto !important;
    max-height: none !important;
    overflow-y: visible !important;
}

.bubbles_list .simplebar-content {
    overflow-x: visible !important;
    overflow-y: visible !important;
}




.bubbles_list .simplebar-track,
.bubbles_list .simplebar-scrollbar {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
}

.bubbles_list .simplebar-placeholder {
    display: none !important;
}

.bubbles_list .simplebar-wrapper {
    overflow: visible !important;
    height: auto !important;
    max-height: none !important;
}

.bubbles_list .simplebar-mask {
    overflow: visible !important;
    position: static !important;
    height: auto !important;
    width: auto !important;
}

.bubbles_list .simplebar-offset {
    overflow: visible !important;
    position: static !important;
    height: auto !important;
    width: auto !important;
}

.bubbles_list .simplebar-content-wrapper {
    overflow: visible !important;
    height: auto !important;
    max-height: none !important;
}

.bubbles_list .simplebar-content {
    overflow: visible !important;
}

</style>


