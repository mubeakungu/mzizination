<template>
    <div class="content cards slots">
        <div class="s1">
            <div class="s2">
                <div class="s3">
                    <div v-for="h in history" :key="h.id">
                        <router-link :to="'/slots/game/' + h.game_id" class="live_">
                            <div class="live_img">
                                <img :src="h.image">
                            </div>
                            <div class="live">
                                <span class="slotname">{{ h.slot_name }}</span>
                                <span class="username">{{ h.username }}</span>
                                <span class="coef">x {{ h.coef }}</span>
                                <span class="number">{{ h.win.toFixed(2) }}</span>
                            </div>
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
        <div class="slots_box">
            <div class="search_">
                <input 
                    type="search" 
                    placeholder="Поиск..." 
                    v-model="search"
                />
            </div>
            <div class="b1_">
                <div class="casino-set casino-provider casino-icons" @click="randomSlot"><span><img src="/assets/image/random.svg?v=1" class="random_dice"></span></div>
                <div 
                    class="casino-set casino-provider casino-icons"
                    @click="
                        slots = []
                        newGame('hot')
                    "
                >
                    <span>
                        <img src="/assets/image/icon_hot.svg?v=1" class="random_dice">
                    </span>
                </div>
                <div 
                    class="casino-set casino-provider casino-icons" 
                    @click="
                        slots = []
                        newGame('new')
                    "
                >
                    <span>
                        <img src="/assets/image/icon_new.svg?v=1" class="random_dice">
                    </span>
                </div>
                <div 
                    class="casino-set casino-provider" 
                    style="width: 150px;" 
                    v-on-clickaway="closeProviders"
                    @click="providers.status = !providers.status"
                >
                    <span>Все провайдеры</span>
                </div>
                <div id="dropdown" class="dropdown__inner" v-if="providers.status">
                    <ul>
                        <li v-for="(item, index) in providers.items" 
                            @click="
                                providers.current = item.provider
                                providers.status = false
                                slots = []
                            "
                        >
                         {{ item.name }}
                            <span style="margin-left: auto;">{{ item.games }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="game_list">
                <div class="search_empty" v-if="!slots.length && !loading">
                    Игры не найдены
                </div>
                <div class="game_" v-for="slot in this.slots" :key="slot.id" v-else>
                    <router-link :to="'/slots/game/' + slot.game_id" class="">
                        <div class="game_image">
                            <vue-load-image>
                              <img slot="image" :src="slot.icon" v-if="slot.isAnimate">
                              <img slot="image" :src="'/img/slots/'+ slot.title.split(' ').join('') + '.jpg?v=7'"  v-else>
                              <h4 slot="preloader" class="loading"></h4>
                              <h4 slot="error" class="loading_error"></h4>
                              <div slot="image" class="provider_container"><p class="small_provider provider_container2">{{ slot.provider_name }}</p></div>
                            </vue-load-image>
                            <div class="rtp_badge">RTP {{ getRtp(slot.game_id) }}%</div>
                        </div>
                        <div class="game_badges"> <img v-if="slot.hot == 1" src="/assets/image/icon_hot.svg?v=1" class="game_badge"> <img v-if="slot.new == 1" src="/assets/image/icon_new.svg?v=1" class="game_badge"> </div>
                        <div class="info">
                            <div class="title">{{ slot.title }}</div>
                            <div class="go"></div>
                            <router-link :to="'/slots/game/' + slot.game_id + '/demo'" class>
                                <div class="title2">DEMO</div>
                            </router-link>
                            <div class="title3">{{ slot.provider_name }}</div>
                        </div>
                    </router-link>
                </div>
                <div v-if="loading && page < last_page">
                    <infinite-loading/>
                </div>
                <div v-observe-visibility="handleInfinityScroll"/>
            </div>
            <br><br>
        </div>
    </div>
</template>
<script>
import $ from 'jquery';
import InfiniteLoading from 'vue-infinite-loading';
import VueLoadImage from 'vue-load-image'

export default {
    data() {
        return {
            page: 1,
            last_page: null,
            provider: 'list',
            search: '',
            timeout: null,
            slots: [],
            history: [],
            per_page: 36,
            observe: null,
            loading: true,
            isLoading: false,
            providers: {
                status: false,
                current: 'all',
                items: [],
            },
            filter: null
        }
    },
    mounted() {
        this.getSlots(this.page);
        this.getCount();
        this.$socket.emit('getHistory');
    },
    components: {
        'vue-load-image': VueLoadImage,
        InfiniteLoading,
    },
    methods: {
        randomSlot() {
            this.$root.axios.post('/slots/getRandom').then((res) => {
                if(res.data.error) {
                    n(res.data.msg, 'error');
                    return this.$router.push('/slots')
                }
                this.$router.push('/slots/game/'+ res.data.id);
                this.getSlot(res.data.id);
            })
        },
        getRtp(gameId) {
            let hash = 0;
            const str = String(gameId);
            for (let i = 0; i < str.length; i++) {
                hash = ((hash << 5) - hash) + str.charCodeAt(i);
                hash = hash & hash;
            }
            const rtp = 94 + (Math.abs(hash) % 45) / 10;
            return rtp.toFixed(1);
        },
        getSlots() {
            this.loading = true
            this.$root.axios.post(`/slots/get?filter=${this.filter || ''}&provider=${this.providers.current}&page=${this.page}&count=${this.per_page}` + (this.search != '' ? '&search='+ this.search : '')).then((res) => {
                this.slots = [...this.slots, ...Object.values(res.data.data)]
                this.last_page = res.data.last_page
                this.loading = false
            })
        },
        newGame(filter) {
            this.page = 0
            this.filter = filter
            this.loading = true

            this.$root.axios.post("/slots/filter", {
                filter
            })
            .then((e) => {
                this.slots = e.data.data
                this.page = 1
                this.last_page = e.data.last_page
                this.loading = false
            })
        },
        getCount() {
            this.$root.axios.post('/slots/count').then((res) => {
                this.providers.items = res.data;
            })
        },
        setProvider(name) {
            this.provider = name;
            this.$root.axios.post(`/slots/${this.provider}`).then((res) => {
                this.slots = res.data;
                this.last_page = res.data.last_page;
            });
        },
        normalPaginate(e) {
            if (e == "&laquo; Previous") return "«";
            if (e == "Next &raquo;") return "»";
            return e;
        },
        linkGen() {

        },
        closeProviders() {
            this.providers.status = false
        },
        handleInfinityScroll(isVisible) {
            if (!isVisible) {
                return;
            }

            if(this.page >= this.last_page && this.page !== 0) {
                this.isLoading = false
                return;
            }
            this.isLoading = true
            this.page += 1;
            this.getSlots()
        }
    },
    sockets: {
        getHistory(data) {
            this.history = data;
        },

        slotsHistory(data) {
            this.history.unshift(data);

            if(this.history.length > 7) this.history.pop();
        }
    },
    watch: {
        search() {
            clearTimeout(this.timeout)
            this.timeout = setTimeout(() => {
                this.loading = true
                this.page = 1
                this.slots = []
                this.isLoading = false
                this.getSlots()
            }, 450)
            this.isLoading = false
        },
        'providers.current'() {
            this.filter = null
            this.loading = true
            this.page = 1
            this.last_page = 1
            this.slots = []
            this.getSlots()
        }
    }
}
</script>

<style scoped>
.b1_ {
    display: flex;
}

.game_badges {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    position: absolute;
    right: 0;
    top: 0;
}

.game_badge {
    box-sizing: border-box;
    height: 2rem;
    opacity: 1;
    padding: 0;
    position: relative;
    width: 2rem;
}

.rtp_badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(16, 185, 129, 0.7);
    color: #fff;
    font-size: 9px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
    z-index: 5;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.game_:hover .rtp_badge {
    opacity: 1;
}

.slots_box {
    display: inline-flex;
    padding: 10px 35px 10px 30px;
    width: 100%;
}

.search_ {
    margin-right: 5px;
    width: 100%;
}
.infinite-loading-container {
    clear: both;
    text-align: center;
}

.infinite-loading-container [class^=loading-] {
    display: inline-block;
    margin: 5px 0;
    width: 28px;
    height: 28px;
    font-size: 28px;
    line-height: 28px;
    border-radius: 50%;
}

.btn-try-infinite {
    margin-top: 5px;
    padding: 5px 10px;
    color: #999;
    font-size: 14px;
    line-height: 1;
    background: transparent;
    border: 1px solid #ccc;
    border-radius: 3px;
    outline: none;
    cursor: pointer;
}

.btn-try-infinite:not(:active):hover {
    opacity: .8;
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
.loading-default {
    position: relative;
    border: 1px solid #999;
    -webkit-animation: loading-rotating ease 1.5s infinite;
    animation: loading-rotating-data ease 1.5s infinite;
}
@-webkit-keyframes loading-rotating {
    0% {
        -webkit-transform: rotate(0);
        transform: rotate(0);
    }

    to {
        -webkit-transform: rotate(1turn);
        transform: rotate(1turn);
    }
}

@keyframes loading-rotating {
    0% {
        -webkit-transform: rotate(0);
        transform: rotate(0);
    }

    to {
        -webkit-transform: rotate(1turn);
        transform: rotate(1turn);
    }
}
.loading-default:before {
    content: "";
    position: absolute;
    display: block;
    top: 0;
    left: 50%;
    margin-top: -3px;
    margin-left: -3px;
    width: 6px;
    height: 6px;
    background-color: #999;
    border-radius: 50%;
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

.search_ input:focus {
    background-color: #fff;
    border-color: #80bdff;
    box-shadow: 0 0 0 .2rem rgba(0,123,255,.25);
    color: #495057;
    outline: 0;
}

.vue-load-image .loading {
    background-color: #fff;
    height: 100%;
    position: relative;
    width: 100%;
    z-index: 2;
}

.vue-load-image {
    background-color: initial;
    display: inline-block;
    height: 207px;
    overflow: hidden;
    position: relative;
    width: 140px;
}

.vue-load-image img {
    height: 207px;
    width: 140px;
}

.info,.vue-load-image img {
    left: 0;
    position: absolute;
    top: 0;
}

.info {
    display: flex;
    flex-direction: column;
    height: 100%;
    justify-content: space-between;
    opacity: 0;
    pointer-events: none;
    text-align: center;
    transition: .45s ease;
    width: 100%;
}

.provider_container {
    align-items: center;
    background: linear-gradient(180deg,rgba(3,25,58,0),#0a0a0a);
    bottom: 0;
    display: flex;
    justify-content: center;
    left: 0;
    opacity: 1;
    padding: 5px 0;
    position: absolute;
    right: 0;
    z-index: 1;
}

.provider_container2 {
    color: #fff;
    opacity: 1;
}

.small_provider {
    font-size: 8px;
    letter-spacing: .14em;
    line-height: 100%;
    margin-bottom: 0;
    text-transform: uppercase;
}

input.form-control.form.search {
    height: 33px;
    text-align: left;
    width: 100%;
}

.game_ .loading:before {
    background-image: url(/image/mob_logo.png?v=2);
}

.game_ .loading:before,.game_ .loading_error:before {
    -webkit-animation: pulse-loading 1.5s infinite;
    animation: pulse-loading 1.5s infinite;
    background-position: 50%;
    background-repeat: no-repeat;
    background-size: contain;
    content: "";
    display: block;
    height: 100%;
    left: 23%;
    margin: 0 auto;
    position: absolute;
    top: 0;
    width: 50%;
}

.game_ .loading_error:before {
    background-image: url(/image/mob_logo.png?v=2);
}

@-webkit-keyframes pulse-loading {
    50% {
        transform: scale(1.15);
    }
}

@keyframes pulse-loading {
    50% {
        transform: scale(1.15);
    }
}

.providers {
    margin-right: 8px;
    width: 16px;
}

.b1_ .b2_ {
    margin-bottom: 12px;
    margin-left: -10px;
    margin-right: 5px;
    position: relative;
}

.b1_ .b2_ span {
    align-items: center;
    display: flex;
    height: 100%;
    justify-content: center;
    left: 0;
    pointer-events: none;
    position: absolute;
    top: 0;
    width: 41px;
}

.b1_ .dropdown__inner {
    background-color: #f1f2fd;
    border-radius: 10px;
    box-shadow: 5px 10px 15px rgb(0 0 0/10%);
    color: #333;
    display: block;
    margin-bottom: 0!important;
    margin-left: 6%;
    margin-top: 2%;
    padding: 5px 5px 0 0;
    position: absolute;
    width: 240px;
    width: 190px;
    z-index: 4;
}

.b1_ .dropdown__inner ul {
    -webkit-padding-start: 10px;
    -webkit-margin-before: 0;
    -webkit-margin-after: 0;
    margin-block-end: 0;
    margin-block-start: 0;
    max-height: 280px!important;
    overflow-y: auto;
    overscroll-behavior-y: contain;
    padding-right: 10px;
    padding-inline-start: 10px;
    position: relative;
    scrollbar-color: #333;
    scrollbar-width: thin;
}

.b1_ .dropdown__inner ul li {
    align-items: center;
    border-left: 4px solid transparent;
    color: #333;
    cursor: pointer;
    display: flex;
    font-size: 14px;
    font-weight: 500;
    line-height: 32px;
    overflow-x: hidden;
    text-overflow: ellipsis;
    transition: all .2s ease-in-out;
    white-space: nowrap;
}

.b1_ .dropdown__inner ul li.active,.b1_ .dropdown__inner ul li.active:hover,.b1_ .dropdown__inner ul li:hover {
    color: #496db0;
}

.b1_ .dropdown__inner ul li span {
    color: #2155ed;
}

.b1_ .dropdown__inner ul li.active:hover span,.b1_ .dropdown__inner ul li.active span,.b1_ .dropdown__inner ul li:hover span {
    color: #333;
}

.game_list {
    text-align: center;
}

.slots {
    padding: 20px 0 14px;
}

.go {
    background-image: url(/assets/image/button__play.svg);
    background-repeat: no-repeat;
    background-size: 100%;
    height: 42px;
    margin: 0 auto;
    opacity: .95;
    width: 42px;
    z-index: 1;
}

.game_:hover .info {
    opacity: 1;
    pointer-events: all;
}

.info .title {
    font-size: .875rem;
    line-height: 1rem;
    padding-bottom: 20px;
    padding-top: 25px;
    width: 90%;
}

.info .title,.info .title2 {
    color: #fff;
    display: flex;
    font-weight: 700;
    height: 30px;
    justify-content: center;
    margin: 0 auto;
    text-align: center;
    z-index: 1;
}

.info .title2 {
    border: 2px solid rgb(255 255 255/50%);
    border-radius: 8px;
    flex-direction: column;
    font-size: .677rem;
    opacity: .95;
    transition: all .2s ease-out;
    width: 40%;
}

.info .title2:hover {
    background: rgb(255 255 255/20%);
    color: #ffde58;
}

.info .title3 {
    color: #fff;
    font-size: 8px;
    font-weight: 500;
    letter-spacing: .14em;
    line-height: 100%;
    padding-bottom: 5px;
    text-transform: uppercase;
    z-index: 1;
}

.info:before {
    background-color: rgba(32,43,77,.9);
    content: "";
    height: 100%;
    left: 0;
    opacity: .85;
    position: absolute;
    top: 0;
    width: 100%;
}

.list {
    text-align: center;
}

.game_ {
    align-items: center;
    border: 0;
    border-radius: 10px;
    box-shadow: 0 5px 10px 2px rgb(8 10 12/5%);
    cursor: pointer;
    display: inline-flex;
    height: 207px;
    justify-content: center;
    margin: 5px;
    overflow: hidden;
    position: relative;
    transition: .2s ease;
    width: 140px;
}

.game_:hover {
    transform: scale(1.05);
}

.slots_p {
    text-align: center;
}

.game_ .game_image {
    height: 207px;
    width: 140px;
}

@media (max-width:992px) {
    info .title {
        font-size: .775rem;
    }

    .info .title2 {
        font-size: .677rem;
    }

    .game_badge {
        height: 1.6rem;
        width: 1.6rem;
    }

    .b1_ .dropdown__inner {
        right: 13px;
    }

    .b1_ {
        display: block!important;
        padding-top: 5px;
    }

    .slots_box {
        display: block;
        padding: 10px!important;
    }

    .game_,.vue-load-image,.vue-load-image img {
        height: 170px;
        width: 115px;
    }

    .game_ {
        margin: 3px;
    }

    .game_ .game_image {
        height: 170px;
        width: 115px;
    }

    .game_ .info {
        font-size: 12px;
    }

    .game_ .info .title {
        padding-top: 12px;
    }
}

.slots {
    text-align: center;
}

img.random_dice {
    height: 20px!important;
    margin-bottom: 2px;
    width: 20px!important;
}

.page-item.active .page-link {
    background-color: #2b5ced;
    border-color: #2b5ced63;
    color: #fff;
    z-index: 1;
}

.page-link {
    color: #4e6588;
}

.casino-set:hover {
    background-image: linear-gradient(45deg,#4d84ea,#486ef7);
    box-shadow: 0 0 16px 0 #b2b8fb;
    transition: all .2s ease-out;
}

@media (max-width:420px) {
    .casino-set {
        width: 90px;
    }
}

.casino-set {
    align-items: center;
    background: #2155ed;
    border-radius: 5px;
    cursor: pointer;
    display: inline-block;
    justify-content: center;
    margin: 5px;
    min-width: 36px;
    padding: 4px;
    text-align: center;
    transition: all .2s ease-out;
    width: 90px;
}

.casino-icons {
    width: 30px;
}

.casino-provider>span {
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 2px;
}

.s1 {
    position: relative;
}

.s2 {
    overflow: auto;
    padding: 0 10px 5px;
    width: 100%;
}

.s3 {
    justify-content: space-between;
    position: relative;
    width: 100%;
}

.live_,.s3 {
    align-items: center;
    display: flex;
}

.live_ {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 5px 0 #dce0ed;
    height: 74px;
    margin: 0 8px;
    padding-right: 8px;
}

.live_>div:first-child {
    display: flex;
    flex-shrink: 0;
    height: 100%;
    padding: 0;
    width: 50px;
}

.live_>div:first-child>img {
    border-radius: 6px;
    display: block;
    height: 100%;
    width: 100%;
}

.live {
    display: flex;
    flex-flow: column nowrap;
    height: 100%;
    justify-content: space-between;
    padding: 3px 0 6px 6px;
    width: 80px;
}

.live .slotname {
    color: #333;
    font-size: 10px;
}

.live .slotname,.live .username {
    display: inline-block;
    font-weight: 500;
    overflow: hidden;
    text-align: left;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.live .username {
    color: grey;
    font-size: 12px;
    padding: 2px 0;
}

.live .number {
    align-items: center;
    color: #28a745!important;
    display: inline-flex;
    font-size: 15px;
    padding-top: 4px;
}

.live .coef,.live .number {
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
}

.live .coef {
    color: #333;
    display: inline-block;
    font-size: 13px;
    padding: 2px 0;
    text-align: left;
    white-space: nowrap;
}

.search_empty {
    margin-top: 1em;
    text-align: center;
}
</style>