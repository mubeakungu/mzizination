<template>
    <div>
        <Ellipsis v-if="isLoading"/>
        <div class="content cards col-12 p-0" v-if="!isLoading">
            <div class="card-header"><span>Заказать выплату</span></div>
            <div class="pt-4 pb-4 col-12">
                <div class="withdraw container">
                    <div class="col-6 p-0">
                        <div class="inbox">
                            <div class="rows">
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
                                <!--<div 
                                    class="pay-box" 
                                    :class="[system == 'sbp' ? 'isActive' : '']"
                                    @click="system = 'sbp'"
                                >
                                    <img src="/assets/image/sbp.png" class="pay-img" />
                                    <div class="pay-title">СБП</div>
                                </div>-->
                                <div 
                                    class="pay-box" 
                                    :class="[system == 'fk' ? 'isActive' : '']"
                                    @click="system = 'fkwallet'"
                                >
                                    <img src="/assets/image/fkwallet.png" class="pay-img" />
                                    <div class="pay-title">FK Wallet</div>
                                </div>
                                <div 
                                    class="pay-box" 
                                    :class="[system == 'trc20' ? 'isActive' : '']"
                                    @click="system = 'trc20'"
                                >
                                    <img src="/assets/image/trc20.png" class="pay-img" />
                                    <div class="pay-title">Tether TRC20</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3 position-relative">
                            <label>
                                {{ systemGet.title }}
                                <span class="text-danger">*</span>
                            </label> 
                            <input 
                                type="text" 
                                :placeholder="systemGet.placeholder" 
                                class="form-control" 
                                v-model="wallet"
                            />
                            <div class="ww-3" @click="showPurse = !showPurse">...</div>
                            
                            <div class="cards accor" v-if="showPurse">
                                <button 
                                    class="amount-number-with flex" 
                                    v-for="item in unq"
                                    :key="item"
                                    @click="selectPurse(item.id)"
                                >
                                    <div class="system_wallet">
                                        <img :src="'/assets/image/' + item.system + '.png'" />
                                    </div>
                                    <span>{{ item.wallet }}</span>
                                </button>
                                <div class="col-12 amount-number-with p-2" v-if="unq.length == 0">У вас нет шаблонов.</div>
                            </div>
                        </div>
                        <!--<div class="form-group mt-3 position-relative">
                            <label>
                                Банк 
                                <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                placeholder="Выберите банк"
                                class="form-control"
                            >
                            <div class="ww-3" @click="showPurseBank = !showPurseBank">...</div>
                            
                            <div class="cards accor" v-if="showPurseBank">
                                <button 
                                    class="amount-number-with flex" 
                                    v-for="item in unq"
                                    :key="item"
                                    @click="selectPurse(item.id)"
                                >
                                    <div class="system_wallet">
                                        <img :src="'/assets/image/' + item.system + '.png'" />
                                    </div>
                                    <span>{{ item.wallet }}</span>
                                </button>
                                <div class="col-12 amount-number-with p-2" v-if="unq.length == 0">У вас нет шаблонов.</div>
                            </div>
                        </div>-->
                        <div class="form-group">
                            <label>
                                Сумма 
                                <span class="text-danger">*</span>
                            </label> 
                            <input 
                                type="number" 
                                placeholder="Введите сумму" 
                                class="form-control" 
                                v-model="amount"
                            />
                        </div>
                        <div class="flex flex-bottom">
                            <div class="withdraw_info">
                                <div>К зачислению: <strong>{{ (amount * ((100 - $root.user.rank.comission) / 100)).toFixed(2) }}</strong></div>
                                <div>Комиссия: <strong>{{ parseFloat($root.user.rank.comission).toFixed(2) }}%</strong></div>
                            </div>
                            <div>Лимит одной выплаты: <strong>{{ systemGet.min }}</strong> ₽ - <strong>{{ systemGet.max }}</strong> ₽</div>
                        </div>
                        <div class="rows with-form-bottom">
                            <button 
                                class="button blue" 
                                style="margin: 10px auto 0px; width: 100%;"
                                @click="create"
                            >
                                Создать выплату
                            </button>
                        </div>
                    </div>
                    <div class="with_desc" style="text-align: center;">
                        <svg
                            viewBox="0 0 16 16"
                            width="1em"
                            height="1em"
                            focusable="false"
                            role="img"
                            aria-label="exclamation circle fill"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor"
                            class="bi-exclamation-circle-fill mr-1 b-icon bi"
                            style="color: rgb(30, 78, 255);"
                        >
                            <g><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"></path></g>
                        </svg>
                        Внимание! Вывод на платежные системы может занимать до 24 часов!<br />
                    </div>
                </div>
            </div>
        </div>
        <div class="content history cards col-12 mt-5 p-0" v-if="!isLoading">
            <table>
                <thead>
                    <tr>
                        <th>Система</th>
                        <th>Сумма</th>
                        <th>Кошелек</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="withdraw in withdraws" :key="withdraw.id">
                        <td class="system_wallet">
                            <img :src="'/assets/image/' + withdraw.system + '.png'" />
                        </td>
                        <td>{{ parseFloat(withdraw.sumWithCom).toFixed(2) }}</td>
                        <td :class="[wallet == 'hide' ? 'hide' : '']">{{ withdraw.wallet }}</td>

                        <td class="expect text-warning" v-if="withdraw.status == 0">
                            <span @click="returnWithdraw(withdraw.id)">Отменить</span>
                        </td>
                        <td class="expect text-success" v-if="withdraw.status == 1">Выполнено</td>
                        <td class="expect text-danger" v-if="withdraw.status == 2">{{ withdraw.reason || 'Отменено' }} </td>
                        <td class="expect text-primary" v-if="withdraw.status == 3">Обработка</td>

                        <td>{{ $moment(withdraw.created_at).format('lll') }}</td>
                    </tr>
                    <td colspan="6" class="p-3" v-if="withdraws.length == 0">История пуста</td>
                </tbody>
            </table>
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
            withdraws: [],
            showPurse: false,
            showPurseBank: false,
            amount: null,
            wallet: null,
            systems: [
                {
                    sys: "qiwi",
                    comissia: 10,
                    placeholder: "Номер кошелька +7XXXXXXXXX",
                    min: 500,
                    max: 5000,
                    title: "QIWI кошелек"
                }, 
                {
                    sys: "fkwallet",
                    comissia: 10,
                    placeholder: "Номер кошелька FXXXXXXX",
                    min: 200,
                    max: 5000,
                    title: "FK кошелек"
                }, 
                {
                    sys: "sbp",
                    comissia: 10,
                    placeholder: "Номер кошелька +7XXXXXXXXX",
                    min: 1500,
                    max: 10000,
                    title: "СБП номер"
                }, 
                {
                    sys: "trc20",
                    comissia: 10,
                    placeholder: "Номер кошелька 0xXXXXX",
                    min: 3000,
                    max: 15000,
                    title: "Tether TRC20 кошелек"
                }, 
                {
                    sys: "card",
                    comissia: 10,
                    placeholder: "Номер карты",
                    min: 1500,
                    max: 15000,
                    title: "Номер карты"
                }
            ],
            unq: [],
            system: 'qiwi'
        }
    },
    methods: {
        create() {
            if(!this.amount || !this.wallet) {
                return this.$root.$emit('noty', {
                    title: 'Ошибка',
                    text: 'Заполните все поля',
                    type: 'error'
                })
            }

            this.$root.axios.post('/withdraw/create', {
                sum: this.amount,
                wallet: this.wallet,
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

                this.withdraws.unshift(data.withdraw)
                this.$root.user.balance = data.balance

                this.$root.$emit('noty', {
                    title: 'Успешно',
                    text: 'Заявка на вывод создана',
                    type: 'success'
                })
            })
        },
        returnWithdraw(id) {
            this.$root.axios.post('/withdraw/decline', {
                id
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

                this.withdraws = this.withdraws.map(item => 
                    item.id !== id
                        ? item
                        : {...item, status: 2}
                )
                
                this.$root.user.balance = data.balance
            })
        },
        init() {
            this.$root.axios.post('/withdraw/init')
            .then(response => {
                const {data} = response

                this.isLoading = false
                this.unq = data.unq
                this.withdraws = data.data
            })
        },
        selectPurse(id) {
            let select = this.unq.find(item => item.id === id);

            this.system = select.system;
            this.wallet = select.wallet
            this.showPurse = false
        }
    },
    mounted() {
        this.init()
    },
    computed: {
        systemGet: function() {
            return this.systems.find(item => item.sys == this.system)
        }
    }
}
</script>

