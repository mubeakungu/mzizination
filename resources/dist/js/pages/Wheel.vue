<template>
<div class="content cards col-12 ml-auto mr-auto p-0">
    <div class="mines">
        <div class="content-body col-12">
            <div class="container rows">
                <div class="one col-4">
                    <div class="controlPanel">
                        <label>Уровень риска</label>
                        <div class="buttons bombs rows">
                            <button 
                                class="amount-number" 
                                :class="[level == 1 ? 'active' : '']" 
                                @click="level = 1"
                                :disabled="btnLoading"
                            >
                                Легкий
                            </button> 
                            <button 
                                class="amount-number" 
                                :class="[level == 2 ? 'active' : '']" 
                                @click="level = 2"
                                :disabled="btnLoading"
                            >
                                Средний
                            </button> 
                            <button 
                                class="amount-number" 
                                :class="[level == 3 ? 'active' : '']" 
                                @click="level = 3"
                                :disabled="btnLoading"
                            >
                                Сложный
                            </button>
                        </div>
                    </div>
                    <div class="controlPanel mt-4 mb-3 mines-bets">
                        <label>Сумма игры</label>
                        <div class="mines-amount">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <button class="input-group-text amount-number amounts" :disabled="btnLoading" @click="typeBet('min')">Min</button> 
                                    <button class="input-group-text amount-number amounts" :disabled="btnLoading" @click="typeBet('/2')">x/2</button>
                                </div>
                                <input 
                                    type="number" 
                                    class="form-control form" 
                                    v-model="bet"
                                    @change="typeBet('default')"
                                    :disabled="btnLoading"
                                />
                                <div class="input-group-append">
                                    <button class="input-group-text amount-number amounts" :disabled="btnLoading" @click="typeBet('x2')">x2</button> 
                                    <button class="input-group-text amount-number amounts" :disabled="btnLoading" @click="typeBet('max')">Max</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button 
                        class="btn-play blue mt-3 col-12"
                        @click="play"
                        :disabled="btnLoading"
                    >
                        Начать игру
                    </button>
                </div>
                <div id="circle-container" class="col-xs-12 height-100">
                    <div class="legend-wheel" style="margin: 0px auto;">
                        <div class="legend-wheel-circle">
                            <img :src="'/assets/image/wheel/' + level + '.png?v=22'" class="legend-wheel-img" />
                            <div class="legend-wheel-inset"></div>
                            <div class="legend-wheel-arrow"></div>
                        </div>
                        <div class="legend-wheel-inner" v-if="!game.end">
                            <div class="legend-wheel-coefficient">
                                <div class="legend-wheel-coefficient-box" v-if="level == 1">
                                    <div class="legend-wheel-coefficient-list">
                                        <div class="legend-wheel-coefficient-item">
                                            <svg width="16" height="16" viewBox="-1 -1 16 16"><circle fill="#273451" r="7" cx="7" cy="7"></circle></svg> 
                                            <span>x 0.0</span>
                                        </div>
                                        <div class="legend-wheel-coefficient-item">
                                            <svg width="16" height="16" viewBox="-1 -1 16 16"><circle fill="#5480f2" r="7" cx="7" cy="7"></circle></svg> 
                                            <span>x 1.2</span>
                                        </div>
                                        <div class="legend-wheel-coefficient-item">
                                            <svg width="16" height="16" viewBox="-1 -1 16 16"><circle fill="#f34102" r="7" cx="7" cy="7"></circle></svg> 
                                            <span>x 1.5</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="legend-wheel-coefficient-box" v-if="level == 2">
                                    <div class="legend-wheel-coefficient-list">
                                        <div class="legend-wheel-coefficient-item">
                                            <svg width="16" height="16" viewBox="-1 -1 16 16"><circle fill="#273451" r="7" cx="7" cy="7"></circle></svg> <span>x 0.0</span>
                                        </div>
                                        <div class="legend-wheel-coefficient-item">
                                            <svg width="16" height="16" viewBox="-1 -1 16 16"><circle fill="#5480f2" r="7" cx="7" cy="7"></circle></svg> <span>x 1.2</span>
                                        </div>
                                        <div class="legend-wheel-coefficient-item">
                                            <svg width="16" height="16" viewBox="-1 -1 16 16"><circle fill="#f34102" r="7" cx="7" cy="7"></circle></svg> <span>x 1.5</span>
                                        </div>
                                    </div>
                                    <div class="legend-wheel-coefficient-list">
                                        <div class="legend-wheel-coefficient-item">
                                            <svg width="16" height="16" viewBox="-1 -1 16 16" xmlns="http://www.w3.org/2000/svg"><circle fill="#91dc00" r="7" cx="7" cy="7"></circle></svg> <span>x3.0</span>
                                        </div>
                                        <div class="legend-wheel-coefficient-item">
                                            <svg width="16" height="16" viewBox="-1 -1 16 16" xmlns="http://www.w3.org/2000/svg"><circle fill="#ed44cc" r="7" cx="7" cy="7"></circle></svg> <span>x5.0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="legend-wheel-coefficient-box" v-if="level == 3">
                                    <div class="legend-wheel-coefficient-list">
                                        <div class="legend-wheel-coefficient-item">
                                            <svg width="16" height="16" viewBox="-1 -1 16 16" xmlns="http://www.w3.org/2000/svg"><circle fill="#24304a" r="7" cx="7" cy="7"></circle></svg> <span>x0.0</span>
                                        </div>
                                        <div class="legend-wheel-coefficient-item">
                                            <svg width="16" height="16" viewBox="-1 -1 16 16" xmlns="http://www.w3.org/2000/svg"><circle fill="#ed44cb" r="7" cx="7" cy="7"></circle></svg> <span>x49.5</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="legend-wheel-inner" v-else>
                            <div class="legend-wheel-winner">
                                <span class="legend-wheel-winner-prize">
                                    <span class="legend-wheel-prize-integer">{{ game.win }}</span>
                                </span>
                                <div 
                                    class="legend-wheel-winner-coefficient" 
                                    style="background-color: rgb(255, 255, 255);"
                                >
                                    x {{ game.coef }}
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
export default {
    data() {
        return {
            level: 1,
            bet: '1.00',
            btnLoading: false,
            game: {
                end: false
            },
            position: [
                {},
                {
                    blue: [444, 500, 507, 514, 535, 542, 550, 560, 570, 590, 606, 630],
                    red: [735, 736, 162],
                    lose: [24, 130, 135, 492, 496]
                },
                {
                    blue: [496, 668, 535, 520, 524, 534, 564, 594, 637],
                    red: [707, 710, 1500, 1503, 625],
                    green: [752, 755, 1472, 1475],
                    pink: [505, 506, 507, 508, 509, 510],
                    lose: [498, 530, 1477, 1479, 1505, 603, 629, 672, 515]
                },
                {
                    pink: [1540, 1535, 1539, 1537],
                    lose: [1000, 1050, 1532, 1543, 783, 283, 400, 900, 990, 1990, 1190, 590, 225, 825]
                }
            ],
            winDegree: 0,
        }
    },
    methods: {
        play() {
            this.btnLoading = true
            this.game.end = false

            this.$root.axios.post('/wheel/start', {
                bet: this.bet,
                level: this.level
            })
            .then(response => {
                const {data} = response
                
                if(data.error) {
                    this.btnLoading = false;
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        text: data.message,
                        type: 'error'
                    })
                }

                this.$root.user.balance -= parseFloat(this.bet)

                const position = this.position[this.level][data.color];
                const random = this.getRandomInt(0, position.length - 1);
                const degree = position[random];

                this.startAnimation(degree)

                setTimeout(() => {
                    this.$root.user.balance = data.balance
                    this.btnLoading = false;
                    
                    this.game = { 
                        win: data.win, 
                        coef: data.coef, 
                        end: true 
                    }
                    setTimeout(() => this.game.end = false, 4000)

                }, 4500);
            })
        },
        getRandomInt(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min
        },
        startAnimation(deg) {
            if(screen.width <= 760) {
                deg -= 90;
            }

            this.winDegree += deg + (360 * 2) - (this.winDegree % 360)

            const wheel = document.querySelector('.legend-wheel-img')
            wheel.style.transform = `rotate(${this.winDegree}deg)`

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
    },
    watch: {
        bet: function () {

            this.bet < 0 ? this.bet = '1.00' : this.bet
            this.bet > 1000000 ? this.bet = '1000000.00' : this.bet

            this.bet = this.$valid(this.bet)
        }
    }
}
</script>

<style scoped>
.legend-wheel {
    display: inline-flex;
    height: 318px;
    position: relative;
    vertical-align: middle;
    width: 318px;
}

@media(max-width: 1024px) {
    .legend-wheel {
        width: 280px;
        height: 280px;
    }
    #circle-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }
}

@media(max-width: 760px) {
    .legend-wheel {
        width: 320px;
        height: 320px
    }
}

@media(max-width: 370px) {
    .legend-wheel {
        width: 270px;
        height: 270px
    }
}

</style>