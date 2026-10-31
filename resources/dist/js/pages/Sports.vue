<template>
    <div class="content cards col-12 ml-auto mr-auto p-0">
        <div class="sportsbook">
            <div class="sb-header">
                <h2>Sportsbook</h2>
                <span class="sb-sub">Live odds from The Odds API</span>
            </div>

            <div v-if="error" class="sb-alert sb-alert-error">{{ error }}</div>

            <div class="sb-tabs" v-if="sports.length">
                <button
                    v-for="s in sports" :key="s.key"
                    class="sb-tab" :class="{ active: s.key === sport }"
                    @click="selectSport(s.key)"
                >{{ s.title }}</button>
            </div>

            <div class="sb-layout">
                <div class="sb-events">
                    <div v-if="loading" class="sb-empty">Loading matches...</div>
                    <div v-else-if="sport && !events.length" class="sb-empty">No upcoming matches with odds right now.</div>

                    <div v-for="e in events" :key="e.id" class="sb-event">
                        <div class="sb-event-meta">{{ e.sport_title }} &middot; {{ formatTime(e.commence_time) }}</div>
                        <div class="sb-event-teams">{{ e.home_team }} <span>vs</span> {{ e.away_team }}</div>
                        <div class="sb-odds">
                            <button
                                v-for="o in orderedOutcomes(e)" :key="o.name"
                                class="sb-odd" :class="{ picked: slip && slip.event.id === e.id && slip.outcome.name === o.name }"
                                @click="pick(e, o)"
                            >
                                <span class="sb-odd-name">{{ labelFor(e, o.name) }}</span>
                                <span class="sb-odd-price">{{ o.price.toFixed(2) }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="sb-slip">
                    <h3>Bet slip</h3>
                    <div v-if="!slip" class="sb-empty">Tap a price to add a selection.</div>
                    <div v-else>
                        <div class="sb-slip-event">{{ slip.event.home_team }} vs {{ slip.event.away_team }}</div>
                        <div class="sb-slip-pick">{{ slip.outcome.name }} @ {{ slip.outcome.price.toFixed(2) }}</div>
                        <input class="sb-input" type="number" min="1" step="1" v-model="amount" placeholder="Stake">
                        <div class="sb-slip-return">Potential return: <b>{{ potential }}</b></div>
                        <button class="sb-place" :disabled="placing" @click="placeBet">{{ placing ? 'Placing...' : 'Place bet' }}</button>
                        <button class="sb-clear" @click="slip = null">Clear</button>
                    </div>
                    <div v-if="message" class="sb-alert" :class="messageType === 'error' ? 'sb-alert-error' : 'sb-alert-ok'">{{ message }}</div>

                    <h3 class="mt-4" v-if="$root.user">My bets</h3>
                    <div v-for="b in myBets" :key="b.id" class="sb-mybet" :class="'st-' + b.status">
                        <div>{{ b.home_team }} vs {{ b.away_team }}</div>
                        <div class="sb-mybet-line">
                            {{ b.selection }} @ {{ Number(b.odds).toFixed(2) }} &middot; {{ Number(b.amount).toFixed(2) }}
                            <span class="sb-status">{{ b.status }}</span>
                        </div>
                        <div v-if="b.result" class="sb-mybet-result">{{ b.result }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            sports: [],
            sport: null,
            events: [],
            loading: false,
            error: null,
            slip: null,
            amount: 100,
            placing: false,
            message: null,
            messageType: 'ok',
            myBets: []
        }
    },
    computed: {
        potential() {
            if (!this.slip) return '0.00'
            return ((Number(this.amount) || 0) * this.slip.outcome.price).toFixed(2)
        }
    },
    mounted() {
        this.loadSports()
        this.loadMyBets()
    },
    methods: {
        loadSports() {
            this.$root.axios.post('/sports/list').then(({ data }) => {
                if (data.error) { this.error = data.message; return }
                this.sports = data.sports
                if (this.sports.length) this.selectSport(this.sports[0].key)
                else this.error = 'No sports are in season right now.'
            })
        },
        selectSport(key) {
            this.sport = key
            this.loading = true
            this.events = []
            this.$root.axios.post('/sports/events', { sport: key }).then(({ data }) => {
                this.loading = false
                if (data.error) { this.error = data.message; return }
                this.events = data.events
            }).catch(() => { this.loading = false })
        },
        loadMyBets() {
            if (!this.$root.user) return
            this.$root.axios.post('/sports/my').then(({ data }) => {
                if (data.bets) this.myBets = data.bets
            })
        },
        orderedOutcomes(e) {
            const rank = n => n === e.home_team ? 0 : (n === 'Draw' ? 1 : 2)
            return [...e.outcomes].sort((a, b) => rank(a.name) - rank(b.name))
        },
        labelFor(e, name) {
            if (name === e.home_team) return '1'
            if (name === 'Draw') return 'X'
            if (name === e.away_team) return '2'
            return name
        },
        pick(event, outcome) {
            this.slip = { event, outcome }
            this.message = null
        },
        placeBet() {
            if (!this.$root.user) {
                this.messageType = 'error'
                this.message = 'Please log in to place a bet'
                return
            }
            this.placing = true
            this.$root.axios.post('/sports/bet', {
                sport: this.slip.event.sport_key,
                event_id: this.slip.event.id,
                selection: this.slip.outcome.name,
                amount: this.amount
            }).then(({ data }) => {
                this.placing = false
                if (data.error) {
                    this.messageType = 'error'
                    this.message = data.message
                    return
                }
                this.$root.user.balance = data.balance
                this.messageType = 'ok'
                this.message = `Bet placed: ${data.bet.selection} @ ${Number(data.bet.odds).toFixed(2)}`
                this.slip = null
                this.loadMyBets()
            }).catch(() => { this.placing = false })
        },
        formatTime(iso) {
            return new Date(iso).toLocaleString([], { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
        }
    }
}
</script>

<style scoped>
.sportsbook { padding: 20px; }
.sb-header h2 { margin: 0; font-weight: 700; }
.sb-sub { opacity: .6; font-size: 13px; }
.sb-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin: 16px 0; }
.sb-tab { border: 1px solid rgba(127,127,127,.3); background: transparent; color: inherit; padding: 6px 14px; border-radius: 20px; cursor: pointer; }
.sb-tab.active { background: #2f80ed; border-color: #2f80ed; color: #fff; }
.sb-layout { display: flex; gap: 20px; align-items: flex-start; }
.sb-events { flex: 1; min-width: 0; }
.sb-event { border: 1px solid rgba(127,127,127,.2); border-radius: 10px; padding: 12px 14px; margin-bottom: 10px; }
.sb-event-meta { font-size: 12px; opacity: .6; }
.sb-event-teams { font-weight: 600; margin: 4px 0 10px; }
.sb-event-teams span { opacity: .5; font-weight: 400; margin: 0 4px; }
.sb-odds { display: flex; gap: 8px; }
.sb-odd { flex: 1; display: flex; justify-content: space-between; border: 1px solid rgba(127,127,127,.3); background: rgba(127,127,127,.08); color: inherit; padding: 8px 10px; border-radius: 8px; cursor: pointer; }
.sb-odd.picked { background: #2f80ed; border-color: #2f80ed; color: #fff; }
.sb-odd-price { font-weight: 700; }
.sb-slip { width: 300px; flex-shrink: 0; border: 1px solid rgba(127,127,127,.2); border-radius: 10px; padding: 14px; position: sticky; top: 80px; }
.sb-slip h3 { font-size: 16px; font-weight: 700; }
.sb-slip-event { font-size: 13px; opacity: .7; }
.sb-slip-pick { font-weight: 600; margin: 4px 0 10px; }
.sb-input { width: 100%; padding: 8px; border-radius: 8px; border: 1px solid rgba(127,127,127,.3); background: transparent; color: inherit; }
.sb-slip-return { margin: 8px 0; font-size: 13px; }
.sb-place { width: 100%; padding: 10px; border: 0; border-radius: 8px; background: #27ae60; color: #fff; font-weight: 700; cursor: pointer; }
.sb-place:disabled { opacity: .6; }
.sb-clear { width: 100%; margin-top: 6px; padding: 6px; border: 0; background: transparent; color: inherit; opacity: .6; cursor: pointer; }
.sb-empty { opacity: .6; padding: 10px 0; }
.sb-alert { padding: 8px 10px; border-radius: 8px; margin: 10px 0; font-size: 13px; }
.sb-alert-error { background: rgba(235,87,87,.15); color: #eb5757; }
.sb-alert-ok { background: rgba(39,174,96,.15); color: #27ae60; }
.sb-mybet { border-left: 3px solid #999; padding: 6px 8px; margin-bottom: 8px; font-size: 13px; }
.sb-mybet.st-won { border-color: #27ae60; }
.sb-mybet.st-lost { border-color: #eb5757; }
.sb-mybet.st-void { border-color: #f2c94c; }
.sb-mybet-line { opacity: .8; }
.sb-mybet-result { opacity: .6; font-size: 12px; }
.sb-status { float: right; text-transform: uppercase; font-size: 11px; font-weight: 700; }
@media (max-width: 900px) { .sb-layout { flex-direction: column; } .sb-slip { width: 100%; position: static; } }
</style>
