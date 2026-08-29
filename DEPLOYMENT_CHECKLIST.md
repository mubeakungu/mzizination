# Mzizination Render Deployment Checklist

## Pre-Deployment (Complete Before Starting)

### Credentials & Accounts
- [ ] GitHub account created
- [ ] Render.com account created
- [ ] M-Pesa Daraja account with credentials:
  - [ ] Consumer Key obtained
  - [ ] Consumer Secret obtained
  - [ ] Shortcode obtained
  - [ ] Passkey obtained
- [ ] Paystack account with credentials:
  - [ ] Public Key obtained
  - [ ] Secret Key obtained

### Repository Setup
- [ ] Code pushed to GitHub repository
- [ ] .env file excluded from Git (verify in .gitignore)
- [ ] No sensitive data in repository
- [ ] README.md in root directory

### Local Testing (Optional)
- [ ] Ran `composer install` locally
- [ ] Ran `php artisan migrate` locally
- [ ] Tested M-Pesa sandbox integration locally
- [ ] All tests passing

---

## Render Setup Phase 1: Infrastructure

### Create PostgreSQL Database
- [ ] Login to Render.com
- [ ] Click **New** → **PostgreSQL**
- [ ] Configure:
  - [ ] Name: `mzizination-db`
  - [ ] Database name: `defaultdb`
  - [ ] PostgreSQL version: 15
  - [ ] Region: Frankfurt (eu-central-1) or closest to Kenya
  - [ ] Plan: **Starter** (recommended for initial launch)
- [ ] Copy **Internal Database URL**: `postgres://...`
- [ ] Copy **Host**: `<hostname>`
- [ ] Copy **User**: `avnadmin`
- [ ] Copy **Password**: `<password>`
- [ ] Copy **Database**: `defaultdb`
- [ ] Wait for database to be "Available" (2-3 minutes)

### Create Web Service
- [ ] Click **New** → **Web Service**
- [ ] Select GitHub repository: `mzizination`
- [ ] Connect repository access if prompted
- [ ] Configure:
  - [ ] Name: `mzizination-api`
  - [ ] Environment: **PHP**
  - [ ] PHP Version: **8.2**
  - [ ] Region: Same as database
  - [ ] Branch: `main`
  - [ ] Auto-deploy: ✅ Enabled
- [ ] Click **Create Web Service**

---

## Render Setup Phase 2: Configuration

### Build Configuration
- [ ] Build Command: `sh scripts/build.sh`
- [ ] Start Command: `sh scripts/start.sh`
- [ ] Auto-deploy from main branch: ✅ Enabled

### Environment Variables - Core
- [ ] `APP_NAME` = `Mzizination`
- [ ] `APP_ENV` = `production`
- [ ] `APP_DEBUG` = `false`
- [ ] `APP_KEY` = `base64:auto-generate` (will be set during build)
- [ ] `APP_URL` = `https://your-app.onrender.com` (update after deployment)
- [ ] `APP_TIMEZONE` = `Africa/Nairobi`

### Environment Variables - Database
- [ ] `DB_CONNECTION` = `pgsql`
- [ ] `DB_HOST` = `<from PostgreSQL service>`
- [ ] `DB_PORT` = `5432`
- [ ] `DB_DATABASE` = `defaultdb`
- [ ] `DB_USERNAME` = `avnadmin`
- [ ] `DB_PASSWORD` = `<from PostgreSQL service>`
- [ ] `DB_SSLMODE` = `require`

### Environment Variables - Cache & Queue
- [ ] `CACHE_DRIVER` = `array`
- [ ] `SESSION_DRIVER` = `cookie`
- [ ] `QUEUE_DRIVER` = `sync`
- [ ] `LOG_CHANNEL` = `stack`
- [ ] `LOG_LEVEL` = `debug`

### Environment Variables - M-Pesa
- [ ] `MPESA_SANDBOX` = `true` (sandbox first!)
- [ ] `MPESA_CONSUMER_KEY` = `<your key>`
- [ ] `MPESA_CONSUMER_SECRET` = `<your secret>`
- [ ] `MPESA_SHORTCODE` = `174379` (or your shortcode)
- [ ] `MPESA_PASSKEY` = `<your passkey>`
- [ ] `MPESA_INITIATOR_NAME` = `testapi`
- [ ] `MPESA_INITIATOR_PASSWORD` = `<your password>`
- [ ] `MPESA_CALLBACK_URL` = `https://your-app.onrender.com/api/payments/mpesa/callback`
- [ ] `MPESA_TIMEOUT_URL` = `https://your-app.onrender.com/api/payments/mpesa/timeout`
- [ ] `MPESA_B2C_CALLBACK_URL` = `https://your-app.onrender.com/api/withdrawals/mpesa/callback`

