<template>
    <div class="header">

        <!-- Mobile Top Bar (Only visible on mobile) -->
        <div class="mobile-top-bar">
            <!-- Logo (Left) -->
            <div class="mobile-top-logo" @click="$root.$emit('open', 'home')">
                <div class="mob_logo"></div>
            </div>
            
            <!-- Profile / Auth (Right) -->
            <div class="mobile-top-right">
                <Profile v-if="$root.user" class="mobile-top-profile"/>
                <div v-if="!$root.user" class="mobile-top-auth" @click="auth">
                    <span class="auth-text">Войти</span>
                </div>
            </div>
        </div>

        <!-- Bottom Sheet Overlay -->
        <div class="bottom-sheet-overlay" v-if="showMenu || showCashierSheet" @click="closeAllSheets"></div>
        
        <!-- Cashier Bottom Sheet -->
        <div class="bottom-sheet cashier-sheet" :class="{'bottom-sheet-open': showCashierSheet}">
            <div class="bottom-sheet-handle" @click="$root.$emit('toggleCashier')"></div>
            <div class="bottom-sheet-content">
                <div class="cashier-header">
                    <div class="cashier-title flex-center">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                        Касса
                    </div>
                    <div class="cashier-balance" v-if="$root.user">
                        <ICountUp :endVal="$root.user.balance" :options="{useEasing: true, useGrouping: true, separator: ',', decimal: '.', decimalPlaces: 2}" /> ₽
                    </div>
                </div>
                <div class="cashier-buttons">
                    <button class="cashier-btn deposit" @click="openPayment">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <polyline points="19 12 12 19 5 12"></polyline>
                        </svg>
                        <span>Пополнить</span>
                        <small>Быстро и безопасно</small>
                    </button>
                    <button class="cashier-btn withdraw" @click="openWithdraw">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="19" x2="12" y2="5"></line>
                            <polyline points="5 12 12 5 19 12"></polyline>
                        </svg>
                        <span>Вывести</span>
                        <small>Вывод за 24 часа</small>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menu Bottom Sheet (Instead of "More") -->
        <div class="bottom-sheet menu_mob" :class="{'bottom-sheet-open': showMenu}">
            <div class="bottom-sheet-handle" @click="$root.$emit('toggleMenu')"></div>
            
            <div class="bottom-sheet-content">
                <!-- Разделы -->
                <div class="bottom-sheet-category">
                    <div class="bottom-sheet-category-title">Разделы</div>
                    <div class="bottom-sheet-items">
                        <div 
                            :class="['bottom-sheet-item', {'isActive': page == 'ref'}]" 
                            @click="openRef" 
                        >
                            <div class="mob-img-menu refs"></div>
                            <span>Рефералы</span>
                        </div>
                        <div 
                            :class="['bottom-sheet-item', {'isActive': page == 'faq'}]" 
                            @click="openFaq" 
                        >
                            <div class="mob-img-menu faq"></div>
                            <span>FAQ</span>
                        </div>
                        <router-link
                            :class="['bottom-sheet-item', {'isActive': page == 'tournaments'}]"
                            to="/tournaments"
                            @click.native="$root.$emit('toggleMenu')"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                                <path d="M4 22h16"></path>
                                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                            </svg>
                            <span>Турниры</span>
                        </router-link>
                    </div>
                </div>
                
                <!-- Социальные сети -->
                <div class="bottom-sheet-category">
                    <div class="bottom-sheet-category-title">Социальные сети</div>
                    <div class="bottom-sheet-items">
                        <a :href="$root.config.vk_url" target="_blank" class="bottom-sheet-item">
                            <img src="/assets/image/vk.svg" width="24px" /> 
                            <span>VK Группа</span>
                        </a>
                        <a :href="$root.config.tg_channel" target="_blank" class="bottom-sheet-item">
                            <img src="/assets/image/tg.svg" width="24px" /> 
                            <span>Telegram Канал</span>
                        </a>
                    </div>
                </div>

                <div class="bottom-sheet-category" v-if="$root.user">
                    <div class="bottom-sheet-items">
                         <div class="bottom-sheet-item" @click="logout" style="color: #ef4444;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <span>Выход</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Full-Width Header -->
        <div class="desktop-header">
            <div class="desktop-header-inner">
                <!-- Left: Logo + Navigation -->
                <div class="desktop-header-left">
                    <!-- Logo -->
                    <div class="desktop-logo" @click="$root.$emit('open', 'home')">
                        <div class="logo_img"></div>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="desktop-nav">
                        <div 
                            class="nav-item" 
                            :class="{'isActive': page == 'home'}"
                            @click="$root.$emit('open', 'home')"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                            <span>Главная</span>
                        </div>
                        <div 
                            class="nav-item" 
                            :class="{'isActive': page == 'ref'}" 
                            @click="$root.$emit('open', 'ref')" 
                            v-if="$root.user"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Рефералы</span>
                    </div>
                    <div 
                        class="nav-item" 
                        :class="{'isActive': page == 'bonus'}" 
                        @click="$root.$emit('open', 'bonus')" 
                        v-if="$root.user"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 12v10H4V12"></path>
                            <path d="M2 7h20v5H2z"></path>
                            <path d="M12 22V7"></path>
                            <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                        </svg>
                        <span>Бонусы</span>
                    </div>
                    <div 
                        class="nav-item" 
                        :class="{'isActive': page == 'tournaments'}" 
                        @click="$root.$emit('open', 'tournaments')" 
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                            <path d="M4 22h16"></path>
                            <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                            <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                        </svg>
                        <span>Турниры</span>
                    </div>
                    <div 
                        class="nav-item" 
                        :class="{'isActive': page == 'faq'}" 
                        @click="$root.$emit('open', 'faq')" 
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span>FAQ</span>
                    </div>
                    </nav>
                </div>
                
                <!-- Right Section -->
                <div class="desktop-right" style="margin-right: 15px;">
                    <Profile v-if="$root.user"/>
                    <div class="header-buttons" v-if="$root.user">
                        <button class="btn-cashier" @click="openCashier('deposit')">
                            <i class="flaticon-coins"></i>
                            <span>Кошелек</span>
                        </button>
                    </div>
                    <div class="header-buttons" v-if="$root.user == null">
                        <button class="btn-auth" @click="auth">
                            <img src="/assets/image/vk_white.svg" width="20" />
                            <span>Войти</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Old header for mobile compatibility -->
        <div class="container rows align-items-center mobile-old-header">
            <div class="header_ flex" style="margin-left: auto;">
                <div class="header_in flex">
                    <div class="top_">
                        <div class="list">
                            <div class="mob_logo" @click="$root.$emit('open', 'home')"></div>
                        </div>
                        <Profile v-if="$root.user"/>
                    </div>
                </div>
            </div>
        </div>
        
        <CashierModal ref="cashier" />
    </div>
