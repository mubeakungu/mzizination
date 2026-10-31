<template>
    <div class="content cards col-12 ml-auto mr-auto p-0">
        <div class="crash" :class="'th-' + theme">
            <div class="cr-head">
                <h2>{{ meta.title }}</h2>
                <div class="cr-themes">
                    <router-link v-for="(m, key) in themes" :key="key" :to="'/crash/' + key" class="cr-theme" :class="{ active: key === theme }">{{ m.title }}</router-link>
                </div>
            </div>

            <div class="cr-history">
                <span v-for="h in history" :key="h.id" class="cr-chip" :class="chipClass(h.crash_point)" @click="fair = h">{{ fmt(h.crash_point) }}x</span>
            </div>

            <div class="cr-stage" ref="stage">
                <canvas ref="canvas"></canvas>
                <div class="cr-mult" :class="{ crashed: phase === 'crashed', cashed: phase === 'cashed' }">
                    <template v-if="phase === 'idle'">{{ meta.idle }}</template>
                    <template v-else>{{ fmt(multiplier) }}x</template>
                    <div class="cr-sub" v-if="phase === 'crashed'">{{ meta.crashText }}</div>
                    <div class="cr-sub" v-if="phase === 'cashed'">Cashed out at {{ fmt(game.cashout_multiplier) }}x: +{{ fmt(game.win) }}</div>
                </div>
            </div>

            <div class="cr-controls">
                <label>Bet
                    <input type="number" v-model="amount" :min="minBet" class="cr-input" :disabled="phase === 'running'">
                    <span class="cr-quick">
                        <button @click="amount = Math.max(minBet, amount / 2)" :disabled="phase === 'running'">½</button>
                        <button @click="amount = amount * 2" :disabled="phase === 'running'">2×</button>
                    </span>
                </label>
                <label>Auto cash-out (optional)
                    <input type="number" v-model="auto" min="1.01" step="0.01" placeholder="e.g. 2.00" class="cr-input" :disabled="phase === 'running'">
                </label>
                <button v-if="phase !== 'running'" class="cr-btn cr-bet" :disabled="busy" @click="start">Bet {{ fmt(amount) }}</button>
                <button v-else class="cr-btn cr-cash" :disabled="busy" @click="cashout">Cash out {{ fmt(amount * multiplier) }}</button>
            </div>
            <div v-if="error" class="cr-error">{{ error }}</div>

            <div class="cr-fair" v-if="fair">
                <b>Provably fair, round #{{ fair.id }}</b>
                <div>Server seed hash (shown before the round): <code>{{ fair.server_seed_hash }}</code></div>
                <div v-if="fair.server_seed">Server seed (revealed): <code>{{ fair.server_seed }}</code></div>
                <div>Client seed: <code>{{ fair.client_seed }}</code> &middot; Nonce: <code>{{ fair.nonce }}</code></div>
                <div v-if="fair.server_seed">
                    Result: <b>{{ fmt(fair.crash_point) }}x</b>
                    <button class="cr-verify" @click="verify(fair)">Verify in browser</button>
                    <span v-if="verified !== null" :class="verified ? 'ok' : 'bad'">{{ verified ? '✔ matches' : '✖ does not match' }}</span>
                </div>
                <small>crash = floor((1 − {{ houseEdge }}) × 2^52 ÷ (2^52 − h) × 100) ÷ 100, where h is the first 52 bits of HMAC-SHA256(server seed, "client seed:nonce").</small>
            </div>
            <p class="cr-rtp">RTP {{ ((1 - houseEdge) * 100).toFixed(0) }}%. Play responsibly, 18+.</p>
        </div>
    </div>
</template>

