<template>
    <div class="users-box ml-auto mr-3" @click="$root.$emit('open', 'profile')">
        <img 
            height="34"
            :src="$root.user.rank.icon"
            v-if="$root.user.rank.icon" 
            class="ranks circle"
        />
        <img
            :src="$root.user.avatar"
            height="34"
            class="circle"
        />
    <div class="pl-2">
        <div class="users-login">
            {{ $root.user.username }}
        </div>
        <div class="users-balance">
            <ICountUp
                :endVal="$root.user.balance"
                :options="options"
            />
        </div>
    </div>
    </div>
</template>

<script>
import IOdometer from 'vue-odometer';
import 'odometer/themes/odometer-theme-default.css';
import ICountUp from 'vue-countup-v2';

export default {
    components: {
        IOdometer,
        ICountUp
    },
    data() {
        return {
            options: {
                useEasing: true,
                useGrouping: true,
                separator: ',',
                decimal: '.',
                prefix: '',
                suffix: '',
                decimalPlaces: 2
            }
        }
    },
}
</script>

<style scoped>
.odometer.odometer-auto-theme, .odometer.odometer-theme-default {
    font-family: Rubik, sans-serif;
}
</style>