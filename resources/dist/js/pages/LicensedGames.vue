<template>
    <div class="content cards col-12 ml-auto mr-auto p-0">
        <div class="lg">
            <template v-if="!$route.params.game">
                <h2>Licensed Games</h2>
                <p class="lg-sub">Games from licensed studios, played with your Mzizination balance.</p>
                <div v-if="loaded && !games.length" class="lg-empty">
                    New games are coming soon. In the meantime, try our crash games:
                    <router-link to="/crash/flight">Safari Flight</router-link>,
                    <router-link to="/crash/rocket">Mzizi Rocket</router-link> and
                    <router-link to="/crash/jet">Savanna Jet</router-link>.
                </div>
                <div class="lg-grid">
                    <router-link v-for="g in games" :key="g.provider + g.game" :to="`/licensed/${g.provider}/${g.game}`" class="lg-card">
                        <div class="lg-title">{{ g.title }}</div>
                        <div class="lg-prov">{{ g.provider_name }}</div>
                    </router-link>
                </div>
            </template>
            <template v-else>
                <div class="lg-bar">
                    <router-link to="/licensed">&larr; All games</router-link>
                </div>
                <div v-if="error" class="lg-error">{{ error }}</div>
                <iframe v-else-if="url" :src="url" class="lg-frame" allow="autoplay; fullscreen"></iframe>
                <div v-else class="lg-empty">Loading game...</div>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    data() { return { games: [], loaded: false, url: null, error: null } },
    watch: {
        '$route.params.game'() { this.load() },
        '$root.user'(u, old) { if (u && !old) this.load() }
    },
    mounted() { this.load() },
    methods: {
        load() {
            this.url = null; this.error = null
            if (!this.$route.params.game) {
                this.$root.axios.post('/provider/catalog').then(({ data }) => { this.games = data.games || []; this.loaded = true })
                return
            }
            if (!this.$root.user) { this.error = 'Please log in to play'; return }
            this.$root.axios.post('/provider/launch', { provider: this.$route.params.provider, game: this.$route.params.game })
                .then(({ data }) => { if (data.error) this.error = data.message; else this.url = data.url })
        }
    }
}
</script>

<style scoped>
.lg { padding: 20px; }
.lg h2 { margin: 0; font-weight: 700; }
.lg-sub { opacity: .65; }
.lg-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin-top: 14px; }
.lg-card { display: block; padding: 28px 16px; border-radius: 12px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff !important; text-decoration: none; }
.lg-title { font-size: 20px; font-weight: 800; }
.lg-prov { opacity: .8; font-size: 12px; }
.lg-empty { opacity: .8; padding: 14px 0; }
.lg-bar { margin-bottom: 10px; }
.lg-frame { width: 100%; height: 72vh; border: 0; border-radius: 12px; background: #000; }
.lg-error { padding: 10px; border-radius: 8px; background: rgba(235,87,87,.15); color: #eb5757; }
</style>