<script>
const THEMES = {
    flight: { title: 'Safari Flight', idle: 'Ready for take-off', crashText: 'Flew off!', sky: ['#ff9a3c', '#3b1d5c'], line: '#ffd166' },
    rocket: { title: 'Mzizi Rocket', idle: 'Ready for launch', crashText: 'Boom!', sky: ['#1a1440', '#05030f'], line: '#a78bfa' },
    jet: { title: 'Savanna Jet', idle: 'Engines ready', crashText: 'Gone!', sky: ['#5ad1c8', '#0b3954'], line: '#e0fbfc' }
}

export default {
    data() {
        return {
            themes: THEMES, amount: 100, auto: '', phase: 'idle', game: null, multiplier: 1, history: [],
            offset: 0, growth: 0.00006, houseEdge: 0.03, maxMult: 1000, minBet: 10, busy: false, error: null,
            fair: null, verified: null, raf: null, poll: null, stars: []
        }
    },
    computed: {
        theme() { return THEMES[this.$route.params.theme] ? this.$route.params.theme : 'rocket' },
        meta() { return THEMES[this.theme] }
    },
    watch: {
        '$root.user'(u, old) { if (u && !old) this.init() },
        theme() { this.draw() }
    },
    mounted() {
        this.stars = Array.from({ length: 60 }, () => [Math.random(), Math.random(), Math.random() * 1.5 + 0.3])
        window.addEventListener('resize', this.draw)
        this.init()
        this.draw()
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.draw)
        cancelAnimationFrame(this.raf)
        clearInterval(this.poll)
    },
    methods: {
        fmt(n) { return Number(n || 0).toFixed(2) },
        chipClass(x) { return x >= 10 ? 'hi' : (x >= 2 ? 'mid' : 'lo') },
        api(url, body) { return this.$root.axios.post(url, body).then(r => r.data) },
        init() {
            if (!this.$root.user) return
            this.api('/crash/init').then(d => {
                if (d.error) return
                this.growth = d.growth; this.houseEdge = d.house_edge; this.maxMult = d.max_multiplier || 1000; this.minBet = d.min_bet
                this.history = d.history
                this.offset = d.server_time_ms - Date.now()
                if (d.active) this.run(d.active)
            })
        },
        start() {
            if (!this.$root.user) { this.error = 'Please log in to play'; return }
            this.error = null; this.busy = true; this.verified = null
            this.api('/crash/start', { amount: this.amount, auto_cashout: this.auto || null, theme: this.theme }).then(d => {
                this.busy = false
                if (d.error) { this.error = d.message; return }
                this.$root.user.balance = d.balance
                this.offset = d.server_time_ms - Date.now()
                this.run(d.game)
            }).catch(() => { this.busy = false })
        },
        run(game) {
            this.game = game; this.phase = 'running'; this.fair = null
            const tick = () => {
                if (this.phase !== 'running') return
                const t = Date.now() + this.offset - game.started_at_ms
                this.multiplier = Math.max(1, Math.floor(Math.exp(this.growth * t) * 100) / 100)
                this.draw()
                this.raf = requestAnimationFrame(tick)
            }
            tick()
            clearInterval(this.poll)
            this.poll = setInterval(() => this.check(), 400)
        },
        check() {
            if (!this.game) return
            this.api('/crash/status', { id: this.game.id }).then(d => { if (!d.error) this.finish(d) })
        },
        cashout() {
            this.busy = true
            this.api('/crash/cashout', { id: this.game.id }).then(d => { this.busy = false; if (!d.error) this.finish(d) })
                .catch(() => { this.busy = false })
        },
        finish(d) {
            if (d.game.status === 'active') return
            clearInterval(this.poll); cancelAnimationFrame(this.raf)
            this.game = d.game
            this.phase = d.game.status
            this.multiplier = d.game.status === 'cashed' ? d.game.cashout_multiplier : d.game.crash_point
            if (d.game.status === 'cashed') {
                // keep the curve going visually to show where it crashed
                this.multiplier = d.game.crash_point
            }
            if (d.balance !== undefined) this.$root.user.balance = d.balance
            this.history = [d.game, ...this.history.filter(h => h.id !== d.game.id)].slice(0, 20)
            this.fair = d.game
            this.draw()
        },
        async verify(g) {
            const enc = new TextEncoder()
            const key = await crypto.subtle.importKey('raw', enc.encode(g.server_seed), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign'])
            const sig = new Uint8Array(await crypto.subtle.sign('HMAC', key, enc.encode(g.client_seed + ':' + g.nonce)))
            const hex = Array.from(sig).map(b => b.toString(16).padStart(2, '0')).join('')
            const h = parseInt(hex.slice(0, 13), 16), e = Math.pow(2, 52)
            let point = Math.floor((1 - this.houseEdge) * e / (e - h) * 100) / 100
            point = Math.max(1, Math.min(point, this.maxMult))
            const seedHash = Array.from(new Uint8Array(await crypto.subtle.digest('SHA-256', enc.encode(g.server_seed)))).map(b => b.toString(16).padStart(2, '0')).join('')
            this.verified = Math.abs(point - g.crash_point) < 0.001 && seedHash === g.server_seed_hash
        },
        draw() {
            const c = this.$refs.canvas, stage = this.$refs.stage
            if (!c || !stage) return
            const W = stage.clientWidth, H = stage.clientHeight, dpr = window.devicePixelRatio || 1
            c.width = W * dpr; c.height = H * dpr; c.style.width = W + 'px'; c.style.height = H + 'px'
            const ctx = c.getContext('2d'); ctx.scale(dpr, dpr)
            const m = this.meta
            const g = ctx.createLinearGradient(0, H, 0, 0); g.addColorStop(0, m.sky[0]); g.addColorStop(1, m.sky[1])
            ctx.fillStyle = g; ctx.fillRect(0, 0, W, H)
            this.drawScenery(ctx, W, H)
            if (this.phase === 'idle') return

            // curve: x = time, y = multiplier, axes stretch as the round goes on
            const pad = 40, mult = Math.max(1.0001, this.multiplier)
            const tMax = Math.log(mult) / this.growth, xMax = Math.max(tMax, 8000), yMax = Math.max(mult, 2)
            const X = t => pad + (W - 2 * pad) * (t / xMax)
            const Y = v => H - pad - (H - 2 * pad) * ((v - 1) / (yMax - 1))
            ctx.lineWidth = 4; ctx.strokeStyle = m.line; ctx.beginPath()
            for (let i = 0; i <= 60; i++) { const t = tMax * i / 60, v = Math.exp(this.growth * t); i ? ctx.lineTo(X(t), Y(v)) : ctx.moveTo(X(t), Y(v)) }
            ctx.stroke()
            ctx.lineTo(X(tMax), H - pad); ctx.lineTo(X(0), H - pad); ctx.closePath()
            ctx.fillStyle = m.line + '33'; ctx.fill()

            const x = X(tMax), y = Y(mult)
            const t2 = tMax * 0.97, angle = Math.atan2(Y(Math.exp(this.growth * t2)) - y, X(t2) - x) + Math.PI
            if (this.phase === 'crashed') this.drawBurst(ctx, x, y)
            else this.drawCraft(ctx, x, y, angle)
        },
        drawScenery(ctx, W, H) {
            if (this.theme === 'rocket') {
                ctx.fillStyle = '#ffffff'
                this.stars.forEach(([sx, sy, r]) => { ctx.globalAlpha = 0.4 + r / 3; ctx.beginPath(); ctx.arc(sx * W, sy * H, r, 0, 7); ctx.fill() })
                ctx.globalAlpha = 1
            } else if (this.theme === 'flight') {
                ctx.fillStyle = 'rgba(255,230,150,.85)'; ctx.beginPath(); ctx.arc(W * 0.8, H * 0.75, 42, 0, 7); ctx.fill()
                ctx.fillStyle = '#2b1a12'
                ctx.beginPath(); ctx.moveTo(0, H)
                for (let i = 0; i <= 10; i++) ctx.lineTo(W * i / 10, H - 18 - 10 * Math.sin(i * 1.7))
                ctx.lineTo(W, H); ctx.fill()
                // acacia tree silhouette
                const tx = W * 0.62, ty = H - 22
                ctx.fillRect(tx - 2, ty - 34, 4, 34)
                ctx.beginPath(); ctx.ellipse(tx, ty - 38, 34, 9, 0, 0, 7); ctx.fill()
            } else {
                ctx.fillStyle = 'rgba(255,255,255,.35)'
                ;[[0.2, 0.25, 40], [0.55, 0.15, 55], [0.8, 0.35, 35], [0.35, 0.55, 30]].forEach(([cx, cy, r]) => {
                    ctx.beginPath(); ctx.arc(cx * W, cy * H, r, 0, 7); ctx.arc(cx * W + r, cy * H + 6, r * 0.8, 0, 7); ctx.arc(cx * W - r, cy * H + 8, r * 0.7, 0, 7); ctx.fill()
                })
            }
        },
        drawCraft(ctx, x, y, angle) {
            ctx.save(); ctx.translate(x, y); ctx.rotate(angle)
            if (this.theme === 'rocket') {
                ctx.fillStyle = '#f8f9fa'; ctx.beginPath(); ctx.moveTo(26, 0); ctx.quadraticCurveTo(10, -11, -14, -8); ctx.lineTo(-14, 8); ctx.quadraticCurveTo(10, 11, 26, 0); ctx.fill()
                ctx.fillStyle = '#ef476f'; ctx.beginPath(); ctx.moveTo(-8, -8); ctx.lineTo(-18, -16); ctx.lineTo(-14, -6); ctx.fill(); ctx.beginPath(); ctx.moveTo(-8, 8); ctx.lineTo(-18, 16); ctx.lineTo(-14, 6); ctx.fill()
                ctx.fillStyle = '#4cc9f0'; ctx.beginPath(); ctx.arc(8, 0, 4, 0, 7); ctx.fill()
                ctx.fillStyle = '#ffb703'; ctx.beginPath(); ctx.moveTo(-14, -5); ctx.lineTo(-28 - Math.random() * 8, 0); ctx.lineTo(-14, 5); ctx.fill()
            } else if (this.theme === 'flight') {
                // side-view propeller plane
                ctx.fillStyle = '#d62828'
                ctx.beginPath(); ctx.ellipse(0, 0, 22, 6, 0, 0, 7); ctx.fill()
                ctx.beginPath(); ctx.moveTo(-14, -2); ctx.lineTo(-24, -14); ctx.lineTo(-18, -14); ctx.lineTo(-8, -3); ctx.fill()
                ctx.fillStyle = '#f77f00'; ctx.beginPath(); ctx.ellipse(2, 3, 11, 3.5, 0.15, 0, 7); ctx.fill()
                ctx.fillStyle = '#bde0fe'; ctx.beginPath(); ctx.ellipse(9, -3, 5, 3, 0, 0, 7); ctx.fill()
                ctx.strokeStyle = '#333'; ctx.lineWidth = 2; ctx.beginPath(); ctx.moveTo(23, -7 + Math.random() * 3); ctx.lineTo(23, 7 - Math.random() * 3); ctx.stroke()
            } else {
                ctx.fillStyle = '#e9ecef'
                ctx.beginPath(); ctx.moveTo(28, 0); ctx.lineTo(-16, -5); ctx.lineTo(-16, 5); ctx.closePath(); ctx.fill()
                ctx.fillStyle = '#0b3954'; ctx.beginPath(); ctx.moveTo(6, 0); ctx.lineTo(-10, -20); ctx.lineTo(-14, -20); ctx.lineTo(-6, 0); ctx.lineTo(-14, 20); ctx.lineTo(-10, 20); ctx.fill()
                ctx.fillStyle = '#ff6b35'; ctx.beginPath(); ctx.moveTo(-16, -3); ctx.lineTo(-30 - Math.random() * 10, 0); ctx.lineTo(-16, 3); ctx.fill()
            }
            ctx.restore()
        },
        drawBurst(ctx, x, y) {
            ctx.save(); ctx.translate(x, y)
            for (let i = 0; i < 12; i++) {
                const a = i * Math.PI / 6, r = 18 + (i % 2) * 10
                ctx.strokeStyle = i % 2 ? '#ffd166' : '#ef476f'; ctx.lineWidth = 3
                ctx.beginPath(); ctx.moveTo(Math.cos(a) * 6, Math.sin(a) * 6); ctx.lineTo(Math.cos(a) * r, Math.sin(a) * r); ctx.stroke()
            }
            ctx.restore()
        }
    }
}
</script>

