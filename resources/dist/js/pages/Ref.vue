<template>
    <div>
        <Ellipsis v-if="isLoading" />
        <div class="content cards col-12 p-0" v-else>
            <div class="pt-4 pb-4 col-12">
                <div class="ref container">
                    <div class="blue ref-title">Получай <strong>{{ refReward }} ₽</strong> за каждого реферала</div>
                    Как только ваш реферал 1 lvl получит одноразовый бонус, вы получите на реферальный баланс {{ refReward }} ₽<br />
                    <br />
                    <h6>Играйте вместе с друзьями и зарабатывайте еще больше!</h6>
                    <label class="desc">
                        Каждый раз, когда ваш реферал делает депозит, вы будете получать % от его пополнения. <br>
                        Это правило также распространяется на рефералов ваших друзей, которых привели в игру вы
                    </label>
                    <div>
                        <div class="col-5 p-0">
                            <h6 class="mt-3">Ваша реферальная ссылка:</h6>
                            <div class="input-group mb-2 col-12 p-0">
                                <input 
                                    type="text" 
                                    disabled="disabled" 
                                    class="form-control bg-white" 
                                    v-model="link"
                                />
                                <div class="input-group-append">
                                    <div class="input-group-text blue" @click="copyRef">
                                        Скопировать
                                    </div>
                                </div>
                            </div>
                            <!-- QR Code -->
                            <div class="qr-code-block mt-3">
                                <h6>QR-код для приглашения:</h6>
                                <div class="qr-code-wrapper">
                                    <img 
                                        :src="qrCodeUrl" 
                                        alt="QR Code" 
                                        class="qr-code-img"
                                        v-if="link"
                                    />
                                </div>
                                <small class="qr-hint">Отсканируйте для быстрого перехода</small>
                                <button class="qr-download-btn" @click="downloadQR" v-if="link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    Скачать QR
                                </button>
                            </div>
                        </div>
                        <div class="col-5 p-0">
                            <h6 class="mt-3">Ваш доход:</h6>
                            <div class="input-group mb-2 col-12 p-0">
                                <input 
                                    type="text" 
                                    disabled="disabled" 
                                    class="form-control bg-white" 
                                    v-model="refIncome"
                                />
                                <div class="input-group-append">
                                    <div class="input-group-text blue" @click="take">
                                        Забрать
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="history">
                        <table class="mt-3 mb-3">
                            <thead>
                                <tr>
                                    <th>Уровень</th>
                                    <th>Рефералов</th>
                                    <th>Доход</th>
                                    <th>Процент</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1 уровень</td>
                                    <td>{{ data.lvl_1.count }}</td>
                                    <td class="text-success">{{ parseFloat(data.lvl_1.income).toFixed(2) }}</td>
                                    <td>15%</td>
                                </tr>
                                <tr>
                                    <td>2 уровень</td>
                                    <td>{{ data.lvl_2.count }}</td>
                                    <td class="text-success">{{ parseFloat(data.lvl_2.income).toFixed(2) }}</td>
                                    <td>3%</td>
                                </tr>
                                <tr>
                                    <td>3 уровень</td>
                                    <td>{{ data.lvl_3.count }}</td>
                                    <td class="text-success">{{ parseFloat(data.lvl_3.income).toFixed(2) }}</td>
                                    <td>2%</td>
                                </tr>
                                <tr class="all_">
                                    <td>Всего:</td>
                                    <td>{{ data.refAll }}</td>
                                    <td class="text-success_">{{ parseFloat(data.incomeAll).toFixed(2) }} ₽</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <h6 class="mt-3">Как это работает?</h6>
                        <label class="desc">Все ваши рефералы станут для вас рефералами 1-го уровня и будут приносить вам 10% от депозитов.</label>
                        <label class="desc">Рефералы ваших рефералов (1-го уровня) станут для вас рефералами 2-го уровня и будут приносить 3% от депозитов.</label>
                        <label class="desc">Рефералы 2-го уровня станут для вас рефералами 3-го уровня и будут вам приносить 2% от депозитов.</label>
                    </div>
                    <div>
                        <h6 class="mt-3">Условиями работы партнерской программы запрещено:</h6>
                        <label class="desc">Привлечение рефералов с помощью спама!</label><br />
                        <label class="desc">Использование собственных или специально зарегистрированных аккаунтов.</label><br />
                        <label class="desc">Привлечение рефералов путем обмана.</label><br />
                        <label class="desc">Нарушители будут оштрафованы или заблокированы в партнерской программе.</label>
                    </div>
                </div>
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
            data: null,
            refIncome: 0,
            refReward: 0,
            link: null
        }
    },
    methods: {
        init() {
            this.$root.axios.post('/referral/get')
            .then(response => {
                const {data} = response

                this.isLoading = false
                this.refIncome = parseFloat(data.ref_income).toFixed(2)
                this.refReward = data.ref_reward
                this.link = data.link
                this.data = data.data
            })
        },
        take() {
            this.$root.axios.post('/referral/take')
            .then(response => {
                const {data} = response

                if(data.error) {
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        text: data.message,
                        type: 'error'
                    })
                }
                
                this.refIncome = '0.00'
                this.$root.user.balance = data.balance
            })
        },
        copyRef() {
            this.$clipboard(this.link)
            this.$root.$emit('noty', {
                title: 'Успешно',
                text: 'Сохранено в буфер обмена',
                type: 'success'
            })
        },
        downloadQR() {
            const downloadUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=png&data=${encodeURIComponent(this.link)}`;
            fetch(downloadUrl)
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'referral-qr-code.png';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                    this.$root.$emit('noty', {
                        title: 'Успешно',
                        text: 'QR-код скачан',
                        type: 'success'
                    });
                })
                .catch(() => {
                    window.open(downloadUrl, '_blank');
                });
        }
    },
    mounted() {
        this.init()
    },
    computed: {
        qrCodeUrl() {
            if (!this.link) return '';
            return `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(this.link)}`;
        }
    }
}
</script>

<style scoped>
.ref {
    padding: 10 15px;
}
.ref .desc {
    font-size: 15px;
}
.refBox {
    border: 1px solid #87919a;
    border-radius: 5px;
    flex: auto;
    margin-right: 15px;
    padding: 5px;
}
.refCount {
    font-size: 14px;
}
.refIncome {
    font-size: 15px;
}
.refBox:last-child {
    margin: 0;
}
.refLvl {
    font-size: 12px;
}
.blue {
    font-size: 14px;
}
.ref-title {
    border-radius: 3px;
    color: #fff;
    font-size: 15px;
    margin-bottom: 1rem;
    padding: 10px;
    text-align: center;
}
.text-success_ {
    color: #28a745!important;
}
.all_ {
    font-weight: 700;
}
/* QR Code styles */
.qr-code-block {
    text-align: center;
    padding: 15px;
    background: rgba(37, 97, 208, 0.05);
    border-radius: 10px;
    border: 1px solid rgba(37, 97, 208, 0.2);
}
.qr-code-wrapper {
    display: inline-block;
    padding: 10px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.qr-code-img {
    width: 150px;
    height: 150px;
    display: block;
}
.qr-hint {
    display: block;
    margin-top: 10px;
    color: #6c757d;
    font-size: 12px;
}
.qr-download-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 12px;
    padding: 8px 16px;
    background: linear-gradient(45deg, #2561d0, #1e4eff);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.qr-download-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 97, 208, 0.3);
}
.qr-download-btn svg {
    width: 16px;
    height: 16px;
}
</style>