<style scoped>
td {
    max-width: 100px;
    word-break: break-all;
}
/* Mobile table fix for long wallet addresses */
@media (max-width: 768px) {
    table {
        font-size: 12px;
    }
    td, th {
        padding: 6px 4px;
    }
    td:nth-child(3) {
        max-width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
}
.card-header {
    text-align: center;
}
.col-6 {
    margin: 0 auto;
}
table tr {
    font-weight: 400 !important;
}
table {
    text-align: center;
    width: 100%;
}
.ww-3 {
    font-size: 20px;
    font-weight: 600;
    right: 15px;
    top: 44%;
}
.accor,
.ww-3 {
    position: absolute;
}
.withdraw .flex-bottom {
    flex-wrap: wrap;
}
.accor {
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
    border-left: 1px solid #ced4da;
    border-right: 1px solid #ced4da;
    width: 100%;
}
.withdraw_info {
    display: flex;
    justify-content: space-between;
    padding-right: 1rem;
    width: 95%;
}
.with_desc {
    margin-top: 0.5rem;
}
.ww-3:hover {
    cursor: pointer;
}
.amount-number-with {
    background-color: #fff;
    border-bottom: 1px solid #ced4da;
    color: #2e3956 !important;
    position: relative;
    width: 100%;
}
.amount-number-with:last-child {
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
}
.amount-number-with .system_wallet img {
    left: 0;
    position: absolute;
    top: 0;
}
.topic {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}
@media (max-width: 460px) {
    .withdraw_info {
        flex-direction: column;
        font-size: 14px;
    }
}
.hide {
    filter: blur(5px);
}
.text-warning > span {
    cursor: pointer
}
.withdraw .rows {
    flex-wrap: wrap;
    gap: 10px;
}
</style>