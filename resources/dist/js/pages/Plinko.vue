<template>
<div class="content cards col-12 ml-auto mr-auto p-0">
    <div class="mines">
        <div class="content-body col-12">
            <div class="container rows">
                <div class="one col-4">
                    <button 
                        class="btn-play blue mt-3 col-12 play_plinko play_plinko_mobile"
                        :class="btnDisabled || autoBetStart ? 'btn-disabled' : ''"
                        @click="play"
                    >
                        Начать игру
                    </button>
                    <button 
                        class="btn-play blue mt-3 col-12 play_plinko play_plinko_mobile"
                        @click="switchAutoBet"
                    >
                        {{ !autoBetStart ? 'Автоигра' : 'Остановить' }}
                    </button>
                    <div class="controlPanel play_plinko_pc">
                        <label>Уровень риска</label>
                        <div class="buttons bombs rows">
                            <button 
                                class="amount-number" 
                                :class="game.difficulty === 'low' ? 'active' : ''"
                                :disabled="game.balls !== 0"
                                @click="game.difficulty = 'low'"
                            >
                                Легкий
                            </button> 
                            <button 
                                class="amount-number" 
                                :class="game.difficulty === 'medium' ? 'active' : ''"
                                :disabled="game.balls !== 0"
                                @click="game.difficulty = 'medium'"
                            >
                                Средний
                            </button> 
                            <button 
                                class="amount-number" 
                                :class="game.difficulty === 'high' ? 'active' : ''"
                                :disabled="game.balls !== 0"
                                @click="game.difficulty = 'high'"
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
                                    <button :disabled="game.balls !== 0" class="input-group-text amount-number amounts" @click="typeBet('min')">Min</button>
                                    <button :disabled="game.balls !== 0" class="input-group-text amount-number amounts" @click="typeBet('/2')">x/2</button>
                                </div>
                                <input 
                                    type="number" 
                                    class="form-control form" 
                                    v-model="bet" 
                                    @change="typeBet('default')"
                                    :disabled="game.balls !== 0"
                                />
                                <div class="input-group-append">
                                    <button :disabled="game.balls !== 0" class="input-group-text amount-number amounts" @click="typeBet('x2')">x2</button>
                                    <button :disabled="game.balls !== 0" class="input-group-text amount-number amounts" @click="typeBet('max')">Max</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="controlPanel play_plinko_mobile_flex">
                        <div class="game-sidebar__input-wrapper game-sidebar__input-wrapper__show-mobile">
                            <label class="game-sidebar__input-label">Риск</label>
                            <div class="form-select form-select_light">
                                <select id="risk" v-model="game.difficulty">
                                    <option value="low">Легкий</option>
                                    <option value="medium">Средний</option>
                                    <option value="high">Сложный</option>
                                </select>
                                <span class="myicon-down-arrow"></span>
                            </div>
                        </div>
                        <div class="game-sidebar__input-wrapper">
                            <label class="game-sidebar__input-label">Пинов</label>
                            <div class="form-select form-select_light">
                                <select id="rows" v-model="game.pins">
                                    <option 
                                        v-for="(item, key) in 9" 
                                        :key="key"
                                        :value="key + 8"
                                    >
                                        {{ key + 8 }}
                                    </option>
                                </select>
                                <span class="myicon-down-arrow"></span>
                            </div>
                        </div>
                    </div>
                    <div class="controlPanel play_plinko_pc">
                        <label>Количество пинов</label>
                        <div class="buttons bombs rows">
                            <button 
                                class="amount-number"
                                :class="{
                                    'active': key + 8 === game.pins,
                                    'btn-disabled': game.balls !== 0
                                }"
                                v-for="(item, key) in 9"
                                :key="key"
                                @click="game.pins = key + 8"
                            >
                            {{ key + 8 }}
                            </button> 
                        </div>
                    </div>
                    <button 
                        class="btn-play blue mt-3 col-12 play_plinko play_plinko_pc"
                        :class="btnDisabled || autoBetStart ? 'btn-disabled' : ''"
                        @click="play"
                    >
                        Начать игру
                    </button>
                    <button 
                        class="btn-play blue mt-3 col-12 play_plinko play_plinko_pc"
                        @click="switchAutoBet"
                    >
                        {{ !autoBetStart ? 'Автоигра' : 'Остановить' }}
                    </button>
                </div>
                <div id="w_container" class="two col-8 flex spaceEvenly">
                    <Ellipsis v-if="isLoading" />
                    <div class="plinko_game" v-else>
                        <div class="plinko_list">
                            <div 
                                class="plinko__history-item" 
                                v-for="(item, key) in game.history"
                                :key="key"
                                :style="'background:' + item.background"
                            >
                                {{ parseFloat(item.coeff).toFixed(1) }}x
                            </div>
                        </div>
                        <div id="plinkoContainer">
                            <div id="plinko" size="10" ref="plinko"></div>
                            <span class="sound-icon" @click="toggleSound">
                                <svg v-if="soundEnabled" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                </svg>
                                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                    <line x1="23" y1="9" x2="17" y2="15"></line>
                                    <line x1="17" y1="9" x2="23" y2="15"></line>
                                </svg>
                            </span>
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
import Ellipsis from '../components/ui/loader/Ellipsis'
import { getColor, getRandomNumber } from '../utils'

