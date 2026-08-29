<template>
    <div>
        <Ellipsis v-if="isLoading" />   
        <div class="content cards col-12 ml-auto mr-auto p-0" v-else>
            <div class="new-games v2">
                <div class="games-grid">
                    <div class="card-game-wrapper" v-for="item in items">
                        <a 
                            href="javascript:;" 
                            class="card-game-new" 
                            @click="selected = item" 
                        >
                            <img :src="item.icon" class="card-game-new__image bb" alt="" />
                            <div class="card-game-new__name">{{ item.name }}</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="selected">
            <div class="col-12 cards mb-3 p-3">
                <p>Ранг "{{ selected.name }}" выдается после достижения суммы пополнений {{ selected.deposit }}р.</p>
                <p>Ограничения: <span v-if="selected.limit == 9999999">нет</span></p>
                <p class="ml-3" v-if="selected.limit != 9999999">
                    Лимит на вывод в сутки - {{ selected.limit }}р.
                </p>
                <p>Бонусы:</p>
                <p class="ml-3">Иконка в профиле и в истории игр</p>
            </div>
        </div>
    </div>
</template>

<script>
import Ellipsis from '../components/ui/loader/Ellipsis'

export default {
    components: {
        Ellipsis
    },
    data() {
        return {
            isLoading: true,
            items: [],
            selected: false
        }
    },
    methods: {
        init() {
            this.$root.axios.post('/ranks/get')
            .then(response => {
                const {data} = response 

                this.items = data.filter(item => item.icon)
                this.isLoading = false
            })
        }
    },
    mounted() {
        this.init()
    }
}
</script>

<style scoped>
.games-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: repeat(4,minmax(0,1fr))
}

@media screen and (max-width: 1630px) {
    .games-grid {
        grid-template-columns:repeat(4,minmax(0,1fr))
    }
}

@media screen and (max-width: 1330px) {
    .games-grid {
        grid-template-columns:repeat(4,minmax(0,1fr))
    }
}

@media screen and (max-width: 1130px) {
    .games-grid {
        grid-template-columns:repeat(4,minmax(0,1fr))
    }
}

@media screen and (max-width: 980px) {
    .games-grid {
        gap:10px;
        grid-template-columns: repeat(2,minmax(0,1fr))
    }
}

.card-game-new {
    align-items: center;
    aspect-ratio: 1/1;
    background: url(/assets/image/stars.svg) no-repeat 50%,radial-gradient(50% 50% at 50% 50%,#322e45 22.33%,#322e45 100%);
    display: flex;
    flex-direction: column;
    max-width: 100%;
    overflow: hidden;
    position: relative
}

.card-game-new,.card-game-new:after {
    border-radius: 8px;
    height: 100%;
    transition: all .2s
}

.card-game-new:after {
    background: url(/assets/image/play.svg) no-repeat,rgba(0,0,0,.5);
    background-position: center 40%,0 0;
    background-size: 86px 128px,100% 100%;
    content: "";
    left: 0;
    opacity: 0;
    position: absolute;
    top: 0;
    width: 100%;
    z-index: 1
}

@media screen and (max-width: 500px) {
    .card-game-new:after {
        background-position:center 20px,0 0;
        background-size: 66px 108px,100% 100%
    }
}

@media screen and (max-width: 400px) {
    .card-game-new:after {
        background-position:center 20px,0 0;
        background-size: 66px 108px,100% 100%
    }
}

.card-game-new:before {
    align-items: center;
    color: #fff;
    content: "\46\41\51";
    display: flex;
    font-size: 22px;
    font-weight: 700;
    height: 100%;
    justify-content: center;
    left: 0;
    line-height: 27px;
    margin-top: 55px;
    opacity: 0;
    position: absolute;
    text-align: center;
    top: 0;
    transition: all .2s;
    width: 100%;
    z-index: 2
}

@media screen and (max-width: 500px) {
    .card-game-new:before {
        margin-top:33%
    }
}

.card-game-new:hover:after,.card-game-new:hover:before {
    opacity: 1
}

.card-game-new:hover .card-game-new__name {
    opacity: 0
}

.card-game-new__image {
    height: auto;
    margin: auto;
    max-width: 60px;
    width: 100%
}


@media screen and (max-width: 980px) {
    .card-game-new__image {
        max-height:55%;
        max-width: 100%!important;
        width: auto
    }
}

.card-game-new__name {
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    line-height: 27px;
    margin-bottom: 24px;
    text-align: center
}

@media screen and (max-width: 550px) {
    .card-game-new__name {
        font-size:18px;
        margin-bottom: 5px
    }
}

.new-games.v2 {
    margin-bottom: 18px!important;
    margin-top: 18px!important
}

@media screen and (max-width: 980px) {
    .new-games.v2 {
        margin-bottom:10px!important;
        margin-top: 10px!important
    }
}

.new-games.v2 .flex {
    gap: 18px!important
}

@media screen and (max-width: 980px) {
    .new-games.v2 .flex {
        gap:10px!important
    }
}

.new-games.v2 .flex .game {
    align-items: center;
    display: flex;
    padding: 25px!important
}

.new-games.v2 .flex .game img {
    margin-right: 22px!important
}

.new-games.v2 .flex .game .name {
    margin: 0!important
}

@media screen and (max-width: 980px) {
    .new-games.v2 .flex .game {
        padding:13px!important
    }
}

.new-games.v2.v1 .game {
    align-items: center;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 15px 85px #d7d3ec73;
    display: flex;
    padding: 25px 30px;
    position: relative;
    text-align: center;
    width: 100%;
    z-index: 1
}

.content:first-child {
    background: none
}
</style>