<style scoped>
.crash { padding: 20px; }
.cr-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.cr-head h2 { margin: 0; font-weight: 700; }
.cr-themes { display: flex; gap: 6px; }
.cr-theme { padding: 6px 12px; border-radius: 16px; border: 1px solid rgba(127,127,127,.3); color: inherit; font-size: 13px; text-decoration: none; }
.cr-theme.active { background: #2f80ed; border-color: #2f80ed; color: #fff !important; }
.cr-history { display: flex; gap: 6px; margin: 12px 0; overflow-x: auto; min-height: 26px; }
.cr-chip { padding: 3px 9px; border-radius: 12px; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; }
.cr-chip.lo { background: rgba(127,127,127,.2); }
.cr-chip.mid { background: rgba(47,128,237,.25); color: #56a0ff; }
.cr-chip.hi { background: rgba(155,81,224,.3); color: #c38bff; }
.cr-stage { position: relative; height: 340px; border-radius: 12px; overflow: hidden; }
.cr-stage canvas { display: block; }
.cr-mult { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 56px; font-weight: 800; color: #fff; text-shadow: 0 2px 12px rgba(0,0,0,.5); pointer-events: none; }
.cr-mult.crashed { color: #ff5d73; }
.cr-mult.cashed { color: #6ee7a8; }
.cr-sub { font-size: 18px; font-weight: 600; }
.cr-controls { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-top: 14px; }
.cr-controls label { display: flex; flex-direction: column; font-size: 13px; flex: 1; min-width: 170px; position: relative; }
.cr-input { padding: 10px; border-radius: 8px; border: 1px solid rgba(127,127,127,.3); background: transparent; color: inherit; margin-top: 4px; }
.cr-quick { position: absolute; right: 6px; bottom: 6px; display: flex; gap: 4px; }
.cr-quick button { border: 0; border-radius: 6px; padding: 3px 8px; background: rgba(127,127,127,.25); color: inherit; cursor: pointer; }
.cr-btn { flex: 1; min-width: 200px; padding: 14px; border: 0; border-radius: 10px; font-size: 17px; font-weight: 800; color: #fff; cursor: pointer; }
.cr-bet { background: #27ae60; }
.cr-cash { background: #f2994a; }
.cr-btn:disabled { opacity: .6; }
.cr-error { margin-top: 10px; padding: 8px 10px; border-radius: 8px; background: rgba(235,87,87,.15); color: #eb5757; }
.cr-fair { margin-top: 14px; font-size: 12px; border: 1px solid rgba(127,127,127,.2); border-radius: 10px; padding: 12px; word-break: break-all; }
.cr-fair div { margin: 3px 0; }
.cr-fair code { font-size: 11px; }
.cr-verify { margin-left: 8px; border: 0; border-radius: 6px; padding: 3px 8px; background: #2f80ed; color: #fff; cursor: pointer; }
.ok { color: #27ae60; margin-left: 6px; } .bad { color: #eb5757; margin-left: 6px; }
.cr-rtp { margin-top: 10px; font-size: 12px; opacity: .6; }
</style>
