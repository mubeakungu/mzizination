<template>
    <div>
        <Ellipsis v-if="isLoading || $root.user == null" />   
        <div class="container" v-else>
            <div class="col-12 p-0 bonuses rows">
                    <!-- Левая колонка с профилем и Telegram -->
                    <div class="profile-left-column col-4 p-0 mr-3">
                        <div class="content cards profile-card-left">
                            <div class="user-profile-avatar">
                                <div class="user-profile-avatar-wrapper relative-wrapper">
                                    <img 
                                        :src="$root.user.avatar"
                                    />
                                </div>
                                <div class="user-profile-name">{{ $root.user.username }}</div>
                                <p class="user-profile-reg">На сайте с {{ $moment($root.user.date).format('DD.MM.YYYY') }}</p>
                                <div class="user-profile-out">
                                    <button onclick="changeTheme();" class="blue" style="margin-bottom: 8px;">Сменить тему</button>
                                    <button class="ser" v-if="$root.user" @click="logout">Выйти</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Telegram блок - под профилем -->
                        <div class="content cards telegram-card">
                            <div class="telegram-box" v-if="!telegramLinked">
                                <div class="tg-header">
                                    <svg viewBox="0 0 24 24" fill="#0088cc"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                    <span class="tg-title">Telegram бот</span>
                                </div>
                                <ul class="tg-benefits">
                                    <li>• Проверка баланса</li>
                                    <li>• Уведомления о бонусах</li>
                                    <li>• Оповещения о выигрышах</li>
                                </ul>
                                <div class="tg-body">
                                    <div class="tg-code-box" v-if="telegramCode">
                                        <div class="tg-code">{{ telegramCode }}</div>
                                        <a :href="telegramBotLink" target="_blank" class="tg-link-btn">Привязать в боте</a>
                                    </div>
                                    <button class="tg-get-btn" @click="getTelegramCode" v-else>Получить код привязки</button>
                                </div>
                            </div>
                            <div class="telegram-box tg-linked" v-else>
                                <div class="tg-header tg-success">
                                    <svg viewBox="0 0 24 24" fill="#10b981"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                    <span class="tg-title">✅ Telegram привязан</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content cards col-8">
                        <!-- Статистика -->
                        <div class="user-stats">
                            <div class="user-stats-grid">
                                <div class="user-stat-card">
                                    <span class="user-stat-label">Депозиты</span>
                                    <span class="user-stat-value deposits">{{ formatMoney(financial.totalDeposits) }} ₽</span>
                                </div>
                                <div class="user-stat-card">
                                    <span class="user-stat-label">Выводы</span>
                                    <span class="user-stat-value withdraws">{{ formatMoney(financial.totalWithdraws) }} ₽</span>
                                </div>
                                <div class="user-stat-card">
                                    <span class="user-stat-label">Ставок</span>
                                    <span class="user-stat-value games">{{ financial.gamesPlayed.toLocaleString() }}</span>
                                </div>
                                <div class="user-stat-card">
                                    <span class="user-stat-label">Профит</span>
                                    <span class="user-stat-value" :class="financial.profit >= 0 ? 'profit-plus' : 'profit-minus'">{{ financial.profit >= 0 ? '+' : '' }}{{ formatMoney(financial.profit) }} ₽</span>
                                </div>
                                <div class="user-stat-card">
                                    <span class="user-stat-label">Вейджер</span>
                                    <span class="user-stat-value" :class="financial.wager <= 0 ? 'wager-done' : 'wager-active'">{{ formatMoney(financial.wager) }} ₽</span>
                                </div>
                            </div>
                        </div>

                        <div class="profile-ranks">
                            <div class="next-rank user-profile-overview-ranks">
                                <img :src="$root.user.rank.icon" alt="" class="rank-icon">
                                <div class="progress-next-rank">
                                    <div class="bets-progress">Ставок: 
                                        <span class="progress-sum">
                                            <span class="beatify-numbers">{{ $root.user.stats.bets }}</span> 
                                            / 
                                            <span class="beatify-numbers">{{ $root.user.nextRank.bets }}</span>
                                        </span>
                                        <div 
                                            class="fill" 
                                            :style="'width:' + betsFill + '%'"
                                        />
                                    </div>
                                    <div class="deposit-progress">Депозитов: 
                                        <span class="progress-sum">
                                            <span class="beatify-numbers">{{ $root.user.stats.deposits }}</span> 
                                            / 
                                            <span class="beatify-numbers">{{ $root.user.nextRank.deposit }}</span>
                                        </span>
                                        <div 
                                            class="fill" 
                                            :style="'width:' + depositFill + '%'"
                                        />
                                    </div>
                                </div> 
                                <img :src="$root.user.nextRank.icon" alt="" class="rank-icon next-icon">
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <br>
                            <div style="font-size: 14px; font-weight: 500;">Каждую неделю Вы можете получить часть проигранных средств в виде кэшбека.</div>
                            <div style="font-size: 14px; font-weight: 500;">Учитываются только ставки в Slots и Live.</div>
                            <div style="font-size: 11px; padding-top: 5px; padding-bottom: 5px;">Обратите внимание, кэшбек доступен каждый понедельник, после этого он сгорает до следующей недели
                                <br>
                            </div> 

                            Ваш процент CashBack - {{ $root.user.rank.cashback }} %
                            <br> Сумма CashBack - {{ parseFloat(cashback).toFixed(2) }} ₽
                            <br>
                            <button
                                :disabled="'понедельник' != eventBuy"
                                class="blue cashback" 
                                style="margin-top: 5px; margin-bottom: 15px;"
                                @click="cashbackClaim"
                            >
                                Получить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <Ellipsis v-if="isLoading || $root.user == null" />
            <div class="container pr-0" v-else>
                <div class="col-12 p-0 bonuses">
                    <div class="content cards col-14 p-0 mr-0">
                        <header class="rang-table">
                            <h6 class="rang-table">Таблица рангов</h6>
                        </header>
                        <div class="table-holder">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="rang-table">
                                        <tr class="rang-table">
                                            <th class="rang-table" style="text-align: center;">Ранг</th>
                                            <th class="rang-table">Ставок</th>
                                            <th class="rang-table">Депозитов</th>
                                            <th class="rang-table">Ежедневный Бонус</th>
                                            <th class="rang-table">Комиссия вывода</th>
                                            <th class="rang-table">Награда</th>
                                            <th class="rang-table">Кэшбэк</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr 
                                            :class="$root.user.rank.id != item.id ? 'disabled' : ''" 
                                            v-for="item in items" 
                                        >
                                            <th scope="row" class="rank">
                                                <span class="rang-table">
                                                    <img :src="item.icon" class="rank_img">&nbsp;{{ item.name }}
                                                </span>
                                            </th>
                                            <td>{{ item.bets.toLocaleString('de-DE') }}</td>
                                            <td>{{ item.deposit.toLocaleString('de-DE') }}</td>
                                            <td>{{ item.min_bonus }} - {{ item.max_bonus }}</td>
                                            <td>{{ item.comission }}%</td>
                                            <td>{{ item.reward.toLocaleString('de-DE') }}</td>
                                            <td>{{ item.cashback }}%</td>
                                        </tr>
                                    </tbody>
                                </table>
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

