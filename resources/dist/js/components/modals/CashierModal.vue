<template>
    <div class="modal-overlay" v-if="visible" @click.self="close">
        <div class="cashier-modal">
            <div class="cashier-head">
                <div class="cashier-title">
                    <i class="flaticon-coins"></i> Касса
                </div>
                <div class="cashier-close" @click="close">
                    <i class="fa fa-times"></i>
                </div>
            </div>

            <div class="cashier-tabs">
                <div 
                    class="cashier-tab" 
                    :class="{active: activeTab === 'deposit'}"
                    @click="activeTab = 'deposit'"
                >
                    <i class="fa fa-plus-circle"></i> Пополнить
                </div>
                <div 
                    class="cashier-tab" 
                    :class="{active: activeTab === 'withdraw'}"
                    @click="activeTab = 'withdraw'"
                >
                    <i class="fa fa-arrow-circle-down"></i> Вывести
                </div>
                <div 
                    class="cashier-tab" 
                    :class="{active: activeTab === 'history'}"
                    @click="activeTab = 'history'"
                >
                    <i class="fa fa-history"></i> История
                </div>
            </div>

            <div class="cashier-body">
                <div class="cashier-error" v-if="error">
                    <i class="fa fa-exclamation-circle"></i> {{ error }}
                </div>
                
                <!-- DEPOSIT TAB -->
                <div v-if="activeTab === 'deposit'" class="tab-content">
                    <div class="cashier-systems">
                        <div 
                            class="cashier-system" 
                            :class="{active: depSystem === 'fk'}"
                            @click="depSystem = 'fk'"
                        >
                            <img src="/assets/image/fkwallet.png" alt="FK">
                            <span>FK Wallet</span>
                        </div>
                        <div 
                            class="cashier-system" 
                            :class="{active: depSystem === 'sbp'}"
                            @click="depSystem = 'sbp'"
                        >
                            <img src="/assets/image/sbp.png" alt="SBP">
                            <span>СБП</span>
                        </div>
                        <div 
                            class="cashier-system" 
                            :class="{active: depSystem === 'card'}"
                            @click="depSystem = 'card'"
                        >
                            <img src="/assets/image/card.png" alt="Card">
                            <span>Карта</span>
                        </div>
                        <div 
                            class="cashier-system" 
                            :class="{active: depSystem === 'qiwi'}"
                            @click="depSystem = 'qiwi'"
                        >
                            <img src="/assets/image/qiwi.png" alt="Qiwi">
                            <span>Qiwi</span>
                        </div>
                    </div>

                    <div class="cashier-form">
                        <div class="form-group">
                            <label>Сумма пополнения (₽)</label>
                            <input type="number" v-model="depSum" class="cashier-input" :placeholder="`Минимум ${getDepSystemConfig.min} ₽`">
                            <div class="quick-amounts">
                                <button @click="depSum = 100">+100</button>
                                <button @click="depSum = 500">+500</button>
                                <button @click="depSum = 1000">+1k</button>
                                <button @click="depSum = 5000">+5k</button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Промокод (если есть)</label>
                            <input type="text" v-model="promocode" class="cashier-input" placeholder="PROMO">
                        </div>

                        <button class="cashier-btn-action" @click="createDeposit" :disabled="isLoading">
                            {{ isLoading ? 'Загрузка...' : 'Перейти к оплате' }}
                        </button>
                        
                         <div class="deposit-warning">
                            <i class="fa fa-info-circle"></i>
                            <span>Бонус +10% при пополнении от 150₽ в воскресенье!</span>
                        </div>
                    </div>
                </div>

                <!-- WITHDRAW TAB -->
                <div v-if="activeTab === 'withdraw'" class="tab-content">
                    <div class="cashier-systems">
                        <div 
                            class="cashier-system" 
                            v-for="sys in withdrawSystemsConfig"
                            :key="sys.sys"
                            :class="{active: withSystem === sys.sys}"
                            @click="withSystem = sys.sys"
                        >
                            <img :src="`/assets/image/${sys.sys == 'fkwallet' ? 'fkwallet' : sys.sys}.png`" :alt="sys.title">
                            <span>{{ sys.title_short || sys.title }}</span>
                        </div>
                    </div>

                    <div class="cashier-form">
                        <div class="form-group">
                            <label>{{ getWithSystemConfig.title }}</label>
                            <input type="text" v-model="withWallet" class="cashier-input" :placeholder="getWithSystemConfig.placeholder">
                        </div>

                        <div class="form-group">
                            <label>Сумма вывода (₽)</label>
                            <input type="number" v-model="withAmount" class="cashier-input" placeholder="Введите сумму">
                            <div class="withdraw-info">
                                <span>Комиссия: {{ $root.user.rank ? parseFloat($root.user.rank.comission).toFixed(1) : 0 }}%</span>
                                <span>Лимит: {{ getWithSystemConfig.min }} - {{ getWithSystemConfig.max }} ₽</span>
                            </div>
                        </div>

                        <div class="withdraw-calc" v-if="withAmount">
                            Получите: <strong>{{ (withAmount * ((100 - ($root.user.rank ? $root.user.rank.comission : 0)) / 100)).toFixed(2) }} ₽</strong>
                        </div>

                        <button class="cashier-btn-action withdraw-btn" @click="createWithdraw" :disabled="isLoading">
                            {{ isLoading ? 'Создание...' : 'Создать заявку' }}
                        </button>
                    </div>
                </div>

                <!-- HISTORY TAB -->
                <div v-if="activeTab === 'history'" class="tab-content history-tab">
                    <div class="history-filter">
                        <button :class="{active: historyFilter === 'all'}" @click="historyFilter = 'all'">Все</button>
                        <button :class="{active: historyFilter === 'dep'}" @click="historyFilter = 'dep'">Пополнения</button>
                        <button :class="{active: historyFilter === 'with'}" @click="historyFilter = 'with'">Выводы</button>
                    </div>

                    <div class="history-list">
                        <div 
                            class="history-item" 
                            :class="{'expanded': expandedItem === item.id + '-' + item.type}"
                            v-for="(item, idx) in filteredHistory" 
                            :key="idx"
                            @click="toggleExpand(item)"
                        >
                            <div class="h-main">
                                <div class="h-icon" :class="item.type === 'deposit' ? 'h-icon-deposit' : 'h-icon-withdraw'">
                                    <svg v-if="item.type === 'deposit'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="19" x2="12" y2="5"></line>
                                        <polyline points="5 12 12 5 19 12"></polyline>
                                    </svg>
                                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </div>
                                <div class="h-info">
                                    <div class="h-title">{{ item.type === 'deposit' ? 'Пополнение' : 'Вывод' }} ({{ item.system_name }})</div>
                                    <div class="h-date">{{ $moment(item.created_at).format('DD.MM.YYYY HH:mm') }}</div>
                                </div>
                                <div class="h-amount">
                                    <div :class="item.type === 'deposit' ? 'text-success' : 'text-danger'">
                                        {{ item.type === 'deposit' ? '+' : '-' }}{{ parseFloat(item.sum).toFixed(0) }} ₽
                                    </div>
                                    <div class="h-status" :class="getStatusClass(item)">
                                        {{ getStatusText(item) }}
                                    </div>
                                </div>
                                <div class="h-expand-icon">
                                    <svg :style="expandedItem === item.id + '-' + item.type ? 'transform: rotate(180deg)' : ''" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Expanded Details -->
                            <div class="h-details" v-if="expandedItem === item.id + '-' + item.type" @click.stop>
                                <div class="h-detail-row">
                                    <span class="h-detail-label">ID транзакции:</span>
                                    <span class="h-detail-value">#{{ item.id }}</span>
                                </div>
                                <div class="h-detail-row" v-if="item.type === 'withdraw'">
                                    <span class="h-detail-label">Кошелек:</span>
                                    <span class="h-detail-value">{{ item.wallet }}</span>
                                </div>
                                <div class="h-detail-row" v-if="item.type === 'withdraw' && item.sumWithCom">
                                    <span class="h-detail-label">К получению:</span>
                                    <span class="h-detail-value">{{ parseFloat(item.sumWithCom).toFixed(2) }} ₽</span>
                                </div>
                                <div class="h-detail-row" v-if="item.reason">
                                    <span class="h-detail-label">Причина отмены:</span>
                                    <span class="h-detail-value text-danger">{{ item.reason }}</span>
                                </div>
                                <div class="h-detail-row">
                                    <span class="h-detail-label">Дата создания:</span>
                                    <span class="h-detail-value">{{ $moment(item.created_at).format('DD.MM.YYYY HH:mm:ss') }}</span>
                                </div>
                                
                                <!-- Cancel Button for pending withdraws -->
                                <button 
                                    v-if="item.type === 'withdraw' && item.status == 0"
                                    class="h-cancel-btn"
                                    @click.stop="cancelWithdraw(item)"
                                    :disabled="cancelLoading"
                                >
                                    <i class="fa fa-times"></i> {{ cancelLoading ? 'Отмена...' : 'Отменить вывод' }}
                                </button>
                            </div>
                        </div>
                        <div v-if="filteredHistory.length === 0" class="history-empty">
                            История пуста
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Confirm Cancel Modal -->
        <div class="confirm-modal-overlay" v-if="showConfirmModal" @click.self="showConfirmModal = false">
            <div class="confirm-modal">
                <div class="confirm-icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <div class="confirm-title">Отменить вывод?</div>
                <div class="confirm-text">
                    Вы уверены, что хотите отменить вывод на сумму 
                    <strong>{{ confirmItem ? parseFloat(confirmItem.sum).toFixed(0) : 0 }} ₽</strong>?
                    <br>Средства вернутся на ваш баланс.
                </div>
                <div class="confirm-buttons">
                    <button class="confirm-btn confirm-btn-cancel" @click="showConfirmModal = false">
                        Нет, оставить
                    </button>
                    <button class="confirm-btn confirm-btn-confirm" @click="confirmCancelWithdraw" :disabled="cancelLoading">
                        <i class="fa fa-check" v-if="!cancelLoading"></i>
                        <i class="fa fa-spinner fa-spin" v-else></i>
                        {{ cancelLoading ? 'Отмена...' : 'Да, отменить' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            visible: false,
            activeTab: 'deposit',
            isLoading: false,
            error: null,
            
            // Deposit Data
            depSystem: 'fk',
            depSum: null,
            promocode: '',
            
            // Withdraw Data
            withSystem: 'qiwi',
            withAmount: null,
            withWallet: '',
            
            // History Data
            payments: [],
            tonPayments: [],
            withdraws: [],
            historyFilter: 'all', // all, dep, with
            expandedItem: null,
            cancelLoading: false,
            showConfirmModal: false,
            confirmItem: null,

            // Config
            withdrawSystemsConfig: [
                { sys: "qiwi", placeholder: "+7XXXXXXXXX", min: 500, max: 5000, title: "QIWI", title_short: "QIWI" }, 
                { sys: "fkwallet", placeholder: "FXXXXXXX", min: 200, max: 5000, title: "FK Wallet", title_short: "FK Wallet" }, 
                { sys: "sbp", placeholder: "+7XXXXXXXXX", min: 1500, max: 10000, title: "СБП", title_short: "СБП" }, 
                { sys: "trc20", placeholder: "0xXXXXX", min: 3000, max: 15000, title: "USDT TRC20", title_short: "USDT" }, 
                { sys: "card", placeholder: "Номер карты", min: 1500, max: 15000, title: "Банковская карта", title_short: "Карта" }
            ],
            depositSystemsConfig: [
                { sys: "fk", title: "FK Wallet", min: 100 },
                { sys: "sbp", title: "СБП", min: 300 },
                { sys: "card", title: "Карта", min: 500 },
                { sys: "qiwi", title: "Qiwi", min: 100 },
                { sys: "ton", title: "TON", min: 100 }
            ],

            // TON Data
            tonRate: 500,
            tonAmountRub: null,
            tonAmountTon: 0,
            tonLoading: false,
            tonWallet: '',
            tonPayment: null,
            tonQrCode: null,
            tonChecking: false,
            tonStatus: 0,
            tonTimerDisplay: '30:00',
            tonTimerInterval: null,
            tonCheckInterval: null,
            tonExpiresAt: null
        }
    },
    computed: {
        getWithSystemConfig() {
            return this.withdrawSystemsConfig.find(x => x.sys === this.withSystem) || this.withdrawSystemsConfig[0];
        },
        getDepSystemConfig() {
            return this.depositSystemsConfig.find(x => x.sys === this.depSystem) || { min: 100 };
        },
        unifiedHistory() {
            const deps = this.payments.map(p => ({...p, type: 'deposit', system_name: 'FK/Card', sum: p.sum}));
            const tonDeps = this.tonPayments.map(p => ({
                ...p, 
                type: 'deposit', 
                system_name: 'TON', 
                sum: p.amount_rub,
                status: p.status
            }));
            const withs = this.withdraws.map(w => ({...w, type: 'withdraw', system_name: w.system.toUpperCase(), sum: w.sumWithCom || w.sum}));
            return [...deps, ...tonDeps, ...withs].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        },
        filteredHistory() {
            if (this.historyFilter === 'dep') return this.unifiedHistory.filter(x => x.type === 'deposit');
            if (this.historyFilter === 'with') return this.unifiedHistory.filter(x => x.type === 'withdraw');
            return this.unifiedHistory;
        },
        tonStatusClass() {
            if (this.tonStatus === 1) return 'ton-status-success';
            if (this.tonStatus === 3) return 'ton-status-expired';
            return 'ton-status-pending';
        },
        tonStatusText() {
            if (this.tonStatus === 1) return '✓ Оплачено!';
            if (this.tonStatus === 3) return '✗ Истекло';
            return '⏳ Ожидание...';
        }
    },
    methods: {
        show(tab = 'deposit') {
            this.activeTab = tab;
            this.visible = true;
            this.loadData();
        },
        close() {
            this.visible = false;
        },
        loadData() {
            this.isLoading = true;
            // Load Deposits History
            this.$root.axios.post('/payment/init').then(res => {
                this.payments = res.data.payments || [];
            });
            // Load TON Payments History
            this.$root.axios.post('/ton/history').then(res => {
                this.tonPayments = res.data.payments || [];
            }).catch(() => {
                this.tonPayments = [];
            });
            // Load Withdraws Data
            this.$root.axios.post('/withdraw/init').then(res => {
                this.withdraws = res.data.data || [];
                this.isLoading = false;
            });
            // Load TON Info
            this.loadTonInfo();
        },
        // Deposit Action
        createDeposit() {
            this.error = null;
            if(!this.depSum) {
                this.error = 'Введите сумму';
                return;
            }
            
            // Client-side validation using config
            const config = this.getDepSystemConfig;
            if (this.depSum < config.min) {
                this.error = `Минимальная сумма пополнения ${config.min} руб`;
                return;
            }
            
            this.isLoading = true;
            this.$root.axios.post('/payment/create', {
                amount: this.depSum,
                code: this.promocode,
                system: this.depSystem
            }).then(res => {
                this.isLoading = false;
                if(res.data.error) {
                    this.error = res.data.message;
                    return;
                }
                location.href = res.data.url;
            }).catch(() => {
                this.isLoading = false;
                this.error = 'Ошибка соединения';
            });
        },
        // Withdraw Action
        createWithdraw() {
            this.error = null;
            if(!this.withAmount || !this.withWallet) {
                this.error = 'Заполните все поля';
                return;
            }
            
            // Client-side wallet validation
            const wallet = this.withWallet.trim();
            
            if(this.withSystem === 'trc20') {
                // TRC20 address starts with T and is 34 characters
                if(!wallet.startsWith('T') || wallet.length !== 34) {
                    this.error = 'Введите корректный TRC20 адрес (начинается с T, 34 символа)';
                    return;
                }
            }
            
            if(this.withSystem === 'qiwi' || this.withSystem === 'sbp') {
                // Phone number validation
                const phone = wallet.replace(/\D/g, '');
                if(phone.length < 10 || phone.length > 12) {
                    this.error = 'Введите корректный номер телефона';
                    return;
                }
            }
            
            if(this.withSystem === 'card') {
                // Card number validation (16 digits)
                const card = wallet.replace(/\s/g, '');
                if(!/^\d{16}$/.test(card)) {
                    this.error = 'Введите корректный номер карты (16 цифр)';
                    return;
                }
            }
            
            if(this.withSystem === 'fkwallet') {
                // FK Wallet starts with F
                if(!wallet.startsWith('F') || wallet.length < 8) {
                    this.error = 'Введите корректный FK Wallet (начинается с F)';
                    return;
                }
            }
            
            this.isLoading = true;
            this.$root.axios.post('/withdraw/create', {
                sum: this.withAmount,
                wallet: this.withWallet,
                system: this.withSystem
            }).then(res => {
                this.isLoading = false;
                if(res.data.error) {
                    this.error = res.data.message;
                    return;
                }
                
                this.withdraws.unshift(res.data.withdraw);
                this.$root.user.balance = res.data.balance;
                this.$root.$emit('noty', { title: 'Успешно', text: 'Заявка создана', type: 'success' });
                this.activeTab = 'history'; // Switch to history
            }).catch(() => {
                this.isLoading = false;
                this.error = 'Ошибка соединения';
            });
        },
        // Helpers
        toggleExpand(item) {
            const key = item.id + '-' + item.type;
            this.expandedItem = this.expandedItem === key ? null : key;
        },
        cancelWithdraw(item) {
            this.confirmItem = item;
            this.showConfirmModal = true;
        },
        confirmCancelWithdraw() {
            if(this.cancelLoading || !this.confirmItem) return;
            
            this.cancelLoading = true;
            this.$root.axios.post('/withdraw/decline', { id: this.confirmItem.id }).then(res => {
                this.cancelLoading = false;
                this.showConfirmModal = false;
                
                if(res.data.error) {
                    this.$root.$emit('noty', { title: 'Ошибка', text: res.data.message, type: 'error' });
                    return;
                }
                
                // Update local data
                const idx = this.withdraws.findIndex(w => w.id === this.confirmItem.id);
                if(idx !== -1) {
                    this.withdraws[idx].status = 2;
                }
                
                this.$root.user.balance = res.data.balance;
                this.$root.$emit('noty', { title: 'Успешно', text: 'Вывод отменен, средства возвращены', type: 'success' });
                this.expandedItem = null;
                this.confirmItem = null;
            }).catch(() => {
                this.cancelLoading = false;
                this.$root.$emit('noty', { title: 'Ошибка', text: 'Ошибка соединения', type: 'error' });
            });
        },
        getStatusClass(item) {
            if (item.status == 1) return 'text-success'; // Completed
            if (item.status == 0) return 'text-warning'; // Pending
            if (item.status == 2) return 'text-danger';  // Cancelled
            return 'text-muted';
        },
        getStatusText(item) {
            if (item.status == 1) return 'Успешно';
            if (item.status == 0) return 'Ожидание';
            if (item.status == 2) return 'Отмена';
            if (item.status == 3) return 'Обработка';
            return 'Неизвестно';
        },
        // TON Methods
        loadTonInfo() {
            this.tonLoading = true;
            this.$root.axios.post('/ton/info').then(res => {
                this.tonRate = res.data.rate || 500;
                this.tonWallet = res.data.wallet || '';
                this.tonLoading = false;
                this.calcTonAmount();
            }).catch(() => {
                this.tonLoading = false;
            });
        },
        // TON - Open Modal
        openTonModal() {
            this.close();
            this.$root.$emit('openTonPayment');
        }
    },
    mounted() {
        this.$root.$on('openCashier', (tab) => this.show(tab));
    }
}
</script>

<style scoped>
/* Scoped styles are now moved to global CSS (app.css / dark.css) */
</style>