<template>
    <div>
        <Ellipsis v-if="isLoading" />
        <div v-else>
            <div class="content cards col-12 p-0">
                <div class="card-header"><span>Пополнить счет</span></div>
                <div class="pt-4 pb-4 col-12">
                    <div class="withdraw container">
                        <div class="col-6 p-0">
                            <div 
                                style="margin-bottom: 1rem; background-color: rgba(0, 128, 0, 0.6); color: white !important; border-radius: 5px; padding: 15px;"
                                v-if="'воскресенье' == eventBuy"
                            >
                                <div class="event-title">Happy Sunday!!!</div>
                                <strong>Каждое воскресенье бонусы при пополнении!</strong>
                                <div>Бонус <strong>+10%</strong> на пополнения от 150 рублей!</div>
                            </div>
                            <div class="inbox mb-2">
                                <label>Выберите систему:</label> 
                                <div class="rows">
                                    <div 
                                        class="pay-box" 
                                        :class="[system == 'fk' ? 'isActive' : '']"
                                        @click="system = 'fk'"
                                    >
                                        <img src="/assets/image/fkwallet.png" class="pay-img" />
                                        <div class="pay-title">FK Wallet</div>
                                    </div>
                                    <div 
                                        class="pay-box" 
                                        :class="[system == 'sbp' ? 'isActive' : '']"
                                        @click="system = 'sbp'"
                                    >
                                        <img src="/assets/image/sbp.png" class="pay-img" />
                                        <div class="pay-title">СБП</div>
                                    </div>
                                    <div 
                                        class="pay-box" 
                                        :class="[system == 'qiwi' ? 'isActive' : '']"
                                        @click="system = 'qiwi'"
                                    >
                                        <img src="/assets/image/qiwi.png" class="pay-img" />
                                        <div class="pay-title">Qiwi</div>
                                    </div>
                                    <div 
                                        class="pay-box" 
                                        :class="[system == 'card' ? 'isActive' : '']"
                                        @click="system = 'card'"
                                    >
                                        <img src="/assets/image/card.png" class="pay-img" />
                                        <div class="pay-title">Карта</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <input 
                                    type="text" 
                                    placeholder="Введите промокод, если есть" 
                                    class="form-control" 
                                    v-model="promocode"
                                />
                            </div>

                            <!-- TON Payment Section -->
                            <div class="form-group mt-3">
                                <label>Введите сумму пополнения: 
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="rows mt-1 mb-2">
                                    <button class="amount-number" @click="sum = 200">200</button> 
                                    <button class="amount-number" @click="sum = 500">500</button> 
                                    <button class="amount-number" @click="sum = 1500">1500</button>
                                    <button class="amount-number" @click="sum = 3000">3000</button> 
                                    <button class="amount-number" @click="sum = 5000">5000</button>
                                </div>
                                <input 
                                    type="number" 
                                    placeholder="Сумма" 
                                    class="form-control" 
                                    v-model="sum"
                                />
                            </div>
                            <div class="rows with-form-bottom">
                                <button 
                                    class="button blue" 
                                    style="margin: 0px auto; width: 100%;"
                                    @click="create"
                                >
                                    Перейти к оплате
                                </button>
                            </div>
                            <div class="warning">
                                <img src="/assets/image/icon18.svg" class="age_18" />
                                <p class="t_warning">
                                    Азартные игры призваны развлекать. Помните, что Вы рискуете деньгами, когда делаете ставки. Не тратьте больше, чем можете позволить себе проиграть.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content history cards col-12 mt-5 p-0">
                <table>
                    <thead>
                        <tr>
                            <th>Система</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="payment in allPayments" 
                            :key="payment.id + '-' + payment.system"
                        >
                            <td class="system_wallet">
                                <img v-if="payment.system === 'ton'" src="/assets/image/ton.svg" style="height: 30px;" />
                                <img v-else src="/assets/image/fkwallet.png" style="height: 30px;" />
                            </td>
                            <td>{{ parseFloat(payment.sum).toFixed(2) }} ₽</td>
                            <td 
                                :class="[
                                    {'text-warning': payment.status == 0},
                                    {'text-success': payment.status == 1},
                                    {'text-danger': payment.status == 3}
                                ]"
                            >
                                {{ getPaymentStatusText(payment.status) }}
                            </td>
                            <td>{{ $moment(payment.created_at).format('lll') }}</td>
                        </tr>
                        <tr v-if="allPayments.length == 0">
                            <td colspan="4" class="p-3">История пуста</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import Ellipsis from '../components/ui/loader/Ellipsis'