### Environment Variables - Paystack
- [ ] `PAYSTACK_PUBLIC_KEY` = `pk_test_...` (sandbox)
- [ ] `PAYSTACK_SECRET_KEY` = `sk_test_...` (sandbox)

### Environment Variables - Payment Settings
- [ ] `WITHDRAWAL_MIN_AMOUNT` = `100`
- [ ] `WITHDRAWAL_MAX_AMOUNT` = `500000`
- [ ] `WITHDRAWAL_AUTO_APPROVE_LIMIT` = `10000`
- [ ] `DEPOSIT_MIN_AMOUNT` = `10`
- [ ] `DEPOSIT_MAX_AMOUNT` = `70000`

### Environment Variables - Security
- [ ] `APP_SECURE_COOKIES` = `true`
- [ ] `SESSION_SECURE_COOKIES` = `true`
- [ ] `SESSION_HTTP_ONLY` = `true`

---

## Render Deployment Phase 3: Deploy

### Trigger Deployment
- [ ] Go to Web Service → **Deploy**
- [ ] Click **Manual Deploy** or wait for auto-deploy from GitHub
- [ ] Monitor build logs in **Logs** tab
- [ ] Wait for "Your service is live" message

### Build Success Checklist
- [ ] ✅ `composer install` completed
- [ ] ✅ `php artisan key:generate` completed
- [ ] ✅ Assets compiled (if any)
- [ ] ✅ No build errors in logs

### Service Status
- [ ] Status shows: **Live**
- [ ] URL format: `https://mzizination-xxxxx.onrender.com`
- [ ] Service is receiving requests (check logs)

---

## Post-Deployment Verification

### Basic Connectivity
- [ ] Run: `curl https://your-app.onrender.com/api/health`
- [ ] Response: `{"status":"ok"}`
- [ ] Check response time (should be < 1 second)

### Database Connection
- [ ] Check logs for migration completion
- [ ] Look for: `✓ Database migrations completed`
- [ ] No `SQLSTATE` errors in logs

### API Endpoints
- [ ] `GET /api/health` → 200 OK
- [ ] `GET /api/payments/methods` → 200 OK
- [ ] `GET /api/withdrawals/methods` → 200 OK

### Environment
- [ ] `php artisan config:cache` executed
- [ ] `php artisan route:cache` executed
- [ ] Logs accessible via Render dashboard

---

## Sandbox Testing Phase

### M-Pesa STK Push Test
- [ ] Open: `https://your-app.onrender.com/deposit`
- [ ] Amount: `100` KSH
- [ ] Phone: `254712345678` (sandbox test number)
- [ ] Method: **M-Pesa**
- [ ] Click **Proceed**
- [ ] Check logs for: `STK Push initiated`
- [ ] Verify no errors: `Callback received`

### M-Pesa Withdrawal Test
- [ ] Open: `https://your-app.onrender.com/withdraw`
- [ ] Amount: `100` KSH
- [ ] Phone: `254712345678`
- [ ] Click **Withdraw**
- [ ] Check logs for: `B2C transfer initiated`
- [ ] Verify: `Withdrawal completed`

### Paystack Test
- [ ] Open: `https://your-app.onrender.com/deposit`
- [ ] Amount: `100` KSH
- [ ] Method: **Paystack**
- [ ] Use test card: `4242 4242 4242 4242`
- [ ] Expiry: Any future date
- [ ] CVC: Any 3 digits
- [ ] Verify: Deposit recorded

### Database Verification
- [ ] Payment records created in database
- [ ] Withdrawal records created
- [ ] Transaction history populated
- [ ] Balances updated correctly

---

## Production Readiness Phase

### Upgrade Infrastructure (if needed)
- [ ] Analyze traffic patterns
- [ ] Upgrade to **Standard** plan if needed
- [ ] Enable auto-scaling (set min/max instances)
- [ ] Add Redis for caching (if needed)

### Custom Domain Setup
- [ ] Purchase domain (e.g., mzizination.co.ke)
- [ ] Go to Web Service → **Settings** → **Custom Domain**
- [ ] Add domain name
- [ ] Add CNAME record to DNS:
  - [ ] Type: `CNAME`
  - [ ] Name: `@` or `mzizination`
  - [ ] Value: `your-app.onrender.com`