export default {
    components: {
        Ellipsis
    },
    data() {
        return {
            isLoading: true,
            items: [],
            bets: [],
            selected: false,
            cashback: 0,
            telegramLinked: false,
            telegramCode: null,
            telegramBotLink: null,
            financial: {
                totalDeposits: 0,
                totalWithdraws: 0,
                profit: 0,
                gamesPlayed: 0,
                wager: 0,
                wagerStatus: 1
            }
        }
    },
    methods: {
        init() {
            this.$root.axios.post('/profile/init')
            .then(response => {
                const {data} = response

                this.items = data.list
                this.cashback = data.cashback
                
                // Загружаем финансовую статистику
                if(data.financial) {
                    this.financial = data.financial
                }

                this.$root.user.rank = data.rank.now
                this.$root.user.nextRank = data.rank.next || data.rank.now
                this.$root.user.stats = data.stats

                this.isLoading = false
                
                // Проверяем привязку Telegram
                this.checkTelegramLink()
            })
        },
        checkTelegramLink() {
            if(this.$root.user && this.$root.user.tg_id && this.$root.user.tg_id != '0') {
                this.telegramLinked = true
            }
        },
        getTelegramCode() {
            this.$root.axios.post('/profile/telegram/link')
            .then(response => {
                const {data} = response
                if(data.linked) {
                    this.telegramLinked = true
                } else {
                    this.telegramCode = data.code
                    this.telegramBotLink = data.bot_link
                }
            })
        },
        unlinkTelegram() {
            this.$root.axios.post('/profile/telegram/unlink')
            .then(response => {
                const {data} = response
                if(data.success) {
                    this.telegramLinked = false
                    this.telegramCode = null
                    this.$root.$emit('noty', {
                        title: 'Успешно',
                        text: 'Telegram отвязан',
                        type: 'success'
                    })
                }
            })
        },
        logout() {
            location.href = `/user/logout`
        },
        formatMoney(value) {
            return parseFloat(value).toLocaleString('ru-RU', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })
        },
        cashbackClaim() {
            this.$root.axios.post('/profile/cashback/claim')
            .then(response => {
                const {data} = response

                if(data.error) {
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        text: data.message,
                        type: 'error'
                    })
                }

                this.$root.user.balance = data.balance
                this.cashback = 0
                
                this.$root.$emit('noty', {
                    title: 'Успешно',
                    text: 'Вы забрали кешбек',
                    type: 'success'
                })
            })
        }
    },
    computed: {
        betsFill() {
            const result = (this.$root.user.stats.bets / this.$root.user.nextRank.bets) * 100

            return result > 100 
                ? 100 
                : parseFloat(result).toFixed(2)
        },
        eventBuy: function() {
            return this.$moment().format("dddd")
        },
        depositFill() {
            const result = (this.$root.user.stats.deposits / this.$root.user.nextRank.deposit) * 100

            return result > 100 
                ? 100 
                : parseFloat(result).toFixed(2)
        },
        profitClass() {
            if(this.financial.profit > 0) return 'profit-positive'
            if(this.financial.profit < 0) return 'profit-negative'
            return 'profit-neutral'
        },
        wagerClass() {
            // wagerStatus: 1 = отыгран, 0 = не отыгран
            if(this.financial.wager <= 0) return 'wager-done'
            return 'wager-active'
        }
    },
    mounted() {
        if(this.$root.user !== null) {
            return this.init()
        } 

        this.$watch('$root.user', (user) => {
            if(user !== null) {
                this.init()
            }
        })
    },
}
</script>

