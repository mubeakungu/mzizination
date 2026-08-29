<template>
    <div class="modal-overlay" v-if="visible" @click.self="close">
        <div class="tg-connect-modal">
            <div class="tg-connect-head">
                <div class="tg-connect-title">
                    <img src="/assets/image/tg.svg" width="22" /> Привязка Telegram
                </div>
                <div class="tg-connect-close" @click="close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
            </div>

            <div class="tg-connect-body">
                <div class="tg-connect-hero">
                    <div class="tg-hero-icon">
                        <img src="/assets/image/tg.svg" width="28" />
                    </div>
                    <div class="tg-hero-text">
                        <div class="tg-hero-title">Получайте бонусы!</div>
                        <div class="tg-hero-desc">Привяжите Telegram и получите доступ к ежедневным бонусам</div>
                    </div>
                </div>

                <div class="tg-connect-steps">
                    <div class="tg-step">
                        <div class="tg-step-num">1</div>
                        <div class="tg-step-content">
                            <div class="tg-step-title">Подпишитесь на канал</div>
                            <a :href="$root.config.tg_channel" target="_blank" class="tg-step-link">
                                <img src="/assets/image/tg.svg" width="16" /> @valuba_casino
                            </a>
                        </div>
                    </div>
                    <div class="tg-step">
                        <div class="tg-step-num">2</div>
                        <div class="tg-step-content">
                            <div class="tg-step-title">Получите код</div>
                            <div class="tg-step-desc">Нажмите кнопку ниже</div>
                        </div>
                    </div>
                    <div class="tg-step">
                        <div class="tg-step-num">3</div>
                        <div class="tg-step-content">
                            <div class="tg-step-title">Отправьте боту</div>
                            <div class="tg-step-desc">Код привязки в бота</div>
                        </div>
                    </div>
                </div>

                <div class="tg-connect-action" v-if="$root.user !== null">
                    <div v-if="!linkCode && !isLoading">
                        <button class="tg-btn-get-code" @click="getCode">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                            </svg>
                            Получить код привязки
                        </button>
                    </div>
                    
                    <div v-if="isLoading" class="tg-loading">
                        <svg class="tg-spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                        </svg>
                        Загрузка...
                    </div>

                    <div v-if="linkCode" class="tg-code-block">
                        <div class="tg-code-label">Ваш код привязки:</div>
                        <div class="tg-code-value" @click="copyCode">
                            <span>{{ linkCode }}</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                        </div>
                        <div class="tg-code-timer">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Код действителен 10 минут
                        </div>
                        
                        <a :href="$root.config.tg_bot + '?start=' + linkCode" target="_blank" class="tg-btn-connect">
                            <img src="/assets/image/tg.svg" width="20" /> Открыть бота и привязать
                        </a>
                    </div>
                </div>

                <div class="tg-connect-benefits">
                    <div class="tg-benefit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0088cc" stroke-width="2">
                            <path d="M20 12v10H4V12"></path>
                            <path d="M2 7h20v5H2z"></path>
                            <path d="M12 22V7"></path>
                            <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                        </svg>
                        <span>Ежедневные бонусы</span>
                    </div>
                    <div class="tg-benefit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0088cc" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span>Уведомления</span>
                    </div>
                    <div class="tg-benefit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0088cc" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Эксклюзивные акции</span>
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
            linkCode: null,
            isLoading: false
        }
    },
    methods: {
        show() {
            this.visible = true;
            this.linkCode = null;
        },
        close() {
            this.visible = false;
        },
        getCode() {
            this.isLoading = true;
            this.$root.axios.post('/profile/telegram/link')
            .then(response => {
                this.linkCode = response.data.code;
                this.isLoading = false;
            })
            .catch(err => {
                this.isLoading = false;
                this.$root.$emit('noty', {
                    title: 'Ошибка',
                    text: 'Не удалось получить код',
                    type: 'error'
                });
            });
        },
        copyCode() {
            navigator.clipboard.writeText(this.linkCode);
            this.$root.$emit('noty', {
                title: 'Скопировано',
                text: 'Код скопирован в буфер обмена',
                type: 'success'
            });
        }
    },
    mounted() {
        this.$root.$on('openConnectTg', () => this.show());
    },
    sockets: {
        connectTelegram(data) {
            if(this.$root.user !== null && data.user_id == this.$root.user.id) {
                this.close();
                this.linkCode = null;
                this.$root.user.tg_id = data.tg_id;
                return this.$root.$emit('noty', {
                    title: 'Успех',
                    text: 'Telegram успешно привязан!',
                    type: 'success'
                })
            }
        }
    }
}
</script>
