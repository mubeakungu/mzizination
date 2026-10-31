<template>
    <div id="left-main" class="left-main cards" :class="{ 'collapsed': isCollapsed }">
        
        <!-- Desktop Floating Games Menu -->
        <div class="floating-games-menu">
            <div class="games-list">
                <router-link
                    tag="div"
                    class="game-item"
                    :class="[page == 'live' ? 'isActive' : '']"
                    :title="isCollapsed ? 'Live' : ''"
                    v-b-tooltip:left-main.hover.right="isCollapsed ? 'Live' : false"
                    to="/live"
                >
                    <div class="games-img live"></div>
                    <span class="game-text">Live</span>
                </router-link>
                <router-link
                    tag="div"
                    class="game-item"
                    :class="[page == 'sports' ? 'isActive' : '']"
                    :title="isCollapsed ? 'Sports' : ''"
                    v-b-tooltip:left-main.hover.right="isCollapsed ? 'Sports' : false"
                    to="/sports"
                >
                    <div class="games-img sports" style="background: none; display: flex; align-items: center; justify-content: center; font-size: 18px;">&#9917;</div>
                    <span class="game-text">Sports</span>
                </router-link>
                <router-link
                    tag="div"
                    class="game-item"
                    :class="[$route.path.indexOf('/licensed') === 0 ? 'isActive' : '']"
                    :title="isCollapsed ? 'Licensed Games' : ''"
                    v-b-tooltip:left-main.hover.right="isCollapsed ? 'Licensed Games' : false"
                    to="/licensed"
                >
                    <div class="games-img" style="background: none; display: flex; align-items: center; justify-content: center; font-size: 18px;">&#9733;</div>
                    <span class="game-text">Licensed Games</span>
                </router-link>
                <router-link
                    tag="div"
                    class="game-item"
                    :class="[page == 'dice' ? 'isActive' : '']"
                    :title="isCollapsed ? 'Dice' : ''"
                    v-b-tooltip:left-main.hover.right="isCollapsed ? 'Dice' : false"
                    to="/dice"
                >
                    <div class="games-img dice"></div>
                    <span class="game-text">Dice</span>
                </router-link>
                <router-link
                    tag="div"
                    class="game-item"
                    :class="[page == 'mines' ? 'isActive' : '']"
                    :title="isCollapsed ? 'Mines' : ''"
                    v-b-tooltip:left-main.hover.right="isCollapsed ? 'Mines' : false"
                    to="/mines"
                >
                    <div class="games-img mines"></div>
                    <span class="game-text">Mines</span>
                </router-link>
                <router-link
                    tag="div"
                    class="game-item"
                    :class="[page == 'bubbles' ? 'isActive' : '']"
                    :title="isCollapsed ? 'Bubbles' : ''"
                    v-b-tooltip:left-main.hover.right="isCollapsed ? 'Bubbles' : false"
                    to="/bubbles"
                >
                    <div class="games-img bubbles"></div>
                    <span class="game-text">Bubbles</span>
                </router-link>
                <router-link
                    tag="div"
                    class="game-item"
                    :class="[page == 'wheel' ? 'isActive' : '']"
                    :title="isCollapsed ? 'Wheel' : ''"
                    v-b-tooltip:left-main.hover.right="isCollapsed ? 'Wheel' : false"
                    to="/wheel"
                >
                    <div class="games-img wheel"></div>
                    <span class="game-text">Wheel</span>
                </router-link>
                <router-link
                    tag="div"
                    class="game-item"
                    :class="[page == 'plinko' ? 'isActive' : '']"
                    :title="isCollapsed ? 'Plinko' : ''"
                    v-b-tooltip:left-main.hover.right="isCollapsed ? 'Plinko' : false"
                    to="/plinko"
                >
                    <div class="games-img plinko"></div>
                    <span class="game-text">Plinko</span>
                </router-link>
                <router-link
                    tag="div"
                    class="game-item"
                    :title="isCollapsed ? 'BlackJack' : ''"
                    v-b-tooltip:left-main.hover.right="isCollapsed ? 'BlackJack' : false"
                    to="/live/game/463"
                >
                    <div class="games-img blackjack"></div>
                    <span class="game-text">BlackJack</span>
                </router-link>
            </div>
            
            <!-- Responsible gaming -->
            <router-link to="/responsible-gaming" class="rg-link" :title="'Responsible Gaming (18+)'">
                <span class="rg-18">18+</span><span v-if="!isCollapsed"> Responsible Gaming</span>
            </router-link>

            <!-- Online Counter -->
            <div class="floating-online">
                <div class="circle-online"></div>
                <span class="online-count" v-if="!isCollapsed">{{ $root.online }}</span>
            </div>
            
            <!-- Social Links -->
            <div class="floating-social" v-if="!isCollapsed">
                <a :href="$root.config.tg_channel" target="_blank" class="social-btn">
                    <img src="/assets/image/tg.svg" width="20px" />
                </a>
                <a :href="$root.config.vk_url" target="_blank" class="social-btn">
                    <img src="/assets/image/vk.svg" width="20px" />
                </a>
            </div>
            <div class="floating-social-collapsed" v-else>
                <a :href="$root.config.tg_channel" target="_blank" class="social-btn-small" title="Telegram">
                    <img src="/assets/image/tg.svg" width="18px" />
                </a>
                <a :href="$root.config.vk_url" target="_blank" class="social-btn-small" title="VK">
                    <img src="/assets/image/vk.svg" width="18px" />
                </a>
            </div>
            
            <!-- Toggle Button -->
            <div class="menu-toggle-btn" @click="toggleMenu">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6" v-if="!isCollapsed"></path>
                    <path d="M9 18l6-6-6-6" v-else></path>
                </svg>
            </div>
        </div>

        <!-- Mobile Bottom Navigation - НЕ ТРОГАЕМ -->
        <div class="mob-nav-bottom">
            <router-link to="/" class="mob-nav-item" :class="{'isActive': page == 'home'}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span class="mob-nav-label">Главная</span>
            </router-link>
            <div class="mob-nav-item" @click="$bvModal.show('search')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <span class="mob-nav-label">Поиск</span>
            </div>
            <div class="mob-nav-item mob-nav-cashier" @click="$root.$emit('toggleCashier')" v-if="$root.user">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <span class="mob-nav-label">Касса</span>
            </div>
            <router-link to="/bonus" class="mob-nav-item" :class="{'isActive': page == 'bonus'}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 12v10H4V12"></path>
                    <path d="M2 7h20v5H2z"></path>
                    <path d="M12 22V7"></path>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                </svg>
                <span class="mob-nav-label">Бонусы</span>
            </router-link>
            <div class="mob-nav-item" @click="$root.$emit('toggleMenu')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
                <span class="mob-nav-label">Меню</span>
            </div>
        </div>

        <SearchSlots />
    </div>