- [ ] Wait for DNS propagation (5-30 minutes)
- [ ] Verify: `curl https://mzizination.co.ke`

### Update M-Pesa Credentials
- [ ] Go to Safaricom Daraja Portal
- [ ] Update Callback URLs:
  - [ ] Deposit: `https://your-domain.com/api/payments/mpesa/callback`
  - [ ] Timeout: `https://your-domain.com/api/payments/mpesa/timeout`
  - [ ] Withdrawal: `https://your-domain.com/api/withdrawals/mpesa/callback`
- [ ] Get Production Credentials:
  - [ ] Consumer Key (production)
  - [ ] Consumer Secret (production)
- [ ] Update `MPESA_SANDBOX=false` in Render environment

### Update Paystack Credentials
- [ ] Log into Paystack Live Dashboard
- [ ] Get Production Keys:
  - [ ] Public Key (live)
  - [ ] Secret Key (live)
- [ ] Update `PAYSTACK_PUBLIC_KEY` and `PAYSTACK_SECRET_KEY`

### Enable Production Flags
- [ ] `APP_DEBUG` = `false`
- [ ] `MPESA_SANDBOX` = `false`
- [ ] `APP_ENV` = `production`
- [ ] `LOG_LEVEL` = `warning` (reduce noise)

### Compliance & Legal
- [ ] [ ] Apply for BCLB License (Betting Control & Licensing Board)
- [ ] [ ] Register with KRA (Kenya Revenue Authority)
- [ ] [ ] Terms & Conditions updated for Kenya
- [ ] [ ] Privacy Policy for Kenyan requirements
- [ ] [ ] Age verification (18+) implemented
- [ ] [ ] Responsible gaming warnings displayed

---

## Monitoring & Alerts

### Set Up Monitoring
- [ ] Go to **Metrics** tab in Web Service
- [ ] Monitor:
  - [ ] CPU usage
  - [ ] Memory usage
  - [ ] Disk I/O
  - [ ] Response times
  - [ ] Error rates

### Configure Alerts
- [ ] Go to **Notifications**
- [ ] Add email for deployment failures
- [ ] Add email for critical errors
- [ ] Set alert thresholds

### Enable Logs
- [ ] Access **Logs** tab regularly
- [ ] Set up log aggregation (optional)
- [ ] Monitor for:
  - [ ] M-Pesa callback failures
  - [ ] Database connection errors
  - [ ] Authentication issues
  - [ ] Payment processing errors

---

## Ongoing Maintenance

### Daily
- [ ] Check Render logs for errors
- [ ] Monitor M-Pesa callbacks
- [ ] Verify payment processing

### Weekly
- [ ] Review error logs
- [ ] Check API response times
- [ ] Verify database backup completion

### Monthly
- [ ] Review traffic patterns
- [ ] Update dependencies: `composer update`
- [ ] Security patches check
- [ ] Database optimization

### Before Major Holidays
- [ ] Capacity testing
- [ ] Backup verification
- [ ] Performance testing with high load
- [ ] Communicate maintenance windows

---

## Rollback Plan

### If Deployment Fails
- [ ] Go to **Settings** → **Deployment Logs**
- [ ] Review build errors
- [ ] Fix issues locally
- [ ] Push corrected code to GitHub
- [ ] Render will auto-deploy again

### If Production Issue Discovered
- [ ] Revert to previous version: `git revert`
- [ ] Push to GitHub
- [ ] Monitor new deployment
- [ ] Fix issues in parallel environment
- [ ] Re-deploy when fixed

### If Database Issue
- [ ] Check recent migrations
- [ ] Backup current data if possible
- [ ] Roll back migration: `php artisan migrate:rollback`
- [ ] Fix and re-run migration

---

## Final Sign-Off

- [ ] All tests passing on production
- [ ] M-Pesa integration working (production)
- [ ] Paystack integration working
- [ ] Withdrawals working correctly
- [ ] Admin dashboard functional
- [ ] Monitoring alerts configured
- [ ] Backup strategy in place
- [ ] Security audit completed
- [ ] Legal/compliance requirements met
- [ ] Ready for user launch

---

## Support Resources

- **Render Docs**: https://render.com/docs
- **Laravel Docs**: https://laravel.com/docs/11.x
- **M-Pesa API**: https://developer.safaricom.co.ke/docs
- **Paystack API**: https://paystack.com/docs/api/
- **PostgreSQL Help**: https://www.postgresql.org/docs/

---

**Deployment Date**: _______________  
**Deployed By**: _______________  
**Sign-Off**: _______________  

---

*Version 1.0 - August 2026*
