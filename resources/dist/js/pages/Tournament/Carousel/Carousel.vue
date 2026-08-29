<template>
    <div>
        <h5 class="mt-3">{{ section.group.head }}</h5>
        <div class="tournaments-list">
            <div class="tournaments-list__empty" v-if="!section.items.length">
                {{ section.group.empty }}
            </div>
            <Slick ref="slick" :options="splideOptions" class="tournaments-list-wrapper">
                <Card 
                    v-for="item in section.items" 
                    :item="item" 
                    :type="section.type"
                    :key="item.id"
                />
            </Slick>
        </div>
    </div>
</template>

<script>
import Slick from 'vue-slick';
import 'slick-carousel/slick/slick.css';
import Card from './Card'

export default {
    components: {
        Slick,
        Card
    },
    props: ['section'],
    data() {
        return {
            splideOptions: {
                slidesToShow: Math.min(3, this.section.items.length),
                slidesToScroll: Math.min(3, this.section.items.length),
                arrows: true,
                infinite: false,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2,
                            infinite: true,
                            dots: true
                        }
                    }, 
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            },
        }
    },
}
</script>