<template>
    <div class="social-bar" :class="{ 'social-bar-open': isOpen }">
        <div class="social-toggle" @click="toggle" v-if="!isOpen">
            <div class="toggle-icon">
                <i class="fa fa-comments"></i>
            </div>
        </div>

        <div class="social-content" v-if="isOpen">
            <div class="social-header">
                <div class="social-tabs">
                    <div 
                        class="social-tab" 
                        :class="{ active: activeTab === 'chat' }"
                        @click="activeTab = 'chat'"
                    >
                        Чат
                    </div>
                    <div 
                        class="social-tab" 
                        :class="{ active: activeTab === 'bets' }"
                        @click="activeTab = 'bets'"
                    >
                        Ставки
                    </div>
                </div>
                <div class="social-close" @click="toggle">
                    <i class="fa fa-times"></i>
                </div>
            </div>

            <!-- CHAT TAB -->
            <div class="social-body chat-body" v-if="activeTab === 'chat'" ref="chatBody">
                <div class="chat-messages">
                    <div 
                        class="chat-message" 
                        v-for="msg in messages" 
                        :key="msg.id"
                    >
                        <div class="msg-avatar">
                            <img :src="msg.user.avatar" :alt="msg.user.username">
                        </div>
                        <div class="msg-content">
                            <div class="msg-meta">
                                <span class="msg-user">{{ msg.user.username }}</span>
                                <span class="msg-time">{{ $moment(msg.created_at).format('HH:mm') }}</span>
                            </div>
                            <div class="msg-text">{{ msg.message }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="social-footer" v-if="activeTab === 'chat'">
                <div class="chat-input-area" v-if="$root.user">
                    <input 
                        type="text" 
                        v-model="newMessage" 
                        @keyup.enter="sendMessage"
                        placeholder="Введите сообщение..."
                        :disabled="isSending"
                    >
                    <button @click="sendMessage" :disabled="isSending || !newMessage.trim()">
                        <i class="fa fa-paper-plane"></i>
                    </button>
                </div>
                <div class="chat-login-placeholder" v-else @click="auth">
                    Войдите, чтобы общаться
                </div>
            </div>

            <!-- LIVE BETS TAB -->
            <div class="social-body bets-body" v-if="activeTab === 'bets'">
                <div class="bets-list">
                    <div 
                        class="bet-card" 
                        v-for="bet in bets" 
                        :key="bet.id"
                        :class="{ 'is-win': bet.win > bet.bet, 'is-high-roll': (bet.win / bet.bet) > 10 }"
                    >
                        <div class="bet-user">
                            <img :src="bet.avatar" alt="">
                            <span>{{ bet.username }}</span>
                        </div>
                        <div class="bet-info">
                            <div class="bet-game">
                                <i :class="getGameIcon(bet.game)"></i> {{ getGameName(bet.game) }}
                            </div>
                            <div class="bet-amount">
                                <span>{{ bet.bet }} ₽</span>
                                <i class="fa fa-arrow-right"></i>
                                <span :class="bet.win > bet.bet ? 'text-success' : 'text-muted'">
                                    {{ bet.win > 0 ? bet.win : 0 }} ₽
                                </span>
                            </div>
                        </div>
                        <div class="bet-coef" v-if="bet.win > 0">
                            x{{ (bet.win / bet.bet).toFixed(2) }}
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
            isOpen: true, // Default open on desktop? Maybe make it responsive
            activeTab: 'chat',
            messages: [],
            bets: [],
            newMessage: '',
            isSending: false,
            pollInterval: null
        }
    },
    methods: {
        toggle() {
            this.isOpen = !this.isOpen;
            if(this.isOpen) {
                this.scrollToBottom();
            }
        },
        auth() {
            location.href = `/auth/vkontakte`;
        },
        fetchHistory() {
            this.$root.axios.post('/chat/history').then(res => {
                const newMsgs = res.data;
                // Check if we need to scroll (only if at bottom or first load)
                const shouldScroll = this.isAtBottom();
                this.messages = newMsgs;
                
                if(shouldScroll) {
                    this.$nextTick(() => this.scrollToBottom());
                }
            });
        },
        fetchBets() {
            this.$root.axios.post('/chat/bets').then(res => {
                this.bets = res.data;
            });
        },
        sendMessage() {
            if(!this.newMessage.trim()) return;
            
            this.isSending = true;
            this.$root.axios.post('/chat/send', {
                message: this.newMessage
            }).then(res => {
                this.isSending = false;
                if(res.data.error) {
                    return this.$root.$emit('noty', { title: 'Ошибка', text: res.data.message, type: 'error' });
                }
                this.newMessage = '';
                this.fetchHistory(); // Refresh immediately
            }).catch(() => {
                this.isSending = false;
            });
        },
        scrollToBottom() {
            const el = this.$refs.chatBody;
            if(el) el.scrollTop = el.scrollHeight;
        },
        isAtBottom() {
            const el = this.$refs.chatBody;
            if(!el) return true;
            return el.scrollHeight - el.scrollTop === el.clientHeight;
        },
        getGameName(game) {
            const names = {
                'dice': 'Dice',
                'mines': 'Mines',
                'bubbles': 'Bubbles',
                'wheel': 'Wheel',
                'plinko': 'Plinko',
                'blackjack': 'Blackjack'
            };
            return names[game] || game;
        },
        getGameIcon(game) {
            const icons = {
                'dice': 'fa fa-dice',
                'mines': 'fa fa-bomb',
                'bubbles': 'fa fa-circle',
                'wheel': 'fa fa-dharmachakra',
                'plinko': 'fa fa-hockey-puck', // approx
                'blackjack': 'fa fa-playing-card'
            };
            return icons[game] || 'fa fa-gamepad';
        }
    },
    mounted() {
        this.fetchHistory();
        this.fetchBets();
        
        // Simple polling every 3 seconds
        this.pollInterval = setInterval(() => {
            if(this.activeTab === 'chat' && this.isOpen) this.fetchHistory();
            if(this.activeTab === 'bets' && this.isOpen) this.fetchBets();
        }, 3000);

        // Auto-close on mobile initially
        if(window.innerWidth < 992) {
            this.isOpen = false;
        }
    },
    destroyed() {
        clearInterval(this.pollInterval);
    }
}
</script>

