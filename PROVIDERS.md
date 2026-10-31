# Licensed provider games (Aviator, JetX, Aviatrix, ...)

These games come from licensed studios (Spribe, SmartSoft Gaming, Aviatrix) or from an aggregator that carries them. They run on the provider's servers inside an iframe, and use the player's Mzizination balance through a **seamless wallet**: the provider calls our API for every bet and win.

## What you need first

1. A gambling licence (BCLB in Kenya). Providers won't sign with an unlicensed operator.
2. A contract with each studio or with one aggregator. They give you a **secret key**, an **operator ID**, a **launch URL format**, and their wallet API spec.

## Turning a provider on

Set its env vars (for example on Render). A provider shows up on `/licensed` only when both its secret and its launch URL are set:

```
SPRIBE_SECRET=...            SPRIBE_OPERATOR=...      SPRIBE_LAUNCH_URL=https://.../?token={token}&game={game}&operator={operator}&currency={currency}&lang={lang}&return_url={lobby}
SMARTSOFT_SECRET=...         SMARTSOFT_OPERATOR=...   SMARTSOFT_LAUNCH_URL=...
AVIATRIX_SECRET=...          AVIATRIX_OPERATOR=...    AVIATRIX_LAUNCH_URL=...
PROVIDER_CURRENCY=KES
```

Give the provider this wallet URL: `https://<your-domain>/provider/<spribe|smartsoft|aviatrix>/wallet`

## Wallet API (`POST /provider/{provider}/wallet`)

The JSON body is signed with header `X-Signature: hex(HMAC-SHA256(raw body, secret))`.

| action | fields | effect |
|---|---|---|
| `balance` | `token` | Returns the balance |
| `bet` | `token, transaction_id, round_id, game, amount` | Takes the stake |
| `win` | `token, transaction_id, round_id, game, amount` | Pays out (always paid, even if the player has since self-excluded) |
| `rollback` | `token, transaction_id, reference_transaction_id` | Refunds a bet or reverses a win |

Response: `{status: "ok", balance, currency}` or `{status: "error", code}`. The error codes are `INVALID_SIGNATURE`, `INVALID_TOKEN`, `INSUFFICIENT_FUNDS`, `LIMIT_REACHED` (self-exclusion, KYC or a daily stake limit), `DUPLICATE_TRANSACTION`, `TRANSACTION_ROLLED_BACK`, `PLAYER_BLOCKED` and `INVALID_AMOUNT`.

Every transaction id is processed once. Retries return the original result, and a rollback that arrives before its bet blocks that bet.

**Each provider's real API uses its own field names, signature scheme and error codes.** When you sign a contract, a small adapter maps their format onto `App\Services\ProviderWallet`. The money logic stays the same.
