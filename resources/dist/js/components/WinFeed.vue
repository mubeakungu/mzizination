<template>
    <div class="win-feed-container" v-if="wins.length > 0">
        <div class="win-feed-header">
            <svg class="win-feed-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 5C4 4.44772 4.44772 4 5 4H19C19.5523 4 20 4.44772 20 5V19C20 19.5523 19.5523 20 19 20H5C4.44772 20 4 19.5523 4 19V5Z" stroke="currentColor" stroke-width="2"/>
                <path d="M8 12H8.01M12 12H12.01M16 12H16.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                <path d="M4 8H20M4 16H20" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span class="win-feed-title">Последние выигрыши</span>
            <span class="win-feed-live">
                <svg class="live-dot" viewBox="0 0 8 8" fill="none">
                    <circle cx="4" cy="4" r="4" fill="currentColor"/>
                </svg>
                LIVE
            </span>
        </div>
        <div class="win-feed-track">
            <div class="win-feed-scroll" :style="{ animationDuration: scrollDuration + 's' }">
                <div class="win-feed-item" v-for="(win, index) in displayWins" :key="index">
                    <div class="win-feed-game">
                        <img :src="win.icon" class="win-feed-game-icon" @error="handleImageError($event, win)" />
                    </div>
                    <div class="win-feed-info">
                        <span class="win-feed-user">{{ win.username }}</span>
                        <span class="win-feed-amount" :class="{ 'big-win': win.amount >= 5000 }">
                            +{{ formatAmount(win.amount) }} ₽
                        </span>
                    </div>
                    <div class="win-feed-game-name">{{ win.game }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'WinFeed',
    data() {
        return {
            wins: [],
            scrollDuration: 30,
            updateInterval: null,
            slots: [
                { name: 'Sweet Bonanza', icon: '/img/slots/SweetBonanza.jpg', provider: 'Pragmatic' },
                { name: 'Gates of Olympus', icon: '/img/slots/GatesofOlympus.jpg', provider: 'Pragmatic' },
                { name: 'Big Bass Bonanza', icon: '/img/slots/BigBassBonanza.jpg', provider: 'Pragmatic' },
                { name: 'Dog House', icon: '/img/slots/TheDogHouse.jpg', provider: 'Pragmatic' },
                { name: 'Fruit Party', icon: '/img/slots/FruitParty.jpg', provider: 'Pragmatic' },
                { name: 'Starlight Princess', icon: '/img/slots/StarlightPrincess.jpg', provider: 'Pragmatic' },
                { name: 'Wild West Gold', icon: '/img/slots/WildWestGold.jpg', provider: 'Pragmatic' },
                { name: 'Buffalo King', icon: '/img/slots/BuffaloKing.jpg', provider: 'Pragmatic' },
                { name: 'Great Rhino', icon: '/img/slots/GreatRhino.jpg', provider: 'Pragmatic' },
                { name: 'Mustang Gold', icon: '/img/slots/MustangGold.jpg', provider: 'Pragmatic' },
                { name: 'Book of the Fallen', icon: '/img/slots/BookoftheFallen.jpg', provider: 'Pragmatic' },
                { name: 'Gems Bonanza', icon: '/img/slots/GemsBonanza.jpg', provider: 'Pragmatic' },
                { name: 'Book of Dead', icon: '/img/slots/BookOfDead.jpg', provider: 'Play n Go' }
            ],
            nameParts: ['Pro', 'King', 'Lucky', 'Win', 'Gold', 'Star', 'Max', 'Bet', 'Spin', 'Cash', 'Rich', 'Top', 'Mega', 'Super', 'Ultra', 'Best', 'Cool', 'Fast', 'Hot', 'Ice']
        }
    },
    computed: {
        displayWins() {
            return [...this.wins, ...this.wins];
        }
    },
    mounted() {
        this.generateInitialWins();
        this.startAutoUpdate();
    },
    beforeDestroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
    },
    methods: {
        generateInitialWins() {
            const count = 8 + Math.floor(Math.random() * 5);
            for (let i = 0; i < count; i++) {
                this.wins.push(this.generateWin());
            }
        },
        generateWin() {
            const slot = this.slots[Math.floor(Math.random() * this.slots.length)];
            const username = this.generateUsername();
            const amount = this.generateAmount();
            
            return {
                username: username,
                amount: amount,
                game: slot.name,
                icon: slot.icon,
                timestamp: Date.now()
            };
        },
        generateUsername() {
            const part = this.nameParts[Math.floor(Math.random() * this.nameParts.length)];
            return part + '***';
        },
        generateAmount() {
            const rand = Math.random();
            if (rand < 0.6) {
                return Math.floor(100 + Math.random() * 900);
            } else if (rand < 0.9) {
                return Math.floor(1000 + Math.random() * 4000);
            } else {
                return Math.floor(5000 + Math.random() * 20000);
            }
        },
        formatAmount(amount) {
            return amount.toLocaleString('ru-RU');
        },
        startAutoUpdate() {
            const addNewWin = () => {
                const newWin = this.generateWin();
                this.wins.unshift(newWin);
                
                if (this.wins.length > 15) {
                    this.wins.pop();
                }
                
                const nextUpdate = 30000 + Math.random() * 60000;
                this.updateInterval = setTimeout(addNewWin, nextUpdate);
            };
            
            const firstUpdate = 30000 + Math.random() * 60000;
            this.updateInterval = setTimeout(addNewWin, firstUpdate);
        },
        handleImageError(event, win) {
            event.target.src = '/img/slots/SweetBonanza.jpg';
        }
    }
}
</script>

<style scoped>
</style>