<style scoped>
    button.blue.cashback {
        background-image: linear-gradient(45deg, #2561d0, #1e4eff) !important;
        color: #fff !important;
        font-weight: 600 !important;
        border: none !important;
    }
    
    button.blue.cashback:hover:not(:disabled) {
        background-image: linear-gradient(45deg, #3d7ae8, #3860ff) !important;
        box-shadow: 0 4px 12px rgba(37, 97, 208, 0.4) !important;
    }
    
    button.blue.cashback:disabled {
        box-shadow: none;
        cursor: not-allowed;
        opacity: .5;
        background-image: linear-gradient(45deg, #a0a0a0, #888888) !important;
        color: #fff !important;
    }

    /* Левый блок профиля - фиксированная высота и центрирование */
    .profile-card-left {
        display: flex;
        align-items: center;
        justify-content: center;
        align-self: flex-start;
        height: auto !important;
        min-height: auto !important;
        max-height: fit-content !important;
    }

    /* Финансовая статистика */
    .financial-stats {
        padding: 15px;
        margin-bottom: 15px;
    }

    .stats-header {
        text-align: center;
        margin-bottom: 15px;
    }

    .stats-header h6 {
        font-size: 16px;
        font-weight: 600;
        color: #fff;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .stats-title-icon {
        width: 20px;
        height: 20px;
    }

    .stats-row {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
    }

    .stats-row:last-child {
        margin-bottom: 0;
    }

    .stats-row-center {
        justify-content: center;
    }

    .stats-row .stat-card {
        flex: 1;
    }

    .stats-row-center .stat-card {
        flex: 1;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        padding: 16px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.08);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .stat-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
    }

    .stat-icon svg {
        width: 20px;
        height: 20px;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .stat-label {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.6);
        font-weight: 500;
    }

    .stat-value {
        font-size: 14px;
        font-weight: 700;
        color: #fff;
    }

    /* Цвета для карточек */
    .stat-card.deposits {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
        border-color: rgba(59, 130, 246, 0.3);
    }

    .stat-card.deposits .stat-icon {
        background: rgba(59, 130, 246, 0.2);
    }

    .stat-card.deposits .stat-icon svg {
        stroke: #60a5fa;
    }

    .stat-card.deposits .stat-value {
        color: #60a5fa;
    }

    .stat-card.withdraws {
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.15), rgba(168, 85, 247, 0.05));
        border-color: rgba(168, 85, 247, 0.3);
    }

    .stat-card.withdraws .stat-icon {
        background: rgba(168, 85, 247, 0.2);
    }

    .stat-card.withdraws .stat-icon svg {
        stroke: #a855f7;
    }

    .stat-card.withdraws .stat-value {
        color: #a855f7;
    }

    .stat-card.games {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
        border-color: rgba(245, 158, 11, 0.3);
    }

    .stat-card.games .stat-icon {
        background: rgba(245, 158, 11, 0.2);
    }

    .stat-card.games .stat-icon svg {
        stroke: #f59e0b;
    }

    .stat-card.games .stat-value {
        color: #f59e0b;
    }

    /* Цвета для профита */
    .stat-card.profit.profit-positive {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
        border-color: rgba(16, 185, 129, 0.3);
    }

    .stat-card.profit.profit-positive .stat-icon {
        background: rgba(16, 185, 129, 0.2);
    }

    .stat-card.profit.profit-positive .stat-icon svg {
        stroke: #10b981;
    }

    .stat-card.profit.profit-positive .profit-value {
        color: #10b981;
    }

    .stat-card.profit.profit-negative {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.05));
        border-color: rgba(239, 68, 68, 0.3);
    }

    .stat-card.profit.profit-negative .stat-icon {
        background: rgba(239, 68, 68, 0.2);
    }

    .stat-card.profit.profit-negative .stat-icon svg {
        stroke: #ef4444;
    }

    .stat-card.profit.profit-negative .profit-value {
        color: #ef4444;
    }

    .stat-card.profit.profit-neutral {
        background: linear-gradient(135deg, rgba(148, 163, 184, 0.15), rgba(148, 163, 184, 0.05));
        border-color: rgba(148, 163, 184, 0.3);
    }

    .stat-card.profit.profit-neutral .stat-icon {
        background: rgba(148, 163, 184, 0.2);
    }

    .stat-card.profit.profit-neutral .stat-icon svg {
        stroke: #94a3b8;
    }

    .stat-card.profit.profit-neutral .profit-value {
        color: #94a3b8;
    }

    /* Wager card styles */
    .stat-card.wager {
        background: linear-gradient(135deg, rgba(236, 72, 153, 0.15), rgba(236, 72, 153, 0.05));
        border-color: rgba(236, 72, 153, 0.3);
    }

    .stat-card.wager .stat-icon {
        background: rgba(236, 72, 153, 0.2);
    }

    .stat-card.wager .stat-icon svg {
        stroke: #ec4899;
    }

    .stat-card.wager .stat-value {
        color: #ec4899;
    }

    .stat-card.wager.wager-done {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
        border-color: rgba(16, 185, 129, 0.3);
    }

    .stat-card.wager.wager-done .stat-icon {
        background: rgba(16, 185, 129, 0.2);
    }

    .stat-card.wager.wager-done .stat-icon svg {
        stroke: #10b981;
    }

    .stat-card.wager.wager-done .stat-value {
        color: #10b981;
    }

    @media (max-width: 992px) {
        .stats-row {
            flex-wrap: wrap;
        }
        
        .stats-row .stat-card {
            flex: 1 1 calc(50% - 6px);
            max-width: none;
        }
        
        .stats-row-center .stat-card {
            flex: 1 1 calc(50% - 6px);
        }
    }

    @media (max-width: 576px) {
        .stats-row .stat-card,
        .stats-row-center .stat-card {
            flex: 1 1 100%;
        }
        
        .stat-card {
            padding: 14px;
        }
    }

    tr.disabled {
        opacity: .4;
    }

    .rank_img {
        width: 42px;
    }

    .rang-table {
        margin: 0;
        padding: 5px;
        text-align: center;
    }

    .table-holder,.table-responsive {
        position: relative;
    }

    .table-responsive::-webkit-scrollbar {
        height: 10px;
    }

    .table-responsive::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 6px #333;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #333;
        outline: 1px solid #333;
    }

    .show {
        opacity: 1;
        visibility: visible;
    }

    tr td:first-child,tr th:first-child {
        background: #fff;
        left: 0;
        min-width: 40px;
        position: sticky;
        text-align: left;
    }

    .user-profile-avatar {
        margin: 0 auto;
        padding: 15px 0;
        width: 200px;
    }

    .user-profile-avatar-wrapper {
        align-items: center;
        display: flex;
        justify-content: center;
        margin-bottom: 4px;
    }

    .user-profile-avatar img {
        border-radius: 50%;
        padding: 5px;
        width: 100px;
    }

    .user-profile-name {
        font-size: 18px;
        margin-top: 5px;
        overflow: hidden;
        text-align: center;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .user-profile-reg {
        color: #838383;
        font-size: 12px;
        margin-top: 3px;
        text-align: center;
        white-space: nowrap;
    }

    .user-profile-out {
        text-align: center;
        margin-top: 15px;
    }

    .table {
        color: ""!important;
    }

    .progress-next-rank {
        width: 100%;
    }

    .next-rank {
        align-items: center;
        display: flex;
        margin: auto;
        max-width: 725px;
    }

    .bets-progress,.deposit-progress {
        color: #fbfbfb;
        font-size: 12px;
        font-weight: 400;
        font-weight: 500;
        padding: 5px;
        position: relative;
        text-align: center;
        width: 100%;
        z-index: 5;
    }

    .bets-progress span.progress-sum,.deposit-progress span.progress-sum {
        color: #fbfbfb;
        font-weight: 700;
    }

    .next-rank img.rank-icon {
        width: 60px;
    }

    img.rank-icon.next-icon {
        margin-left: 10px;
    }

    .bets-progress {
        background: rgb(125 92 245/60%);
    }

    .deposit-progress {
        background: rgb(56 126 239/60%);
        margin-top: 10px;
    }

    .bets-progress .fill,.deposit-progress .fill {
        height: 100%;
        left: 0;
        position: absolute;
        top: 0;
        z-index: -1;
    }

    .bets-progress .fill {
        background: #7d5cf5;
        background-image: linear-gradient(90deg,#7d5cf5,#7856f7)!important;
    }

    .deposit-progress .fill {
        background: #38ef7d;
        background-image: linear-gradient(90deg,#4d84ea,#2e59f4);
    }

    .profile-ranks {
        padding-top: 1em;
    }

    /* Telegram Section */
    .telegram-section {
        background: linear-gradient(135deg, rgba(0, 136, 204, 0.15), rgba(0, 136, 204, 0.05));
        border: 1px solid rgba(0, 136, 204, 0.3);
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        text-align: center;
    }

    .telegram-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 15px;
        font-size: 16px;
        font-weight: 600;
        color: #0088cc;
    }

    .telegram-icon {
        width: 24px;
        height: 24px;
    }

    .telegram-content p {
        margin: 0 0 10px;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.8);
    }

    .telegram-content ul {
        list-style: none;
        padding: 0;
        margin: 0 0 15px;
        text-align: left;
        display: inline-block;
    }

    .telegram-content ul li {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 5px;
    }

    .telegram-link-box {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 15px;
    }

    .telegram-code {
        font-family: monospace;
        font-size: 24px;
        font-weight: bold;
        color: #0088cc;
        letter-spacing: 3px;
        margin-bottom: 12px;
    }

    .telegram-btn {
        display: inline-block;
        background: linear-gradient(135deg, #0088cc, #0077b5);
        color: #fff;
        padding: 10px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }

    .telegram-btn:hover {
        background: linear-gradient(135deg, #0099dd, #0088cc);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 136, 204, 0.4);
        color: #fff;
        text-decoration: none;
    }

    .telegram-get-code {
        background: linear-gradient(135deg, #0088cc, #0077b5);
        color: #fff;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .telegram-get-code:hover {
        background: linear-gradient(135deg, #0099dd, #0088cc);
        transform: translateY(-2px);
    }

    .telegram-linked {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .telegram-status {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #10b981;
    }

    .status-icon {
        font-size: 18px;
    }

    .telegram-unlink {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .telegram-unlink:hover {
        background: rgba(239, 68, 68, 0.3);
    }
</style>