export default {
    components: {
        Ellipsis
    },
    data() {
        return {
            isLoading: true,
            system: 'fk',
            promocode: null,
            sum: null,
            payments: [],
            tonPaymentsHistory: [],
            // TON Data
            tonRate: 120,
            tonMinRub: 12,
            tonMinTon: 0.1,
            tonLoading: false,
            tonWallet: '',
            tonMemo: '',
            tonQrCode: null,
            tonStatus: 0,
            tonCheckInterval: null,
            tonRateInterval: null
        }
    },
    methods: {
        create() {
            if(!this.sum) {
                return this.$root.$emit('noty', {
                    title: 'Ошибка',
                    text: 'Заполните все поля',
                    type: 'error'
                })
            }

            this.$root.axios.post('/payment/create', {
                amount: this.sum,
                code: this.promocode,
                system: this.system
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

                location.href = data.url
            })
        },
        init() {
            this.$root.axios.post('/payment/init')
            .then(response => {
                const {data} = response
                
                this.isLoading = false
                this.payments = data.payments || []
            })
            // Load TON payments history
            this.$root.axios.post('/ton/history').then(res => {
                this.tonPaymentsHistory = res.data.payments || []
            }).catch(() => {
                this.tonPaymentsHistory = []
            })
            // Load TON info
            this.loadTonInfo()
        },
        // TON Methods
        loadTonInfo() {
            this.tonLoading = true
            this.$root.axios.post('/ton/info').then(res => {
                this.tonRate = res.data.rate || 120
                this.tonMinRub = res.data.min_rub || 12
                this.tonMinTon = res.data.min_ton || 0.1
                this.tonWallet = res.data.wallet || ''
                this.tonMemo = res.data.memo || ''
                this.generateTonQR()
                this.startTonAutoCheck()
                this.startTonRateUpdate()
                this.tonLoading = false
            }).catch(() => {
                this.tonLoading = false
            })
        },
        generateTonQR() {
            const tonLink = `ton://transfer/${this.tonWallet}?text=${this.tonMemo}`
            this.tonQrCode = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(tonLink)}`
        },
        startTonAutoCheck() {
            this.tonCheckInterval = setInterval(() => {
                this.checkBalance()
            }, 10000)
        },
        startTonRateUpdate() {
            // Обновляем курс каждые 3 минуты
            this.tonRateInterval = setInterval(() => {
                this.updateTonRate()
            }, 180000)
        },
        async updateTonRate() {
            try {
                const res = await this.$root.axios.post('/ton/rate')
                if (res.data.rate) {
                    this.tonRate = res.data.rate
                    this.tonMinTon = res.data.min_ton
                }
            } catch (e) {}
        },
        clearTonIntervals() {
            if (this.tonCheckInterval) clearInterval(this.tonCheckInterval)
            if (this.tonRateInterval) clearInterval(this.tonRateInterval)
        },
        async checkBalance() {
            try {
                const res = await this.$root.axios.post('/user/init')
                if (res.data.user && res.data.user.balance > this.$root.user.balance) {
                    const diff = res.data.user.balance - this.$root.user.balance
                    this.$root.user.balance = res.data.user.balance
                    this.tonStatus = 1
                    this.$root.$emit('noty', {
                        title: 'Успешно!',
                        text: `+${diff.toFixed(2)} ₽`,
                        type: 'success'
                    })
                }
            } catch (e) {}
        },
        copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.$root.$emit('noty', {
                    title: 'Скопировано',
                    text: 'Текст скопирован в буфер обмена',
                    type: 'success'
                })
            })
        },
        getPaymentStatusText(status) {
            if (status == 1) return 'Зачислено'
            if (status == 0) return 'Ожидание'
            if (status == 3) return 'Истек'
            return 'Неизвестно'
        }
    },
    computed: {
        eventBuy: function() {
            return this.$moment().format("dddd")
        },
        allPayments() {
            const regular = (this.payments || []).map(p => ({
                ...p,
                system: 'fk',
                sum: p.sum
            }))
            const ton = (this.tonPaymentsHistory || []).map(p => ({
                ...p,
                system: 'ton',
                sum: p.amount_rub
            }))
            return [...regular, ...ton].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        },
        tonStatusClass() {
            if (this.tonStatus === 1) return 'ton-status-success'
            return 'ton-status-pending'
        },
        tonStatusText() {
            if (this.tonStatus === 1) return '✓ Платеж получен!'
            return '⏳ Ожидание платежа...'
        }
    },
    mounted() {
        this.init()
    },
    beforeDestroy() {
        this.clearTonIntervals()
    }
}
</script>


<style scoped>
/* TON Payment Styles */
.ton-box.isActive {
    border-color: #0088cc !important;
    background: rgba(0, 136, 204, 0.1) !important;
}
.ton-payment-section {
    margin-top: 15px;
}
.ton-loading {
    text-align: center;
    padding: 40px;
    color: #888;
    font-size: 18px;
}
.ton-rate-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(0, 136, 204, 0.1);
    border: 1px solid rgba(0, 136, 204, 0.3);
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 12px;
}
.ton-rate-info span { color: #0088cc; }
.ton-min-info {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    margin-bottom: 16px;
    color: #10b981;
}
.ton-qr-section { text-align: center; margin-bottom: 16px; }
.ton-qr-img {
    width: 180px;
    height: 180px;
    border-radius: 10px;
    background: #fff;
    padding: 10px;
}
.ton-detail-item { margin-bottom: 12px; }
.ton-detail-item label {
    display: block;
    font-size: 12px;
    color: #888;
    margin-bottom: 5px;
}
.ton-memo-item label { color: #f59e0b; font-weight: 600; }
.ton-copy-field {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.2s;
}
.ton-copy-field:hover { background: rgba(255, 255, 255, 0.15); }
.ton-memo-field {
    background: rgba(245, 158, 11, 0.1);
    border-color: rgba(245, 158, 11, 0.3);
    font-weight: 600;
    font-size: 16px;
}
.ton-warning-box {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 8px;
    padding: 12px;
    font-size: 13px;
    color: #f59e0b;
    margin: 15px 0;
}
.ton-status-box {
    text-align: center;
    padding: 12px;
    border-radius: 8px;
    font-weight: 500;
    margin-bottom: 12px;
}
.ton-status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.ton-status-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.ton-auto-check {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #888;
    font-size: 13px;
}
/* Original styles */
.warning {
    align-items: center;
    display: flex;
}
img.age_18 {
    height: 36px;
    width: 36px;
}
p.t_warning {
    font-size: 12px;
    line-height: 14px;
    margin-left: 10px;
    padding-top: 16px;
}
table tr {
    font-weight: 400 !important;
}
.card-header {
    text-align: center;
}
.col-6 {
    margin: 0 auto;
}
table {
    text-align: center;
    width: 100%;
}
.amount-number {
    border: 1px solid #ced4da;
    border-radius: 5px;
    flex: auto;
    margin-right: 5px;
}
.amount-number:last-child {
    margin: 0;
}
.event-title {
    -webkit-animation: event__tilte 3s infinite;
    animation: event__tilte 3s infinite;
    background-color: #ffd9d9;
    border-radius: 5px;
    color: #ff3e3e;
    font-weight: 600;
    padding: 5px;
    position: absolute;
    right: -31px;
    top: -1px;
}
@-webkit-keyframes event__tilte {
    0% {
        transform: rotate(35deg) scale(1);
    }
    50% {
        transform: rotate(25deg) scale(1.1);
    }
    to {
        transform: rotate(35deg) scale(1);
    }
}
@keyframes event__tilte {
    0% {
        transform: rotate(35deg) scale(1);
    }
    50% {
        transform: rotate(25deg) scale(1.1);
    }
    to {
        transform: rotate(35deg) scale(1);
    }
}
@-webkit-keyframes event__action {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
    to {
        transform: scale(1);
    }
}
@keyframes event__action {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
    to {
        transform: scale(1);
    }
}
@media (max-width: 500px) {
    .with-form-bottom {
        flex-direction: column;
    }
    .with-form-bottom .text {
        display: none;
    }
    .with-form-bottom .button {
        width: 100%;
    }
}
.withdraw .rows {
    flex-wrap: wrap;
    gap: 10px;
}
</style>