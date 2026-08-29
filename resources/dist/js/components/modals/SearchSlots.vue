<template>
    <b-modal 
        id="search" 
        @show="showModal"
        @hidden="resetModal"
        hide-footer
        size="lg"
        dialog-class="search-modal-dialog"
    >
        <template #modal-title>Поиск игр</template>
        <fieldset class="form-group">
            <div class="search-input-wrapper">
                <label class="input-search select-games-search-for-modal__input input--empty">
                    <input 
                        id="games-search" 
                        name="games-search"
                        v-model="search"
                        type="text"
                        placeholder="Поиск игры..." 
                        autocomplete="off" 
                        class="input__native form-control"
                    >
                </label>
            </div>
            <div class="game-search-modal">
                <div class="search_empty" v-if="!slotsSearch.length && !loading">
                    Игры не найдены
                </div>
                <div role="listbox" id="games-search-menu" class="select-games-search-for-modal__dropdown" v-for="slot in this.slotsSearch" :key="slot.id">
                    <div id="games-search-item-0" role="option" class="select-games-search-for-modal__option option option--highlighted">
                        <router-link tag="a" :to="'/slots/game/' + slot.game_id" class="">
                            <a class="select-games-search-for-modal__option-link">
                                <span></span> 
                                <img :src="slot.icon" v-if="slot.isAnimate" class="select-games-search-for-modal__option-image image">
                                <img :src="'/img/slots/'+ slot.title.split(' ').join('') + '.jpg?v=7'"  v-else class="select-games-search-for-modal__option-image image"> 
                                <div class="select-games-search-for-modal__option-name">
                                <span class="select-games-search-for-modal__option-title body2">
                                    {{ slot.title }}
                                </span> 
                                <span class="select-games-search-for-modal__option-description body4">
                                    {{ slot.provider_name }}
                                </span></div>
                            </a>
                        </router-link>
                    </div>
                </div>
                <div v-if="isLoading"><infinite-loading></infinite-loading></div>
                <div v-observe-visibility="handleInfinityScroll"/>
            </div>
        </fieldset>
    </b-modal>
</template>

<script>
import InfiniteLoading from 'vue-infinite-loading';

