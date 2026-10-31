<template>
    <div class="content cards col-12 ml-auto mr-auto p-0">
        <div class="rg">
            <h2>Responsible Gaming</h2>
            <p class="rg-sub">Gambling should be fun. Set limits, take a break, and verify your age here. 18+ only.</p>

            <div v-if="!$root.user" class="rg-card">Please log in to manage your settings.</div>

            <template v-else-if="s">
                <div v-if="msg" class="rg-alert" :class="msgType">{{ msg }}</div>

                <!-- KYC -->
                <div class="rg-card">
                    <h3>Age & identity verification <span class="rg-badge" :class="'k-' + s.kyc_status">{{ kycLabel }}</span></h3>
                    <template v-if="s.kyc_status === 'none' || s.kyc_status === 'rejected'">
                        <p v-if="s.kyc_status === 'rejected'" class="rg-note">Your last submission was rejected. Please check your details and try again.</p>
                        <p class="rg-note">You must be 18 or older. We need this before you can deposit or play, and withdrawals need a verified ID.</p>
                        <div class="rg-row">
                            <label>Date of birth<input type="date" v-model="kyc.date_of_birth" class="rg-input"></label>
                            <label>National ID / passport no.<input type="text" v-model="kyc.id_number" class="rg-input" maxlength="20"></label>
                        </div>
                        <button class="rg-btn" @click="post('/profile/rg/kyc', kyc, 'Details submitted. You can now play; withdrawals unlock once we verify your ID.')">Submit</button>
                    </template>
                    <p v-else class="rg-note">Date of birth: {{ s.date_of_birth }} &middot; ID: {{ s.id_number_masked }}</p>
                </div>

                <!-- Limits -->
                <div class="rg-card">
                    <h3>Daily limits</h3>
                    <p class="rg-note">Lowering a limit applies immediately. Raising or removing one takes effect after 24 hours. Leave blank for no limit.</p>
                    <div class="rg-row">
                        <label>Deposit limit per day
                            <input type="number" min="0" v-model="limits.deposit" class="rg-input" placeholder="No limit">
                            <small>Deposited today: {{ fmt(s.deposited_today) }}</small>
                        </label>
                        <label>Stake limit per day
                            <input type="number" min="0" v-model="limits.stake" class="rg-input" placeholder="No limit">
                            <small>Staked today: {{ fmt(s.staked_today) }}</small>
                        </label>
                    </div>
                    <p v-if="s.pending" class="rg-note">
                        Pending change from {{ new Date(s.pending.effective_at).toLocaleString() }}:
                        <span v-if="s.pending.deposit !== null">deposit &rarr; {{ s.pending.deposit === 'none' ? 'no limit' : fmt(s.pending.deposit) }}</span>
                        <span v-if="s.pending.stake !== null">stake &rarr; {{ s.pending.stake === 'none' ? 'no limit' : fmt(s.pending.stake) }}</span>
                    </p>
                    <button class="rg-btn" @click="saveLimits">Save limits</button>
                </div>

                <!-- Self-exclusion -->
                <div class="rg-card">
                    <h3>Take a break (self-exclusion)</h3>
                    <p v-if="s.self_excluded_until" class="rg-note"><b>You are excluded until {{ new Date(s.self_excluded_until).toLocaleString() }}.</b> You can still withdraw your balance.</p>
                    <template v-else>
                        <p class="rg-note">You won't be able to deposit or play for the chosen period. This can't be undone early.</p>
                        <div class="rg-row">
                            <select v-model="period" class="rg-input">
                                <option v-for="o in s.exclusion_options" :key="o" :value="String(o)">{{ o === 'permanent' ? 'Permanently' : o + (o === 1 ? ' day' : ' days') }}</option>
                            </select>
                            <button class="rg-btn rg-danger" @click="exclude">Exclude me</button>
                        </div>
                    </template>
                </div>

                <div class="rg-card rg-help">
                    Need to talk to someone? Contact the Betting Control and Licensing Board (BCLB) or a counselling service such as Gamblers Anonymous Kenya.
                </div>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return { s: null, msg: null, msgType: 'ok', kyc: { date_of_birth: '', id_number: '' }, limits: { deposit: '', stake: '' }, period: '7' }
    },
    computed: {
        kycLabel() {
            return { none: 'Not submitted', pending: 'Under review', verified: 'Verified', rejected: 'Rejected' }[this.s.kyc_status] || this.s.kyc_status
        }
    },
    mounted() { this.load() },
    watch: {
        '$root.user'(u, old) { if (u && !old) this.load() }
    },
    methods: {
        load() {
            if (!this.$root.user) return
            this.$root.axios.post('/profile/rg/init').then(({ data }) => this.apply(data))
        },
        apply(data) {
            this.s = data
            this.limits.deposit = data.daily_deposit_limit === null ? '' : data.daily_deposit_limit
            this.limits.stake = data.daily_stake_limit === null ? '' : data.daily_stake_limit
        },
        post(url, body, okMsg) {
            return this.$root.axios.post(url, body).then(({ data }) => {
                if (data.error) { this.msgType = 'err'; this.msg = data.message; return }
                this.apply(data)
                this.msgType = 'ok'
                this.msg = okMsg
            })
        },
        saveLimits() {
            const v = x => (x === '' || x === null) ? null : x
            this.post('/profile/rg/limits', { deposit: v(this.limits.deposit), stake: v(this.limits.stake) }, 'Limits saved.')
        },
        exclude() {
            const label = this.period === 'permanent' ? 'permanently' : `for ${this.period} day(s)`
            if (!confirm(`Exclude yourself ${label}? You will not be able to deposit or play, and this cannot be undone early.`)) return
            this.post('/profile/rg/self-exclude', { period: this.period }, 'Self-exclusion is active.')
        },
        fmt(n) { return Number(n || 0).toFixed(2) }
    }
}
</script>

<style scoped>
.rg { padding: 20px; max-width: 820px; }
.rg h2 { font-weight: 700; margin: 0; }
.rg-sub { opacity: .65; }
.rg-card { border: 1px solid rgba(127,127,127,.2); border-radius: 10px; padding: 16px; margin-bottom: 14px; }
.rg-card h3 { font-size: 17px; font-weight: 700; }
.rg-note { opacity: .75; font-size: 13px; }
.rg-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 10px; }
.rg-row label { display: flex; flex-direction: column; font-size: 13px; flex: 1; min-width: 200px; }
.rg-row small { opacity: .6; margin-top: 4px; }
.rg-input { padding: 8px; border-radius: 8px; border: 1px solid rgba(127,127,127,.3); background: transparent; color: inherit; margin-top: 4px; }
.rg-input option { color: #000; }
.rg-btn { padding: 9px 18px; border: 0; border-radius: 8px; background: #2f80ed; color: #fff; font-weight: 600; cursor: pointer; }
.rg-danger { background: #eb5757; }
.rg-badge { font-size: 11px; padding: 3px 8px; border-radius: 10px; margin-left: 8px; vertical-align: middle; background: rgba(127,127,127,.25); }
.k-verified { background: rgba(39,174,96,.25); color: #27ae60; }
.k-pending { background: rgba(242,201,76,.25); color: #f2c94c; }
.k-rejected { background: rgba(235,87,87,.25); color: #eb5757; }
.rg-alert { padding: 10px; border-radius: 8px; margin-bottom: 12px; }
.rg-alert.ok { background: rgba(39,174,96,.15); color: #27ae60; }
.rg-alert.err { background: rgba(235,87,87,.15); color: #eb5757; }
.rg-help { font-size: 13px; opacity: .8; }
</style>
