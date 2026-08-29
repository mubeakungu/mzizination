<template>
    <div id="left-main" class="left-main cards" :class="{ 'collapsed': isCollapsed }">
        <div class="logoBox" @click="$root.$emit('open', 'home')">
            <div class="logo_img"></div>
        </div>
        <div class="games-menu mt-1 sticky-games-menu">
            <router-link
                tag="div"
                class="games-item menu-live"
                :class="[page == 'live' ? 'isActive blue' : '']"
                :title="isCollapsed ? 'Live' : ''"
                v-b-tooltip:left-main.hover.right="isCollapsed ? 'Live' : false"
                to="/live"
            >
                <div class="games-img live"></div>
                <span class="games-item-text">Live</span>
            </router-link>
            <router-link
                tag="div"
                class="games-item menu-dice" 
                :class="[page == 'dice' ? 'isActive blue' : '']"
                :title="isCollapsed ? 'Dice' : ''"
                v-b-tooltip:left-main.hover.right="isCollapsed ? 'Dice' : false"
                to="/dice"
            >
                <div class="games-img dice"></div>
                <span class="games-item-text">Dice</span>
            </router-link>
            <router-link
                tag="div"
                class="games-item menu-mines" 
                :class="[page == 'mines' ? 'isActive blue' : '']"
                :title="isCollapsed ? 'Mines' : ''"
                v-b-tooltip:left-main.hover.right="isCollapsed ? 'Mines' : false"
                to="/mines"
            >
                <div class="games-img mines"></div>
                <span class="games-item-text">Mines</span>
            </router-link>
            <router-link
                tag="div" 
                class="games-item menu-bubbles" 
                :class="[page == 'bubbles' ? 'isActive blue' : '']"
                :title="isCollapsed ? 'Bubbles' : ''"
                v-b-tooltip:left-main.hover.right="isCollapsed ? 'Bubbles' : false"
                to="/bubbles"
            >
                <div class="games-img bubbles"></div>
                <span class="games-item-text">Bubbles</span>
            </router-link>
            <router-link
                tag="div" 
                class="games-item menu-wheel" 
                :class="[page == 'wheel' ? 'isActive blue' : '']"
                :title="isCollapsed ? 'Wheel' : ''"
                v-b-tooltip:left-main.hover.right="isCollapsed ? 'Wheel' : false"
                to="/wheel"
            >
                <div class="games-img wheel"></div>
                <span class="games-item-text">Wheel</span>
            </router-link>
            <router-link 
                tag="div"
                class="games-item menu-plinko" 
                :class="[page == 'plinko' ? 'isActive blue' : '']"
                :title="isCollapsed ? 'Plinko' : ''"
                v-b-tooltip:left-main.hover.right="isCollapsed ? 'Plinko' : false"
                to="/plinko"
            >
                <div class="games-img plinko"></div>
                <span class="games-item-text">Plinko</span>
            </router-link>
            <router-link 
                tag="div"
                class="games-item menu-blackjack"
                :title="isCollapsed ? 'BlackJack' : ''"
                v-b-tooltip:left-main.hover.right="isCollapsed ? 'BlackJack' : false"
                to="/live/game/463"
            >
                <div class="games-img blackjack"></div>
                <span class="games-item-text">BlackJack</span>
            </router-link>
        </div>
        <!-- New Mobile Bottom Navigation -->
        <div class="mob-nav-bottom">
            <!-- Главная -->
            <router-link
                to="/"
                class="mob-nav-item"
                :class="{'isActive': page == 'home'}"
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span class="mob-nav-label">Главная</span>
            </router-link>

            <!-- Поиск -->
            <div 
                class="mob-nav-item"
                @click="$bvModal.show('search')"
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <span class="mob-nav-label">Поиск</span>
            </div>

            <!-- Касса (Центральная) -->
            <div 
                class="mob-nav-item mob-nav-cashier"
                @click="$root.$emit('toggleCashier')"
                v-if="$root.user"
            >
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <span class="mob-nav-label">Касса</span>
            </div>

            <!-- Бонусы -->
            <router-link
                to="/bonus"
                class="mob-nav-item"
                :class="{'isActive': page == 'bonus'}"
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 12v10H4V12"></path>
                    <path d="M2 7h20v5H2z"></path>
                    <path d="M12 22V7"></path>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                </svg>
                <span class="mob-nav-label">Бонусы</span>
            </router-link>

            <!-- Меню -->
            <div 
                class="mob-nav-item"
                @click="$root.$emit('toggleMenu')"
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
                <span class="mob-nav-label">Меню</span>
            </div>
        </div>
        <div class="contact-info mb-3">
            <div class="online-box text-center mb-3">
                <div class="online-spawn flex">
                    <div class="circle-online"></div><span class="ml-1 online-text">{{ $root.online }}</span></div>
            </div>
            <div class="social-links-container" v-if="!isCollapsed">
                <a :href="$root.config.tg_channel" target="_blank" class="contact-box social-link-item mb-3">
                    <img src="/assets/image/tg.svg" width="25px" />
                    <span class="social-link-text">Telegram</span>
                </a>
                <a :href="$root.config.vk_url" target="_blank" class="contact-box social-link-item mb-3">
                    <img src="/assets/image/vk.svg" width="25px" />
                    <span class="social-link-text">VK</span>
                </a>
            </div>
            <div v-else>
                <a :href="$root.config.tg_channel" target="_blank" class="contact-box mb-3" :title="'Telegram'" v-b-tooltip:left-main.hover.right="'Telegram'"><img src="/assets/image/tg.svg" width="25px" /></a>
                <a :href="$root.config.vk_url" target="_blank" class="contact-box mb-3" :title="'VK'" v-b-tooltip:left-main.hover.right="'VK'"><img src="/assets/image/vk.svg" width="25px" /></a>
            </div>
        </div>
        <div class="menu-toggle" @click="toggleMenu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="{ 'rotated': isCollapsed }">
                <path d="M15 18l-6-6 6-6" v-if="!isCollapsed"></path>
                <path d="M9 18l6-6-6-6" v-else></path>
            </svg>
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
.contact-info {
    padding-bottom: 70px;
}
.sticky-games-menu {
    position: sticky;
    top: 20px;
    z-index: 10;
}