export default {
    components: {
        Ellipsis
    },
    data() {
        return {
            isLoading: true,
            btnDisabled: true,
            bet: "1.00",
            autoBet: null,
            autoBetStart: 0,
            game: {
                pins: 8,
                difficulty: 'medium',
                multipliers: [],
                history: [],
                balls: 0
            },
            sounds: {
                pin: null,
                win: null,
                lose: null,
                drop: null
            },
            soundEnabled: true,
            volume: 50,
            showVolumeSlider: false
        }
    },
    mounted() {
        this.init()
        this.initSounds()
    },
    beforeRouteLeave(to, from, next) {
        $('.ball').stop()
        clearInterval(this.autoBet)

        next()
    },
    methods: {
        init() {
            this.$root.axios.post('/plinko/init')
            .then(response => {
                const {data} = response
                this.game.multipliers = data
                this.isLoading = false

                this.$nextTick(() => {
                    this.createBoard(this.game.pins)
                    this.btnDisabled = false
                })
            })
        },
        initSounds() {
            // Создаем звуки с помощью Web Audio API
            try {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)()
                // Загружаем сохраненную громкость
                const savedVolume = localStorage.getItem('plinko_volume')
                if (savedVolume !== null) {
                    this.volume = parseInt(savedVolume)
                }
                const savedEnabled = localStorage.getItem('plinko_sound_enabled')
                if (savedEnabled !== null) {
                    this.soundEnabled = savedEnabled === 'true'
                }
            } catch (e) {
                this.soundEnabled = false
                return
            }
        },
        toggleVolumeSlider() {
            this.showVolumeSlider = !this.showVolumeSlider
        },
        toggleSound() {
            this.soundEnabled = !this.soundEnabled
            localStorage.setItem('plinko_sound_enabled', this.soundEnabled)
        },
        saveVolume() {
            localStorage.setItem('plinko_volume', this.volume)
            if (this.volume == 0) {
                this.soundEnabled = false
            } else if (!this.soundEnabled) {
                this.soundEnabled = true
            }
            localStorage.setItem('plinko_sound_enabled', this.soundEnabled)
        },
        getVolume() {
            return this.volume / 100 * 0.3 // Max 0.3 чтобы не было слишком громко
        },
        playPinSound() {
            if (!this.soundEnabled || !this.audioContext || this.volume == 0) return
            try {
                const vol = this.getVolume()
                const oscillator = this.audioContext.createOscillator()
                const gainNode = this.audioContext.createGain()
                
                oscillator.connect(gainNode)
                gainNode.connect(this.audioContext.destination)
                
                // Высокий короткий звук удара
                oscillator.frequency.setValueAtTime(800 + Math.random() * 400, this.audioContext.currentTime)
                oscillator.type = 'sine'
                
                gainNode.gain.setValueAtTime(vol * 0.5, this.audioContext.currentTime)
                gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.05)
                
                oscillator.start(this.audioContext.currentTime)
                oscillator.stop(this.audioContext.currentTime + 0.05)
            } catch (e) {}
        },
        playWinSound(coeff) {
            if (!this.soundEnabled || !this.audioContext || this.volume == 0) return
            try {
                const vol = this.getVolume()
                const oscillator = this.audioContext.createOscillator()
                const gainNode = this.audioContext.createGain()
                
                oscillator.connect(gainNode)
                gainNode.connect(this.audioContext.destination)
                
                // Победный звук - выше для больших выигрышей
                const baseFreq = coeff >= 2 ? 600 : 400
                oscillator.frequency.setValueAtTime(baseFreq, this.audioContext.currentTime)
                oscillator.frequency.exponentialRampToValueAtTime(baseFreq * 1.5, this.audioContext.currentTime + 0.1)
                oscillator.type = 'sine'
                
                gainNode.gain.setValueAtTime(vol, this.audioContext.currentTime)
                gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.2)
                
                oscillator.start(this.audioContext.currentTime)
                oscillator.stop(this.audioContext.currentTime + 0.2)
                
                // Второй тон для большого выигрыша
                if (coeff >= 2) {
                    const self = this
                    setTimeout(() => {
                        const osc2 = self.audioContext.createOscillator()
                        const gain2 = self.audioContext.createGain()
                        osc2.connect(gain2)
                        gain2.connect(self.audioContext.destination)
                        osc2.frequency.setValueAtTime(800, self.audioContext.currentTime)
                        osc2.type = 'sine'
                        gain2.gain.setValueAtTime(vol * 0.8, self.audioContext.currentTime)
                        gain2.gain.exponentialRampToValueAtTime(0.01, self.audioContext.currentTime + 0.15)
                        osc2.start(self.audioContext.currentTime)
                        osc2.stop(self.audioContext.currentTime + 0.15)
                    }, 100)
                }
            } catch (e) {}
        },
        playDropSound() {
            if (!this.soundEnabled || !this.audioContext || this.volume == 0) return
            try {
                const vol = this.getVolume()
                const oscillator = this.audioContext.createOscillator()
                const gainNode = this.audioContext.createGain()
                
                oscillator.connect(gainNode)
                gainNode.connect(this.audioContext.destination)
                
                // Звук падения в корзину
                oscillator.frequency.setValueAtTime(300, this.audioContext.currentTime)
                oscillator.frequency.exponentialRampToValueAtTime(100, this.audioContext.currentTime + 0.1)
                oscillator.type = 'sine'
                
                gainNode.gain.setValueAtTime(vol * 0.8, this.audioContext.currentTime)
                gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.1)
                
                oscillator.start(this.audioContext.currentTime)
                oscillator.stop(this.audioContext.currentTime + 0.1)
            } catch (e) {}
        },
        switchAutoBet() {
            this.autoBetStart = !this.autoBetStart

            if(this.autoBetStart) {
                return this.autoBet = setInterval(this.play, 300)
            }

            clearInterval(this.autoBet)
        },
        play() {
            if(parseFloat(this.$root.user.balance) < this.bet) {
                return this.$root.$emit('noty', {
                    title: 'Ошибка',
                    text: 'Недостаточно средств',
                    type: 'error'
                })
            }

            this.btnDisabled = true
            this.$root.axios.post('/plinko/play', {
                bet: this.bet,
                pins: this.game.pins,
                difficulty: this.game.difficulty
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

                this.$root.user.balance -= data.bet

                this.animateBall(data.bucket + 1, data.result, data.coeff)
                this.game.balls += 1
            })
            .finally(() => {
                setTimeout(() => this.btnDisabled = false, 250)
            })
        },
        animateBall(target, result, coeff) {
            const { el, duration } = this.createBall(target)
            $('#plinko').append(el);

            $(el).attr('result', result)
            $(el).attr('coeff', coeff)
            $(el).animate(
                this.getPosition(el), 
                duration, 
                () => this.moveBall(el, duration)
            )
        },
        moveBall(el, duration) {
            const ballSize = parseInt($('#plinko').attr('size'))
            const { step, delta, target, result, coeff } = this.getAttributesValues(el, parseFloat)
            const updatedStep = step + 1

            let deltaUpdate = getRandomNumber(0, 1)
            let heading = 0
    
            $(el).attr('step', updatedStep)
            
            if(updatedStep !== ballSize + 1) {
                const pin = $(`.pin[row="${step}"][pos="${delta}"]`)

                $(pin).addClass('touched')
                setTimeout(() => $(pin).removeClass('touched'), 300)
                this.playPinSound()
                
                if(delta === target) {
                    deltaUpdate = 0
                } else if(ballSize - step == target - delta) {
                    deltaUpdate = 1
                }

                heading = !deltaUpdate 
                    ? getRandomNumber(10, 14)
                    : getRandomNumber(20, 24)

                deltaUpdate += delta;

                $(el).attr('delta', deltaUpdate)
                $(el).removeAttr("heading")
                    .delay(duration / 1e3)
                    .queue(function () {
                        $(el).attr("heading", heading).dequeue();
                    });

                $(el).animate(
                    this.getPosition(el), 
                    duration, 
                    () => this.moveBall(el, duration)
                );
            } else {
                // шарик пролетел все препятствия
                const bucket = $(`.bucket-${target}`)
                const bucketWrap = $(`.bucket-${target} .payout-value-wrap`)

                $(bucket).addClass('animated')
                setTimeout(() => $(bucket).removeClass('animated'), 300);

                this.playDropSound()
                this.playWinSound(coeff)

                this.game.balls -= 1
                this.game.history.unshift({ coeff, background: $(bucketWrap).css('background') })
                this.$root.user.balance += result

                $(el).removeAttr("heading")
                    .delay(duration / 1e3)
                    .queue(function () {
                        $(el).attr("heading", 2).dequeue();
                    })
                    .delay(duration)
                    .queue(function () {
                        $(el).remove().dequeue();
                    });
            }
        },
        getPosition(ball) {
            const { step, delta } = this.getAttributesValues(ball)
            const el = $(`[row="${step}"][pos="${delta}"]`)

            const top = parseFloat(el.css('top'))
            const left = parseFloat(el.css('left'))
                
            return { top, left }
        },
        getAttributesValues(el, parseFn) { // нужно вынести в utils
            const attributeValues = {};
            for (const { name, value } of el.attributes) {
                if (parseFn && typeof parseFn === 'function') {
                    attributeValues[name] = parseFn(value);
                } else {
                    attributeValues[name] = value;
                }
            }
            return attributeValues;
        },
        createBall(target) {
            const size = $('#plinko').attr('size')
            const ballSize = 1 / 3 / (parseInt(size) + 2)
            const duration = getRandomNumber(150, 250)

            const styles = {
                position: 'absolute',
                top: `${-100 * ballSize}%`,
                left: '50%',
                width: `${100 * ballSize}%`,
                height: `${100 * ballSize}%`,
                background: 'radial-gradient(circle at 30% 30%, #fff 0%, #ffd700 35%, #f59e0b 70%, #b45309 100%)',
                borderRadius: '50%',
                animationDuration: `${duration / 1e3}s`,
                transform: 'translate(-50%, -125%)',
                boxShadow: '0 0 15px rgba(255, 215, 0, 0.7), inset 0 -3px 6px rgba(0,0,0,0.3)',
                border: '1px solid rgba(255,255,255,0.3)',
            };

            const attrs = {
                class: 'ball',
                step: 2,
                delta: 1,
                target,
            };

            const el = document.createElement('div')
                        
            Object.keys(styles).forEach(key => {
                el.style[key] = styles[key];
            });
        
            Object.keys(attrs).forEach(key => {
                el.setAttribute(key, attrs[key]);
            });

            return { el, duration }
        },
        createBoard(pins) {
            pins += 2;
            $('#plinko').empty();
            $('#plinko').attr("size", pins);

            for (let row = 2; row <= pins; row++) {
                for (let pos = 0; pos <= row; pos++) {
                    const left = 0.5 + (pos - row / 2) / (pins + 2);
                    const top = (row + 1 - 2) / (pins + 2);
                    const widthHeight = 1 / (row == pins ? 3 : 5) / (pins + 2);

                    if (row == pins) {
                        if (pos !== 0 && pos !== pins) {
                            const html = `
                                <div 
                                    class="payout-value bucket bucket-${pos}" 
                                    row="${row}" 
                                    pos="${pos}" 
                                    style="z-index:2;position: absolute;top:${100 * top}%;left:${100 * left}%;width: ${250 * widthHeight}%;height: ${250 * widthHeight}%;transform: translate(-50%, -50%)"
                                >
                                    <div class="payout-value-wrap" style="background: ${getColor(pos, this.game.pins)}">
                                        ${this.getMulti(pos)}
                                        <div class="payout-value-wrap__pocket"></div>
                                    </div>
                                </div>
                            `;
                            $('#plinko').append(html);
                        }
                    } else {
                        const styles = {
                            position: 'absolute',
                            top: `${100 * top}%`,
                            left: `${100 * left}%`,
                            width: `${150 * widthHeight}%`,
                            height: `${150 * widthHeight}%`,
                            background: 'radial-gradient(circle at 30% 30%, #60a5fa 0%, #3b82f6 50%, #1d4ed8 100%)',
                            borderRadius: '50%',
                            transform: 'translate(-50%, -50%)',
                            boxShadow: '0 2px 6px rgba(0,0,0,0.3), inset 0 1px 2px rgba(255,255,255,0.3)',
                            transition: 'transform 0.15s ease, box-shadow 0.15s ease',
                        };

                        const el = document.createElement('div')
                        
                        Object.keys(styles).forEach(key => {
                            el.style[key] = styles[key];
                        });

                        el.setAttribute('row', row)
                        el.setAttribute('pos', pos)
                        el.setAttribute('class', 'pin')

                        $('#plinko').append(el);
                    }
                }
            }
        },
        getMulti(position) {
            let multiplier = this.game.multipliers[this.game.difficulty][this.game.pins][position - 1];

            if(multiplier >= 1000) return parseFloat(multiplier / 1000).toFixed(0) + 'K'
            return multiplier
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
        'game.pins'() {
            this.createBoard(this.game.pins)
        },
        'game.difficulty'() {
            this.createBoard(this.game.pins)
        },
        'game.history'() {
            if(this.game.history.length > 15) {
                this.game.history.pop()
            }
        },
        bet: function () {
            this.bet < 0 ? this.bet = '1.00' : this.bet
            this.bet > 1000000 ? this.bet = '1000000.00' : this.bet

            this.bet = this.$valid(this.bet)
        }
    }
}
</script>

