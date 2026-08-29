<template>
    <div class="ton-payment-modal" v-if="visible">
        <div class="ton-modal-overlay" @click.self="close"></div>
        <div class="ton-modal-content">
            <div class="ton-modal-header">
                <div class="ton-title">
                    <img src="/assets/image/ton.svg" width="24" alt="TON">
                    <span>Пополнение TON</span>
                </div>
                <div class="ton-close" @click="close">
                    <i class="fa fa-times"></i>
                </div>
            </div>

            <div class="ton-modal-body">
                <div class="ton-loading" v-if="loading">
                    <i class="fa fa-spinner fa-spin"></i>
                </div>

                <div v-else>
                    <div class="ton-rate-info">
                        <span class="rate-label">Курс:</span>
                        <span class="rate-value">1 TON = {{ rate.toFixed(2) }} ₽</span>
                    </div>

                    <div class="ton-min-info">
                        Минимум: <strong>{{ minTon.toFixed(2) }} TON</strong> (~{{ minRub }} ₽)
                    </div>

                    <div class="ton-qr">
                        <img :src="qrCode" alt="QR Code">
                    </div>

                    <div class="ton-details">
                        <div class="detail-group">
                            <label>Адрес кошелька:</label>
                            <div class="detail-value copyable" @click="copy(wallet)">
                                <span>{{ shortWallet }}</span>
                                <i class="fa fa-copy"></i>
                            </div>
                        </div>

                        <div class="detail-group important">
                            <label>⚠️ MEMO (обязательно!):</label>
                            <div class="detail-value copyable memo" @click="copy(memo)">
                                <span>{{ memo }}</span>
                                <i class="fa fa-copy"></i>
                            </div>
                        </div>
                    </div>

                    <div class="ton-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span>Укажите MEMO при переводе! Без него платеж не зачислится автоматически.</span>
                    </div>

                    <div class="ton-status" :class="statusClass">{{ statusText }}</div>

                    <div class="ton-auto-check">
                        <i class="fa fa-refresh fa-spin"></i> Платеж зачислится автоматически
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
            visible: false,
            loading: true,
            rate: 120,
            minRub: 12,
            minTon: 0.1,
            wallet: '',
            memo: '',
            qrCode: null,
            checkInterval: null,
            rateInterval: null,
            status: 0
        }
    },
    computed: {
        shortWallet() {
            if (!this.wallet) return '';
            return this.wallet.slice(0, 10) + '...' + this.wallet.slice(-8);
        },
        statusClass() {
            if (this.status === 1) return 'status-success';
            return 'status-pending';
        },
        statusText() {
            if (this.status === 1) return '✓ Платеж получен!';
            return '⏳ Ожидание платежа...';
        }
    },
    methods: {
        show() {
            this.visible = true;
            this.loading = true;
            this.status = 0;
            this.loadInfo();
        },
        close() {
            this.visible = false;
            this.clearIntervals();
        },
        async loadInfo() {
            try {
                const res = await this.$root.axios.post('/ton/info');
                this.rate = res.data.rate;
                this.minRub = res.data.min_rub;
                this.minTon = res.data.min_ton;
                this.wallet = res.data.wallet;
                this.memo = res.data.memo;
                this.generateQR();
                this.startAutoCheck();
                this.startRateUpdate();
            } catch (e) {}
            this.loading = false;
        },
        generateQR() {
            const link = `ton://transfer/${this.wallet}?text=${this.memo}`;
            this.qrCode = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(link)}`;
        },
        startAutoCheck() {
            this.checkInterval = setInterval(() => this.checkBalance(), 10000);
        },
        startRateUpdate() {
            // Обновляем курс каждые 3 минуты (180000 мс)
            this.rateInterval = setInterval(() => this.updateRate(), 180000);
        },
        async updateRate() {
            try {
                const res = await this.$root.axios.post('/ton/rate');
                if (res.data.rate) {
                    this.rate = res.data.rate;
                    this.minTon = res.data.min_ton;
                }
            } catch (e) {}
        },
        clearIntervals() {
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
                this.checkInterval = null;
            }
            if (this.rateInterval) {
                clearInterval(this.rateInterval);
                this.rateInterval = null;
            }
        },
        async checkBalance() {
            try {
                const res = await this.$root.axios.post('/user/init');
                if (res.data.user && res.data.user.balance > this.$root.user.balance) {
                    const diff = res.data.user.balance - this.$root.user.balance;
                    this.$root.user.balance = res.data.user.balance;
                    this.status = 1;
                    this.$root.$emit('noty', {
                        title: 'Успешно!',
                        text: `+${diff.toFixed(2)} ₽`,
                        type: 'success'
                    });
                }
            } catch (e) {}
        },
        copy(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.$root.$emit('noty', { title: 'Скопировано', text: '', type: 'success' });
            });
        }
    },
    mounted() {
        this.$root.$on('openTonPayment', () => this.show());
    },
    beforeDestroy() {
        this.clearIntervals();
    }
}
</script>

<style scoped>
.ton-payment-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; }
.ton-modal-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); }
.ton-modal-content { position: relative; background: #1a1f2e; border-radius: 16px; width: 90%; max-width: 400px; max-height: 90vh; overflow-y: auto; }
.ton-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
.ton-title { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 600; color: #fff; }
.ton-close { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; cursor: pointer; color: #888; }
.ton-close:hover { background: rgba(255,255,255,0.1); color: #fff; }
.ton-modal-body { padding: 20px; }
.ton-loading { text-align: center; padding: 40px; color: #888; font-size: 32px; }
.ton-rate-info { background: rgba(0,136,204,0.1); border: 1px solid rgba(0,136,204,0.3); border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; }
.rate-label { font-size: 12px; color: #888; }
.rate-value { font-size: 16px; font-weight: 600; color: #0088cc; display: block; }
.ton-min-info { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 10px; padding: 12px; text-align: center; margin-bottom: 16px; color: #10b981; }
.ton-qr { text-align: center; margin-bottom: 16px; }
.ton-qr img { width: 180px; height: 180px; border-radius: 12px; background: #fff; padding: 10px; }
.ton-details { margin-bottom: 16px; }
.detail-group { margin-bottom: 12px; }
.detail-group label { display: block; font-size: 12px; color: #888; margin-bottom: 4px; }
.detail-group.important label { color: #f59e0b; font-weight: 600; }
.detail-value { background: rgba(255,255,255,0.05); border-radius: 8px; padding: 12px; color: #fff; display: flex; align-items: center; justify-content: space-between; }
.detail-value.copyable { cursor: pointer; }
.detail-value.copyable:hover { background: rgba(255,255,255,0.1); }
.detail-value.memo { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); font-weight: 600; font-size: 16px; }
.ton-warning { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 10px; padding: 12px; font-size: 13px; color: #f59e0b; display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
.ton-status { padding: 12px; border-radius: 10px; margin-bottom: 12px; text-align: center; font-weight: 500; }
.ton-status.status-pending { background: rgba(245,158,11,0.1); color: #f59e0b; }
.ton-status.status-success { background: rgba(16,185,129,0.1); color: #10b981; }
.ton-auto-check { display: flex; align-items: center; justify-content: center; gap: 10px; color: #888; font-size: 13px; }
</style>
