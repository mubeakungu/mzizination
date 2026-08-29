<template>
    <div class="main">
        <a href="javascript:;" class=""  @click="$root.$emit('open', 'live')">
            <div class="main-item">
                <picture><img src="/assets/image/main1.png" class="main-item-img-big"></picture>
                <div class="main-item-overlay"></div>
                <div class="main-item-left">
                    <div class="main-item-left-text slots_text">Играй в Live игры
                        <br>прямо сейчас! </div> <a type="button" class="main-item-btn" @click="$root.$emit('open', 'live')">Играть в Live</a></div>
            </div>
        </a>
        <a href="javascript:;" class="" @click="$root.$emit('open', 'slots')">
            <div class="main-item">
                <picture><img src="/assets/image/main2.png" class="main-item-img-bg"></picture>
                <div class="main-item-left">
                    <div class="main-item-left-text slots_text">Испытай удачу
                        <br> в топовых слотах! </div> <a type="button" class="main-item-btn" @click="$root.$emit('open', 'slots')">Играть в Slots</a></div>
            </div>
        </a>
    </div>
</template>