<template>
    <div class="header">
        <div class="container rows align-items-center">
            <div class="header-menu rows" v-if="showMenu || $mq.desktop">
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
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
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
            </div>
            <div class="header_ flex" style="margin-left: auto;">
                <div class="header_in flex">
                    <div class="top_">
                        <div class="list">
                            <div class="mob_logo"  @click="$root.$emit('open', 'home')"></div>
                            <div class="online-spawn flex">
                                <div class="circle-online"></div><span class="ml-1" style="letter-spacing: 1.1px;">{{ $root.online }}</span></div>
                        </div>
                        <Profile v-if="$root.user"/>
                    </div>
                    <div class="header-buttons" v-if="$root.user">
                        <button class="blue mr-2" @click="$root.$emit('open', 'payment')">Пополнить</button>
                        <button class="ser" @click="$root.$emit('open', 'withdraw')">Вывести</button>
                        <!---->
                    </div>
                </div>
            </div>
            <div class="header-buttons ml-auto" v-if="$root.user == null">
                <button class="blue" @click="auth">
                    <img src="/assets/image/vk_white.svg" width="25" />
                    <span class="ml-2">Авторизация</span>
                </button>
            </div>
        </div>
        <div class="header-menu menu_mob" v-if="showMenu">
            <a :href="$root.config.vk_url" target="_blank" class="item">
                <img src="/assets/image/vk.svg" width="22px" /> 
                VK Группа
            </a>
            <a :href="$root.config.tg_channel" target="_blank" class="item">
                <img src="/assets/image/tg.svg" width="22px" /> TG Канал
            </a>
            <div 
                :class="['item', {'isActive': page == 'ref'}]" 
                @click="$root.$emit('open', 'ref')" 
            >
                <div class="mob-img-menu refs"></div>
                Рефералы
            </div>
            <div 
                :class="['item', {'isActive': page == 'faq'}]" 
                @click="$root.$emit('open', 'faq')" 
            >
                <div class="mob-img-menu faq"></div>
                FAQ
            </div>
        </div>
    </div>
</template>

<script>
import Profile from "./Profile"

export default {
    props: ['page'],
    components: {
        Profile
    },
    data() {
        return {
            showMenu: false
        }
    },
    mounted() {
        this.$root.$on('open', () => {
            this.showMenu = false
        })
        this.$root.$on('toggleMenu', () => {
            this.showMenu = !this.showMenu
        })
    },
    methods: {
        auth() {
            location.href = `/auth/vkontakte`
        },
        logout() {
            location.href = `/user/logout`
        },
    }
}
</script>