<style>
/* ========== PLINKO REDESIGN ========== */

.btn-disabled {
    pointer-events: none;
    opacity: 0.6;
}

.amount-number.amounts {
    background-color: #fff !important;
    color: #5a5a5a !important;
    transition: all 0.2s ease;
}

.amount-number.amounts:hover {
    background-color: #f0f0f0 !important;
    transform: translateY(-1px);
}

/* История выигрышей */
.plinko_list {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    width: 400px;
    overflow: hidden;
    height: 64px;
    padding: 0 10px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    margin-bottom: 10px;
}

.plinko_list .plinko__history-item {
    border-radius: 8px;
    width: auto;
    min-width: 55px;
    margin: 0 4px;
    text-align: center;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 10px;
    animation: historySlideIn 0.3s ease-out;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* Sound Icon */
#plinkoContainer {
    position: relative;
}

.sound-icon {
    position: absolute;
    bottom: 10px;
    right: 10px;
    cursor: pointer;
    color: #fff;
    opacity: 0.7;
    transition: opacity 0.2s;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.4);
    border-radius: 50%;
    z-index: 10;
}

.sound-icon:hover {
    opacity: 1;
}

.sound-icon svg {
    stroke: #fff;
}

@keyframes historySlideIn {
    from {
        opacity: 0;
        transform: translateX(-20px) scale(0.8);
    }
    to {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}

#w_container {
    max-width: 100%;
    width: 100%;
}
.plinko_game {
    align-items: center;
    display: flex;
    flex-direction: column;
    height: 100%;
    max-width: 650px;
    width: 100%;
}
.plinko-payouts {
    height: 2em;
    order: 1;
    padding: 0 35px;
    width: 380px;
}
.payout-line {
    display: flex;
    justify-content: space-around;
}
.plinko-payouts .payout-value {
    -webkit-backface-visibility: hidden;
    font-size: 0.8em;
    font-weight: 500;
    transform-origin: 0 0;
    will-change: transform;
    z-index: 2;
}
.payout-value-wrap,
.plinko-payouts .payout-value {
    align-items: center;
    display: flex;
    justify-content: center;
    position: relative;
}
.payout-value-wrap {
    border-radius: 8px;
    color: #fff;
    font-size: clamp(8px, 0.55em, 12px);
    font-weight: 800;
    min-height: 22px;
    padding: 2px 1px;
    text-align: center;
    width: 100%;
    text-shadow: 0 1px 2px rgba(0,0,0,0.4);
    box-shadow: 
        inset 0 -8px 15px rgba(0,0,0,0.3),
        0 4px 12px rgba(0,0,0,0.25);
    border: 1px solid rgba(255,255,255,0.15);
    transition: all 0.2s ease;
    white-space: nowrap;
}
.payout-value-wrap__pocket {
    background: #fff;
    border-radius: 50%;
    height: 40%;
    left: 50%;
    position: absolute;
    top: -6px;
    transform: translateX(-50%);
    width: 50%;
}
.payout-value.animated {
    -webkit-animation: bucket_drop 0.4s cubic-bezier(0.18, 0.89, 0.32, 1.28) 0s;
    animation: bucket_drop 0.4s cubic-bezier(0.18, 0.89, 0.32, 1.28) 0s;
}