<style scoped>
/* Social Bar Styles - Scoped mostly, but using app vars */
.social-bar {
    position: fixed;
    right: 0;
    top: 70px; /* Below header */
    bottom: 0;
    width: 300px;
    background: #1e293b; /* Dark theme default */
    border-left: 1px solid rgba(255,255,255,0.05);
    z-index: 90;
    transition: transform 0.3s ease;
    transform: translateX(100%);
    display: flex;
    flex-direction: column;
}

.social-bar.social-bar-open {
    transform: translateX(0);
}

.social-toggle {
    position: absolute;
    left: -50px;
    top: 20px;
    width: 50px;
    height: 50px;
    background: #1e293b;
    border-radius: 10px 0 0 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: -5px 0 15px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.05);
    border-right: none;
}

.toggle-icon {
    font-size: 20px;
    color: #cbd5e1;
}

/* Light Theme overrides */
.theme--light .social-bar {
    background: #fff;
    border-left: 1px solid #e2e8f0;
}
.theme--light .social-toggle {
    background: #fff;
    border-color: #e2e8f0;
}
.theme--light .toggle-icon { color: #475569; }

/* Header */
.social-header {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 15px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    background: #1a1f2e;
}
.theme--light .social-header {
    background: #f8fafc;
    border-color: #e2e8f0;
}

.social-tabs {
    display: flex;
    background: rgba(255,255,255,0.05);
    border-radius: 8px;
    padding: 3px;
    flex: 1;
    margin-right: 15px;
}
.theme--light .social-tabs { background: #e2e8f0; }

.social-tab {
    flex: 1;
    text-align: center;
    padding: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #94a3b8;
    cursor: pointer;
    border-radius: 6px;
    transition: 0.2s;
}

.social-tab.active {
    background: #3b82f6;
    color: white;
}

.social-close {
    cursor: pointer;
    color: #94a3b8;
}
.social-close:hover { color: #ef4444; }

/* Body */
.social-body {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
}

/* Chat Messages */
.chat-messages {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.chat-message {
    display: flex;
    gap: 10px;
}

.msg-avatar img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.msg-content {
    background: rgba(255,255,255,0.05);
    padding: 8px 12px;
    border-radius: 0 12px 12px 12px;
    flex: 1;
}
.theme--light .msg-content { background: #f1f5f9; }

.msg-meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    margin-bottom: 4px;
}

.msg-user { font-weight: 700; color: #cbd5e1; }
.theme--light .msg-user { color: #334155; }

.msg-time { color: #64748b; }

.msg-text {
    font-size: 13px;
    color: #f1f5f9;
    word-break: break-word;
}
.theme--light .msg-text { color: #1e293b; }

/* Footer / Input */
.social-footer {
    padding: 15px;
    border-top: 1px solid rgba(255,255,255,0.05);
    background: #1a1f2e;
}
.theme--light .social-footer { background: #fff; border-color: #e2e8f0; }

.chat-input-area {
    display: flex;
    gap: 10px;
}

.chat-input-area input {
    flex: 1;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 10px;
    color: white;
    font-size: 13px;
    outline: none;
}
.theme--light .chat-input-area input {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #1e293b;
}

.chat-input-area button {
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    width: 40px;
    cursor: pointer;
}

.chat-login-placeholder {
    text-align: center;
    background: #3b82f6;
    color: white;
    padding: 10px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 13px;
}

/* Bets Feed */
.bets-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bet-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 10px;
    padding: 10px;
    position: relative;
    overflow: hidden;
}
.theme--light .bet-card {
    background: #fff;
    border-color: #e2e8f0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.bet-card.is-win {
    border-color: rgba(16, 185, 129, 0.3);
    background: linear-gradient(90deg, rgba(16, 185, 129, 0.05) 0%, transparent 100%);
}

.bet-card.is-high-roll {
    box-shadow: 0 0 15px rgba(245, 158, 11, 0.2);
    border-color: rgba(245, 158, 11, 0.5);
}

.bet-user {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #cbd5e1;
}
.theme--light .bet-user { color: #475569; }

.bet-user img {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.bet-info {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
}

.bet-game i { margin-right: 5px; color: #64748b; }

.bet-amount {
    font-weight: 600;
    color: #fff;
}
.theme--light .bet-amount { color: #1e293b; }

.bet-coef {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 10px;
    background: rgba(255,255,255,0.1);
    padding: 2px 6px;
    border-radius: 4px;
    color: #cbd5e1;
}
.is-win .bet-coef {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

/* Mobile Responsive */
@media (max-width: 992px) {
    .social-bar {
        width: 100%;
        top: auto;
        bottom: 56px; /* Above bottom nav */
        height: 50%; /* Half screen */
        transform: translateY(100%);
        border-left: none;
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    
    .social-bar.social-bar-open {
        transform: translateY(0);
    }
    
    .social-toggle {
        display: none; /* Hide toggle on mobile, use menu instead if needed */
    }
}
</style>