<style scoped>
sup {
    background: #e91e63
}
.search_ {
    margin-right: 5px;
    width: 100%;
}
.search_::before {
    filter: invert(.8);
}
.search_:before {
    background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAHxqAAB8agGqO5LeAAAE7WlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNi4wLWMwMDIgNzkuMTY0NDg4LCAyMDIwLzA3LzEwLTIyOjA2OjUzICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1sbnM6cGhvdG9zaG9wPSJodHRwOi8vbnMuYWRvYmUuY29tL3Bob3Rvc2hvcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgMjIuMCAoV2luZG93cykiIHhtcDpDcmVhdGVEYXRlPSIyMDIzLTA0LTA2VDIxOjI3OjMxKzAzOjAwIiB4bXA6TW9kaWZ5RGF0ZT0iMjAyMy0wNC0wNlQyMTozNToxMyswMzowMCIgeG1wOk1ldGFkYXRhRGF0ZT0iMjAyMy0wNC0wNlQyMTozNToxMyswMzowMCIgZGM6Zm9ybWF0PSJpbWFnZS9wbmciIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MDg5NDM0YWYtYmI4ZS00MDQ2LWEwNzQtY2E4ZjU1MmVmOTk5IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjA4OTQzNGFmLWJiOGUtNDA0Ni1hMDc0LWNhOGY1NTJlZjk5OSIgeG1wTU06T3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOjA4OTQzNGFmLWJiOGUtNDA0Ni1hMDc0LWNhOGY1NTJlZjk5OSI+IDx4bXBNTTpIaXN0b3J5PiA8cmRmOlNlcT4gPHJkZjpsaSBzdEV2dDphY3Rpb249ImNyZWF0ZWQiIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6MDg5NDM0YWYtYmI4ZS00MDQ2LWEwNzQtY2E4ZjU1MmVmOTk5IiBzdEV2dDp3aGVuPSIyMDIzLTA0LTA2VDIxOjI3OjMxKzAzOjAwIiBzdEV2dDpzb2Z0d2FyZUFnZW50PSJBZG9iZSBQaG90b3Nob3AgMjIuMCAoV2luZG93cykiLz4gPC9yZGY6U2VxPiA8L3htcE1NOkhpc3Rvcnk+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+h8ZO7wAAA6JJREFUaIHVmV1IFUEYhh/XwlRKsT9LwwKVoCjIEhK9iMjACKIugqAoiagugopuCgwFkSCwIgqSbrqIiiDrqj+irCAthIiK8AeFjAzTzP4I83Qx53D0OLOzuzPnnHpgbnZ23u/9zp6dnfkmBTMWA1VACbAIyAOyAQcYAfqAHuA1cBtoBUKGMa0xDTgAvEOY8tP6gQZgdsJdx1APjOE/AVm7BGQl1j6sBgYMjavazkQlcTxOCYxv101Npmj6LwLVmntGgedAC9CFeMlDQCawACgHyoAMjU4b4smPae7zzTXcf8VeRJLpHvWqiM5aqtZuz77ghEuwP8B+A+31wGcX/WYD7QlsdgnSDsy0FOeGS5xaU/EMxFwvE79nKi6hURErBKw0ET6pEG01EdVwQRHzUVDBeYgZKFbwe7gvnrRJ4oYQE4RvGhRiu2w41bBcEftuELEeidALGy49ovqLFfsRqVCI7LDpVMNShYfDfkTqJAIfgFSbTj3QIvFx08tAB1gILJP0PUR8/BLJHcm1Ui8DHWAJ4rHG8tTEUUBk72QuUKAbGHkieZK+TjNPgXiruK5dTTjAHMSuL5ZBE0cBGQZ+SK7P0A10UC/lrS+nPRDZfcai227gAN8UfZkmjgKSjnxbIHtKE3CA98AnSV+uoakgzEd4iuWLbqCDqIbIXuxVZp4CsUJy7RfwUTfQAToQdadYygxNBaFccu0lYhLwxHbky4MSG+48koa8UnPGj0i+RCAEnLPpVEO1wkOlX6FbCqEiW041dEhi9wYR2igRCiH21vGmRhG7Lqigqlxz0NSpC2sUMUeAnKCilQrRELDJyK6cQsT0Kot31FS8SSFse9tbgbogbqVQl4oofaqSOW8hxhEX/T7EGYsVioGfLsEGgX0BdDegP1d5Zuh9EqXoz0IGgbPAOmAuk1epOYivdS3QrdEa3x7bTqYIf+cifYhdXlvYuKxO5rXJtr1GpAEPDAzp2gDwW9HnqfDgl21Ezz5stWNh7VMu91yJRzJTEPP7kIH5UcTMF/uxu+wypikeyYB4qbeEA3R6MN8PXAX2ANNddJtdNE7rDNmggOg5e6TiMUz0nL0b7zWA+8BaRV8jcCiwyyQgqzhGWn0SfQXiCepkapLoyzcpiG+SKhlfxe1kMxV4gzqZvcmz5p8s5Gc3kbY1ac4CMAtxzCFLZAjYLSuG/YsMIMpTXyV92YgN2n9FPmLFPf6JdBGf3WvcKQReIZLoIVyd/AvlAEtjj4f/kQAAAABJRU5ErkJggg==);
    background-repeat: no-repeat;
    background-size: 16px 16px;
    content: "";
    height: 16px;
    position: absolute;
    transform: translateY(78%) translatex(50%);
    width: 16px;
}
.search_ input {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background: #fff;
    border: 2px solid rgb(33 85 237/20%);
    border-radius: 6px;
    color: #333;
    font-size: 16px;
    font-weight: 400;
    height: 33px;
    margin-top: 5px;
    padding-left: 35px;
    width: 100%;
}
.search_ input:focus-visible {
    outline: 0;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 6px;
    margin: 0 4px;
    flex-shrink: 0;
    white-space: nowrap;
}
.nav-item svg {
    flex-shrink: 0;
    stroke: #2e3956;
    transition: stroke 0.2s ease;
}
.nav-item span {
    white-space: nowrap;
    color: inherit;
}
.nav-item:hover {
    background-color: rgba(33, 85, 237, 0.1);
}
.nav-item:hover svg {
    stroke: #2155ed;
}
.nav-item.isActive {
    color: #2155ed;
    background-color: rgba(33, 85, 237, 0.1);
}
.nav-item.isActive svg {
    stroke: #2155ed;
}
@media screen and (max-width: 992px) {
    .nav-item span {
        display: none;
    }
    .nav-item {
        padding: 8px;
    }
}
.header-buttons {
    display: flex;
    gap: 10px;
}
.header-buttons button.blue {
    background: linear-gradient(45deg, #2561d0, #1e4eff);
    border: none;
    color: #fff;
    font-weight: 500;
    padding: 10px 24px;
    border-radius: 8px;
    box-shadow: 0 0 16px 0 rgba(178, 184, 251, 0.6);
    transition: all 0.2s ease;
}
.header-buttons button.blue:hover {
    background: linear-gradient(45deg, #4d84ea, #486ef7);
    box-shadow: 0 0 20px 0 rgba(178, 184, 251, 0.8);
}
.header-buttons button.ser {
    background: #dce0ed;
    border: none;
    color: #38477c;
    font-weight: 500;
    padding: 10px 24px;
    border-radius: 8px;
    transition: all 0.2s ease;
}
.header-buttons button.ser:hover {
    background: #c8cee4;
    color: #2e3956;
}
.hamburger-menu {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    width: 30px;
    height: 30px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    z-index: 1001;
}
.hamburger-menu span {
    width: 100%;
    height: 3px;
    background: #2e3956;
    border-radius: 3px;
    transition: all 0.3s ease;
    transform-origin: center;
}
.hamburger-menu span.active:nth-child(1) {
    transform: translateY(10px) rotate(45deg);
}
.hamburger-menu span.active:nth-child(2) {
    opacity: 0;
}
.hamburger-menu span.active:nth-child(3) {
    transform: translateY(-10px) rotate(-45deg);
}
@media screen and (min-width: 993px) {
    .hamburger-menu {
        display: none;
    }
}
</style>