.payout-value.animated .payout-value-wrap {
    animation: bucket_glow 0.5s ease-out;
}

@keyframes bucket_glow {
    0% { box-shadow: inset 0 -8px 15px rgba(0,0,0,0.3), 0 4px 12px rgba(0,0,0,0.25), 0 0 0 rgba(255,255,255,0); }
    50% { box-shadow: inset 0 -8px 15px rgba(0,0,0,0.3), 0 4px 12px rgba(0,0,0,0.25), 0 0 25px rgba(255,255,255,0.6); }
    100% { box-shadow: inset 0 -8px 15px rgba(0,0,0,0.3), 0 4px 12px rgba(0,0,0,0.25), 0 0 0 rgba(255,255,255,0); }
}
@-webkit-keyframes bucket_drop {
    0% {
        transform: translate(-50%, -50%);
    }
    50% {
        transform: translate(-50%, -10%);
    }
    to {
        transform: translate(-50%, -50%);
    }
}
@keyframes bucket_drop {
    0% {
        transform: translate(-50%, -50%);
    }
    50% {
        transform: translate(-50%, -10%);
    }
    to {
        transform: translate(-50%, -50%);
    }
}
.play_plinko_pc {
    display: block;
}
.play_plinko_mobile,
.play_plinko_mobile_flex {
    display: none;
}
.game-sidebar__input-wrapper {
    width: 50%;
}
.form-select select {
    -webkit-appearance: none;
    -moz-appearance: none;
    border: 1px solid #ced4da;
    border-radius: 8px;
    height: 46px;
    outline: none;
    padding: 0 10px;
    text-indent: 1px;
    width: 100%;
}
.game-sidebar__input-wrapper__show-mobile .form-select.form-select_light {
    margin-right: 1em;
}
.mines {
    overflow: hidden;
}
.plinko_list .bubbles-item {
    border: 0;
    color: #333 !important;
}
.play_plinko {
    touch-action: manipulation;
}
#plinkoContainer {
    display: block;
    height: 400px;
    margin: auto;
    overflow: hidden;
    position: relative;
    width: 400px;
}
#plinkoContainer #plinko {
    height: 100%;
    position: absolute;
    width: 100%;
}
#plinkoContainer #plinko [heading]:not([heading=""]) {
    -webkit-animation-iteration-count: 1;
    animation-iteration-count: 1;
    -webkit-animation-timing-function: ease-in-out;
    animation-timing-function: ease-in-out;
}
#plinkoContainer #plinko [heading="10"] {
    -webkit-animation-name: bounceLeft-1;
    animation-name: bounceLeft-1;
}
#plinkoContainer #plinko [heading="11"] {
    -webkit-animation-name: bounceLeft-2;
    animation-name: bounceLeft-2;
}
#plinkoContainer #plinko [heading="12"] {
    -webkit-animation-name: bounceLeft-3;
    animation-name: bounceLeft-3;
}
#plinkoContainer #plinko [heading="13"] {
    -webkit-animation-name: bounceLeft-4;
    animation-name: bounceLeft-4;
}
#plinkoContainer #plinko [heading="14"] {
    -webkit-animation-name: bounceLeft-5;
    animation-name: bounceLeft-5;
}
@-webkit-keyframes bounceLeft-1 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -250%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceLeft-1 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -250%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@-webkit-keyframes bounceLeft-2 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -260%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceLeft-2 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -260%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@-webkit-keyframes bounceLeft-3 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -240%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceLeft-3 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -240%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@-webkit-keyframes bounceLeft-4 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -220%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceLeft-4 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -220%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@-webkit-keyframes bounceLeft-5 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -200%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceLeft-5 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translate(-100%, -200%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
#plinkoContainer #plinko [heading="20"] {
    -webkit-animation-name: bounceRight-1;
    animation-name: bounceRight-1;
}
#plinkoContainer #plinko [heading="21"] {
    -webkit-animation-name: bounceRight-2;
    animation-name: bounceRight-2;
}
#plinkoContainer #plinko [heading="22"] {
    -webkit-animation-name: bounceRight-3;
    animation-name: bounceRight-3;
}
#plinkoContainer #plinko [heading="23"] {
    -webkit-animation-name: bounceRight-4;
    animation-name: bounceRight-4;
}
#plinkoContainer #plinko [heading="24"] {
    -webkit-animation-name: bounceRight-5;
    animation-name: bounceRight-5;
}
@-webkit-keyframes bounceRight-1 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-250%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceRight-1 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-250%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@-webkit-keyframes bounceRight-2 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-260%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceRight-2 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-260%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@-webkit-keyframes bounceRight-3 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-240%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceRight-3 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-240%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@-webkit-keyframes bounceRight-4 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-220%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceRight-4 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-220%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@-webkit-keyframes bounceRight-5 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-200%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
@keyframes bounceRight-5 {
    0% {
        transform: translate(-50%, -125%);
    }
    50% {
        transform: translateY(-200%);
    }
    to {
        transform: translate(-50%, -125%);
    }
}
#plinkoContainer #plinko [heading="2"] {
    -webkit-animation-name: fallAway;
    animation-name: fallAway;
}
@-webkit-keyframes fallAway {
    0% {
        transform: translate(-50%, -125%) scale(1);
    }
    to {
        transform: translate(-50%, -50%) scale(1);
    }
}
@keyframes fallAway {
    0% {
        transform: translate(-50%, -125%) scale(1);
    }
    to {
        transform: translate(-50%, -50%) scale(1);
    }
}
@media (max-width: 1024px) {
    .content-body {
        padding: 15px 0 !important;
    }
    #plinkoContainer {
        height: 40vh;
        width: 100%;
    }
    .controlPanel.mines-bets label,
    .play_plinko_pc {
        display: none;
    }
    .play_plinko_mobile {
        display: block;
    }
    .play_plinko_mobile_flex {
        display: flex;
    }
}
.pin.touched {
    -webkit-animation: touched 0.35s ease-out;
    animation: touched 0.35s ease-out;
}
@-webkit-keyframes touched {
    0% {
        box-shadow: 0 0 0 0 rgba(96, 165, 250, 0.8);
        transform: translate(-50%, -50%) scale(1);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(96, 165, 250, 0);
        transform: translate(-50%, -50%) scale(1.4);
    }
    to {
        box-shadow: 0 0 0 0 rgba(96, 165, 250, 0);
        transform: translate(-50%, -50%) scale(1);
    }
}
@keyframes touched {
    0% {
        box-shadow: 0 0 0 0 rgba(96, 165, 250, 0.8);
        transform: translate(-50%, -50%) scale(1);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(96, 165, 250, 0);
        transform: translate(-50%, -50%) scale(1.4);
    }
    to {
        box-shadow: 0 0 0 0 rgba(96, 165, 250, 0);
        transform: translate(-50%, -50%) scale(1);
    }
}

</style>