export default {
    data() {
        return {
            provider: 'list',
            slotsSearch: [],
            per_page: 36,
            loading: true,
            isLoading: false,
            page: 1,
            search: '',
            providers: {
                status: false,
                current: 'all',
                items: [],
            },
        }
    },
    mounted() {
        this.getSlots(this.page);
    },
    components: {
        InfiniteLoading,
    },
    methods: {
        showModal() {
            this.$root.$emit('searchModalState', true)
        },
        resetModal() {
            this.$root.$emit('searchModalState', false)
        },
        getSlots() {
            this.$root.axios.post(`/slots/get?provider=${this.providers.current}&page=${this.page}&count=${this.per_page}` + (this.search != '' ? '&search='+ this.search : '')).then((res) => {
                this.slotsSearch = [...this.slotsSearch, ...Object.values(res.data.data)]
                this.last_page = res.data.last_page
                this.loading = false
            })
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
    watch: {
        search() {
            clearTimeout(this.timeout)
            this.timeout = setTimeout(() => {
                this.loading = true
                this.page = 1
                this.slotsSearch = []
                this.isLoading = false
                this.getSlots()
            }, 450)
            this.isLoading = false
        }
    }
}
</script>

<style scoped>
.changeHistory {
    color: #00000040;
    cursor: pointer;
    margin-top: 15px;
    text-align: center;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.slot_box {
    display: inline-flex;
}

.option {
    align-items: center;
    color: #fff;
    cursor: pointer;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: flex-start;
    margin: 0;
    outline: none;
    padding: 8px 16px;
    text-decoration: none;
    text-shadow: none;
    transition: color .3s ease,background-color .3s ease;
    width: 100%;
}

.option:first-of-type {
    margin-top: 0;
}

.option:last-of-type {
    margin-bottom: 0;
}

.option:only-child {
    margin: 0;
}

.option--highlighted {
    color: #fff;
}

.input input:not(:-moz-placeholder-shown)+.input__label,.input textarea:not(:-moz-placeholder-shown)+.input__label {
    font-size: 12px;
    transform: translate3d(0,-6px,0);
}

.input-search {
    align-items: center;
    display: flex;
    margin: auto;
    overflow: hidden;
    position: relative;
    width: 100%;
}

.input__focus {
    background: rgba(34,43,73,.05);
    height: 100%;
    left: 0;
    position: absolute;
    top: 0;
    transform: scaleX(0);
    transform-origin: left;
    width: 100%;
    z-index: -1;
}

.input-search input {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background: #222b49;
    border: 0;
    color: #fff;
    font-family: inherit;
    font-size: 16px;
    font-weight: 400;
    height: 56px;
    padding: 16px 16px 0;
    transition: all .15s ease;
    width: 100%;
}

.input-search input:hover {
    background: rgba(34,43,73,.04);
}

.input-search input:not(:-moz-placeholder-shown)+.input__label,.input-search textarea:not(:-moz-placeholder-shown)+.input__label,.input input:not(:-moz-placeholder-shown)+.input__label,.input textarea:not(:-moz-placeholder-shown)+.input__label {
    font-size: 12px;
    transform: translate3d(0,-6px,0);
}

.input-search input:focus {
    background: rgba(34,43,73,.05);
    outline: none;
}

.input-search input:-webkit-autofill {
    -webkit-text-fill-color: #fff!important;
    background: #222b49!important;
    background-clip: content-box!important;
    border: none!important;
    border-radius: 0!important;
    box-shadow: inset 0 0 0 100px #222b49!important;
    color: #fff!important;
    -webkit-transition: color .3s ease,background-color .3s ease,border-color .3s ease!important;
    transition: color .3s ease,background-color .3s ease,border-color .3s ease!important;
}

.input-search .input__native {
    padding: 0 16px 0 48px;
}

@media(min-width:960px) {
    .input-search .input__native {
        padding: 0 56px;
    }
}


#games-search {
    background: #efefef;
    color: #333;
}


#games-search::-moz-placeholder {
    color: hsla(0,0%,100%,.4)!important;
}

.select-games-search-for-modal {
    color: #fff;
    display: block;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.2;
    margin: 0;
    padding: 0;
    position: relative;
    text-align: left;
    width: 100%;
}

.select-games-search-for-modal .input__native:hover {
    background-color: #283151;
}

.select-games-search-for-modal__dropdown {
    background-color: #efefef;
    border-radius: 10px;
    display: block;
    list-style: none;
    margin-top: 16px;
    padding: 0;
    width: 100%;
}

.select-games-search-for-modal__option-image {
    height: 50px;
    margin-right: 30px;
    width: 100px;
}

.select-games-search-for-modal .select-games-search-for-modal__option {
    cursor: pointer;
    display: block;
    margin: 0;
    padding: 0;
    width: 100%;
}

.select-games-search-for-modal__option-link {
    align-items: center;
    border-radius: 10px;
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    font-size: 12px;
    font-weight: 600;
    justify-content: flex-start;
    letter-spacing: .12px;
    line-height: 1.2;
    margin-left: -16px;
    margin-right: -16px;
    padding: 16px;
    text-align: left;
    text-decoration: none;
    text-transform: uppercase;
    transition: background-color .3s ease,color .3s ease;
}

@media(min-width:720px) {
    .select-games-search-for-modal__option-link {
        font-size: 16px;
    }
}

@media(min-width:960px) {
    .select-games-search-for-modal__option-link {
        margin: unset;
        padding: 16px 40px;
    }
}

@media(min-width:1248px) {
    .select-games-search-for-modal__option-link {
        padding: 8px 24px;
    }
}

.select-games-search-for-modal__option-link:hover {
    background-color: #fff;
}

.select-games-search-for-modal__option-title {
    color: #1b1b1b;
    display: block;
    text-transform: none;
}

.select-games-search-for-modal__option-description {
    color: #969696;
    display: block;
    font-size: 12px;
    text-transform: none;
}

.select-games-search-for-modal__option-image {
    border-radius: 8px;
    display: inline-block;
    height: auto;
    margin: 0 16px 0 0;
    vertical-align: top;
    width: 64px;
}

@media(max-width:992px) {
    .select-games-search-for-modal__option-image {
        width: 52px;
    }
}

.game-icon {
    align-items: center;
    display: flex;
    height: 100%;
    justify-content: center;
    width: 32px;
}

.s1yetyrx {
    fill: #7d5cf5;
    height: 1.4em;
    width: 1.4em;
}

.option:only-child {
    margin: 0;
}
.option:last-of-type {
    margin-bottom: 0;
}
.option:first-of-type {
    margin-top: 0;
}
.option--highlighted {
    color: #fff;
}
.option {
    align-items: center;
    color: #fff;
    cursor: pointer;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: flex-start;
    margin: 0;
    outline: none;
    padding: 8px 16px;
    text-decoration: none;
    text-shadow: none;
    transition: color .3s ease,background-color .3s ease;
    width: 100%;
}
.search_empty {
    margin-top: 1em;
    text-align: center;
}
#games-search {
    background: #fff !important;
    color: #333 !important;
}
#games-search::placeholder {
    color: #999 !important;
    opacity: 1 !important;
}
.input-search .input__native {
    padding: 0 20px 0 20px;
    font-size: 18px !important;
    background: #fff !important;
    color: #333 !important;
}
.input-search .input__native::placeholder {
    font-size: 18px !important;
    color: #999 !important;
    opacity: 1 !important;
}
.input-search {
    align-items: center;
    display: flex;
    margin: auto;
    overflow: visible;
    position: relative;
    width: 100%;
    margin-bottom: 10px;
}
.input-search input {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background: #fff !important;
    border: 2px solid #ddd !important;
    border-radius: 8px;
    color: #333 !important;
    font-family: inherit;
    font-size: 18px !important;
    font-weight: 400;
    height: 60px;
    padding: 0 20px;
    transition: all .15s ease;
    width: 100%;
    box-sizing: border-box;
}
.input-search input:focus {
    border-color: #2155ed !important;
    outline: none;
}
.input-search input::placeholder {
    color: #999 !important;
    opacity: 1 !important;
}

/* Dark theme overrides */
body.dark .modal-content .search-input-wrapper {
    background: #222b49 !important;
}

body.dark .modal-content .input-search input {
    background: #222b49 !important;
    border: 2px solid #384462 !important;
    color: #fff !important;
}

body.dark .modal-content .input-search input:focus {
    border-color: #7d5cf5 !important;
    background: #283151 !important;
}

body.dark .modal-content .input-search input::placeholder {
    color: rgba(255, 255, 255, 0.5) !important;
    opacity: 1 !important;
}
.search-modal-dialog {
    max-width: 700px;
}
.search-input-wrapper {
    position: sticky;
    top: 0;
    background: #fff;
    z-index: 10;
    padding-bottom: 10px;
    margin-bottom: 10px;
}
.game-search-modal {
    max-height: 500px;
    overflow-y: auto;
}
.select-games-search-for-modal__dropdown {
    margin-top: 10px;
}
</style>