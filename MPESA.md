# M-Pesa deposits and withdrawals

Deposits use **Lipa na M-Pesa Online (STK push)**: the player types their number and amount, their phone asks for the M-Pesa PIN, and their balance is credited when Safaricom confirms the payment. Withdrawals use **B2C**: the player requests a payout, an admin approves it in **Admin → Withdraws**, and the money is sent to their phone.

All amounts are whole Kenyan shillings, and the site balance is credited 1:1 in KES.

## How it works

| Step | What happens |
|---|---|
| Player taps **Pay with M-Pesa** | `POST /mpesa/deposit` creates a pending payment and sends the STK push. Responsible-gambling deposit limits and self-exclusion are checked first. |
| Player enters their PIN | Safaricom calls `POST /mpesa/callback/{MPESA_CALLBACK_SECRET}/stk`. |
| Before crediting | The site **asks Safaricom directly** (STK query) whether that payment succeeded. Daraja callbacks aren't signed, so a callback alone never credits a balance. |
| Callback lost or late | The cashier polls `POST /mpesa/deposit/status`, which runs the same STK query after 20 seconds. |
| Player requests a withdrawal | The balance is held and the request waits for an admin (status 0). |
| Admin presses **Send** | A B2C payment is sent (status 3). A double click can't pay twice. |
| Safaricom reports the result | `POST /mpesa/callback/{secret}/b2c/result` marks it paid (1), or **refunds** the balance if it failed (2). |
| Safaricom times out | The withdrawal stays at status 3 with a note. Check the M-Pesa statement before paying it again by hand. |

Every deposit is credited once only. The payment row is locked, and duplicate callbacks are ignored. Referral commissions and the 2x wagering requirement work the same way they do for the other payment methods.

## Setup

1. Create an app at <https://developer.safaricom.co.ke> with **Lipa na M-Pesa Sandbox** and **M-Pesa Sandbox (B2C)**.
2. In Render, set these environment variables (`render.yaml` lists them all):

   | Variable | Sandbox value |
   |---|---|
   | `APP_URL` | your public https URL, e.g. `https://mzizination.onrender.com` |
   | `MPESA_SANDBOX` | `true` |
   | `MPESA_CONSUMER_KEY` / `MPESA_CONSUMER_SECRET` | from your Daraja app |
   | `MPESA_SHORTCODE` | `174379` |
   | `MPESA_PASSKEY` | the Lipa na M-Pesa passkey shown on the Daraja simulator |
   | `MPESA_CALLBACK_SECRET` | Render generates it; keep it secret |
   | `MPESA_B2C_SHORTCODE` | the B2C test shortcode from Daraja (e.g. `600xxx`) |
   | `MPESA_INITIATOR_NAME` / `MPESA_INITIATOR_PASSWORD` | the B2C test credentials from Daraja |
   | `MPESA_CERT` | the PEM text of Safaricom's **sandbox** certificate (or upload it to `storage/app/mpesa-certificate.pem`) |

3. Optional limits: `MPESA_MIN_DEPOSIT` (10), `MPESA_MAX_DEPOSIT` (150000), `MPESA_MIN_WITHDRAW` (50), `MPESA_MAX_WITHDRAW` (150000).
4. For a **till** instead of a paybill, set `MPESA_TRANSACTION_TYPE=CustomerBuyGoodsOnline`, `MPESA_SHORTCODE` to the store number and `MPESA_PARTY_B` to the till number.

## Going live

Live payments need a Safaricom paybill or till that is approved for STK push and B2C. That approval in turn needs your **BCLB licence**.

Once you are approved:
- Set `MPESA_SANDBOX=false`.
- Replace the keys with your production app's keys.
- Use the **production** certificate.

Set `MPESA_SANDBOX` and the keys together: sandbox keys don't work against the live API.

If the M-Pesa keys are missing, the cashier tells players that M-Pesa isn't available yet, and nothing breaks.
