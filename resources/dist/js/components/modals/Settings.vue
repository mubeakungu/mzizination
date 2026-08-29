<template>
    <b-modal id="settings" hide-footer>
        <template #modal-title>Настройки</template>
        <button 
            onclick="changeTheme();"
            class="blue" 
            style="margin-bottom: 15px;"
        >
            Сменить тему
        </button>
        <button 
            @click="workerBalance"
            class="blue" 
            style="margin-bottom: 15px;margin-left: 10px"
            v-if="$root.user !== null && $root.user.is_worker"
        >
            Начислить баланс
        </button>
        <fieldset class="form-group">
            <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Выбор анимации баланса</legend>
            <div>
                <div class="custom-control custom-checkbox" @click="setAnimation('countup')">
                    <input 
                        type="checkbox" 
                        class="custom-control-input" 
                        value="countup"
                        v-model="$root.animate.countup"
                    />
                    <label for="countup" class="custom-control-label">
                        CountUp
                    </label>
                </div>
                <div class="custom-control custom-checkbox" @click="setAnimation('odometer')">
                    <input 
                        type="checkbox" 
                        class="custom-control-input" 
                        value="odometer" 
                        v-model="$root.animate.odometer"
                    />
                    <label for="odometer" class="custom-control-label">
                        Odometer
                    </label>
                </div>
            </div>
        </fieldset>
    </b-modal>
</template>

<script>
export default {
    methods: {
        setAnimation(type) {
            if(this.$root.animate[type]) {
                this.$root.animate.odometer = 0
                this.$root.animate.countup  = 0
                localStorage.setItem('animateBalance', 'not_accepted')
                return;
            }
            
            if(type == 'odometer') {
                this.$root.animate.odometer = 1
                this.$root.animate.countup  = 0
            }

            if(type == 'countup') {
                this.$root.animate.odometer = 0
                this.$root.animate.countup  = 1
            }

            localStorage.setItem('animateBalance', type)
        },
        workerBalance() {
            this.$root.axios.post('/payment/worker')
            .then(response => {
                const {data} = response

                if(data.error) {
                    return this.$root.$emit('noty', {
                        title: 'Ошибка',
                        text: data.message,
                        type: 'error'
                    })
                }

                this.$bvModal.hide('settings')
                this.$root.user.balance = data.balance
            })
        }
    },
}
</script>