</template>

<script>
import Profile from "./Profile"
import ICountUp from 'vue-countup-v2'
import CashierModal from "./modals/CashierModal"

export default {
    props: ['page'],
    components: {
        Profile,
        ICountUp,
        CashierModal
    },
    data() {
        return {
            showMenu: false,
            showCashierSheet: false
        }
    },
    mounted() {
        this.$root.$on('open', () => {
            this.showMenu = false
            this.showCashierSheet = false
        })
        this.$root.$on('toggleMenu', () => {
            this.showMenu = !this.showMenu
            this.showCashierSheet = false
        })
        this.$root.$on('toggleCashier', () => {
            this.showCashierSheet = !this.showCashierSheet
            this.showMenu = false
        })
    },
    methods: {
        openCashier(tab) {
            this.$refs.cashier.show(tab)
        },
        auth() {
            location.href = `/auth/vkontakte`
        },
        logout() {
            location.href = `/user/logout`
        },
        closeAllSheets() {
            this.showMenu = false
            this.showCashierSheet = false
        },
        openPayment() {
            this.$root.$emit('open', 'payment')
            this.closeAllSheets()
        },
        openWithdraw() {
            this.$root.$emit('open', 'withdraw')
            this.closeAllSheets()
        },
        openRef() {
            this.$root.$emit('open', 'ref')
            this.closeAllSheets()
        },
        openFaq() {
            this.$root.$emit('open', 'faq')
            this.closeAllSheets()
        }
    }
}
</script>

<style scoped>
/* Hide old mobile header on desktop */
@media screen and (min-width: 993px) {
    .mobile-old-header {
        display: none !important;
    }
}

/* Hide desktop header on mobile */
@media screen and (max-width: 992px) {
    .desktop-header {
        display: none !important;
    }
}
</style>