</template>

<script>
import SearchSlots from "./modals/SearchSlots";

export default {
    props: ['page'],
    data() {
        return {
            slotSearchShow: false,
            isCollapsed: false
        }
    },
    methods: {
        errorGame() {
            this.$root.$emit('noty', {
                title: 'Ошибка!',
                text: 'Игра временно недоступна.',
                type: 'error'
            })
        },
        toggleMenu() {
            this.isCollapsed = !this.isCollapsed
            this.saveMenuState()
            this.$root.$emit('menuToggle', this.isCollapsed)
        },
        loadMenuState() {
            const saved = localStorage.getItem('leftMenuCollapsed')
            if (saved !== null) {
                this.isCollapsed = saved === 'true'
                this.$root.$emit('menuToggle', this.isCollapsed)
            }
        },
        saveMenuState() {
            localStorage.setItem('leftMenuCollapsed', this.isCollapsed.toString())
        }
    },
    components: {
        SearchSlots
    },
    mounted() {
        this.loadMenuState()
        this.$root.$on('searchModalState', data => {
            this.slotSearchShow = data
        })
    }
}
</script>

<style scoped>
/* Hide desktop menu on mobile */
@media screen and (max-width: 992px) {
    .floating-games-menu {
        display: none !important;
    }
}

/* Hide mobile nav on desktop */
@media screen and (min-width: 993px) {
    .mob-nav-bottom {
        display: none !important;
    }
}
.rg-link { display: flex; align-items: center; justify-content: center; gap: 6px; margin: 8px 0; font-size: 12px; opacity: .75; color: inherit; text-decoration: none; }
.rg-link:hover { opacity: 1; }
.rg-18 { border: 1.5px solid #eb5757; color: #eb5757; border-radius: 50%; font-size: 10px; font-weight: 700; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; }
</style>
