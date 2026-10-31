# Crash games

Three original themes share one engine: **Safari Flight** (`/crash/flight`), **Mzizi Rocket** (`/crash/rocket`) and **Savanna Jet** (`/crash/jet`). Only the artwork differs; the odds are identical.

## How a round works

1. The player bets, optionally with an auto cash-out target. The server generates a random server seed and shows its SHA-256 hash before the round starts.
2. The multiplier grows from 1.00x as `e^(0.00006 × ms)` on the **server clock** (about 2x at 11.6 s and 10x at 38 s).
3. **Cash out**: the server works out the multiplier from its own clock and pays `stake × multiplier`. The client never sends a multiplier.
4. If the multiplier reaches the crash point first, the stake is lost. Auto cash-out pays at the target if the target is below the crash point. This also works if the player closed the page: `crash:settle` runs every minute.
5. When the round ends, the server seed is revealed. The player can check the result with **Verify in browser**.

## Fairness maths

```
h     = first 52 bits of HMAC-SHA256(key = server_seed, message = client_seed + ":" + nonce)
crash = floor( (1 - edge) × 2^52 / (2^52 - h) × 100 ) / 100     (min 1.00, max CRASH_MAX_MULTIPLIER)
```

`P(crash ≥ x) = (1 − edge) / x`, so the RTP is `1 − edge` whatever target a player chooses. There is no "antiminus" or other outcome manipulation. Results depend only on the seeds.

## Settings

| Env var | Default | |
|---|---|---|
| `CRASH_HOUSE_EDGE` | `0.03` | 97% RTP |
| `CRASH_MAX_MULTIPLIER` | `1000` | Cap on the crash point |
| `CRASH_MAX_WIN` | `0` | Cap on a single payout (0 = none) |
| `CRASH_MIN_BET` / `CRASH_MAX_BET` | `10` / `100000` | |

Bets go through the responsible-gambling middleware (`rg:bet`), so KYC, self-exclusion and daily stake limits apply.