.games-item {
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

.games-img {
    flex-shrink: 0;
    margin-right: 12px;
}

.games-item-text {
    white-space: nowrap;
    opacity: 1;
    width: auto;
    transition: opacity 0.2s ease-in-out, width 0.2s ease-in-out;
    font-size: 14px;
    font-weight: 500;
    color: #2e3956;
    flex: 1;
    text-align: left;
}

.social-links-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 12px;
    background: rgba(242, 243, 253, 0.4);
    border-radius: 8px;
    margin: 12px;
}

.social-link-item {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 8px 12px;
    border-radius: 5px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.social-link-item:hover {
    background: rgba(255, 255, 255, 0.3);
}

.social-link-item img {
    margin-right: 12px;
    flex-shrink: 0;
}

.social-link-text {
    font-size: 14px;
    font-weight: 500;
    color: #2e3956;
    white-space: nowrap;
}

.left-main.collapsed .games-item-text {
    opacity: 0;
    width: 0;
    overflow: hidden;
    margin: 0;
}

.left-main.collapsed .games-item {
    justify-content: center;
}

.left-main.collapsed .games-img {
    margin-right: 0;
}

.online-text {
    transition: opacity 0.2s ease-in-out, width 0.2s ease-in-out;
    white-space: nowrap;
}

.left-main.collapsed .online-text {
    opacity: 0;
    width: 0;
    overflow: hidden;
    margin: 0;
}

.menu-toggle {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    width: 36px;
    height: 36px;
    background: #f2f3fd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 20;
}

.menu-toggle:hover {
    background: #e0e2f3;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.menu-toggle svg {
    transition: transform 0.3s ease;
    stroke: #496db0;
}

.menu-toggle svg.rotated {
    transform: rotate(180deg);
}

@media screen and (max-width: 992px) {
    .sticky-games-menu {
        display: none !important;
    }
    .menu-toggle {
        display: none !important;
    }
    .social-links-container {
        display: none !important;
    }
    .contact-info {
        display: none !important;
    }
}

/* Desktop Left Menu - Compact & Clean Design */
@media screen and (min-width: 993px) {
    .left-main {
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.04);
    }
    
    /* Logo - Always visible */
    .left-main .logoBox {
        padding: 16px;
        margin-bottom: 16px;
        display: flex !important;
        justify-content: center;
        align-items: center;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .left-main .logo_img {
        width: 40px;
        height: 40px;
        transition: transform 0.2s ease;
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    .left-main .logoBox:hover .logo_img {
        transform: scale(1.05);
    }
    
    /* Games Menu */
    .left-main .games-menu {
        padding: 0 12px;
        margin-bottom: 16px;
    }
    
    /* Compact Game Cards */
    .left-main .games-item {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
        border: none;
        min-height: 44px;
    }
    
    .left-main .games-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }
    
    .left-main .games-item.isActive {
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
    }
    
    .left-main .games-img {
        width: 24px;
        height: 24px;
        margin-right: 12px;
        flex-shrink: 0;
        transition: all 0.2s ease;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
    }
    
    .left-main .games-item-text {
        font-size: 14px;
        font-weight: 600;
        color: #ffffff;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    
    /* Collapsed State - Small Round Icons */
    .left-main.collapsed .games-item {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        padding: 0;
        justify-content: center;
        align-items: center;
        margin: 0 auto 8px auto;
        display: flex !important;
    }
    
    .left-main.collapsed .games-img {
        margin: 0 !important;
        width: 20px;
        height: 20px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        filter: brightness(0) invert(1) !important;
    }
    
    .left-main.collapsed .games-item-text {
        display: none;
    }
    
    .left-main.collapsed .games-item:hover {
        transform: scale(1.05);
    }
    
    /* Online Indicator */
    .left-main .online-box {
        margin: 16px 12px;
        padding: 8px 12px;
        background: rgba(242, 243, 253, 0.6);
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .left-main .online-spawn {
        justify-content: center;
        color: #2e3956;
        font-weight: 500;
        font-size: 13px;
    }
    
    .left-main .circle-online {
        background: #10b981;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }
    
    /* Social Links */
    .left-main .social-links-container {
        background: rgba(242, 243, 253, 0.4);
        border-radius: 8px;
        padding: 12px;
        margin: 0 12px;
        border: 1px solid #e2e8f0;
    }
    
    .left-main .social-link-item {
        padding: 8px 10px;
        border-radius: 6px;
        transition: all 0.2s ease;
        color: #2e3956;
        text-decoration: none;
    }
    
    .left-main .social-link-item:hover {
        background: rgba(255, 255, 255, 0.8);
        transform: translateX(2px);
        color: #2e3956;
    }
    
    .left-main .social-link-text {
        color: #2e3956;
        font-weight: 500;
        font-size: 13px;
    }
    
    /* Collapsed social icons */
    .left-main.collapsed .contact-box {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 6px auto;
        background: rgba(242, 243, 253, 0.6);
        transition: all 0.2s ease;
        border: 1px solid #e2e8f0;
    }
    
    .left-main.collapsed .contact-box:hover {
        background: rgba(242, 243, 253, 0.8);
        transform: scale(1.05);
    }
    
    /* Menu Toggle Button */
    .left-main .menu-toggle {
        background: #f2f3fd;
        border: 1px solid #e2e8f0;
        width: 32px;
        height: 32px;
        bottom: 25px;
    }
    
    .left-main .menu-toggle:hover {
        background: #e0e2f3;
        transform: translateX(-50%) scale(1.05);
    }
    
    .left-main .menu-toggle svg {
        stroke: #496db0;
        width: 16px;
        height: 16px;
    }
}
</style>