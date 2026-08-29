# Mzizination on Render - Quick Start (5 Minutes)

## 📋 Checklist Before Deploying

- [ ] GitHub account
- [ ] Render.com account  
- [ ] M-Pesa Daraja credentials
- [ ] Paystack account

---

## 🚀 Deploy in 5 Steps

### 1. Push to GitHub (1 min)

```bash
git clone https://github.com/your-username/mzizination.git
cd mzizination
git add .
git commit -m "Ready for Render deployment"
git push origin main
```

### 2. Create Render Account (1 min)

Visit: https://render.com/signup

### 3. Create PostgreSQL Database (1 min)

In Render Dashboard:
- Click **New** → **PostgreSQL**
- Name: `mzizination-db`
- Region: Frankfurt (or nearest to Kenya)
- Copy the **Internal Database URL**

### 4. Deploy Web Service (1 min)

In Render Dashboard:
- Click **New** → **Web Service**
- Connect GitHub repository
- Build Command: `sh scripts/build.sh`
- Start Command: `sh scripts/start.sh`

### 5. Set Environment Variables (1 min)

Add these to Render environment variables:

```
APP_NAME=Mzizination
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-APP.onrender.com

# Database - Copy from PostgreSQL service
DB_CONNECTION=pgsql
DB_HOST=<internal-db-url>
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=<password>
DB_SSLMODE=require

# Cache settings
CACHE_DRIVER=array
SESSION_DRIVER=cookie

# M-Pesa
MPESA_SANDBOX=true
MPESA_CONSUMER_KEY=your_key_here
MPESA_CONSUMER_SECRET=your_secret_here
MPESA_SHORTCODE=174379
MPESA_PASSKEY=your_passkey_here

# Paystack
PAYSTACK_PUBLIC_KEY=pk_test_your_key
PAYSTACK_SECRET_KEY=sk_test_your_key
```

---

## ✅ Verify Deployment

Once deployment completes:

```bash
# Test API
curl https://YOUR-APP.onrender.com/api/health

# Should return: {"status":"ok"}
```

---

## 🎮 Test M-Pesa Deposit

1. Open: https://YOUR-APP.onrender.com/deposit
2. Enter amount: 100 KSH
3. Enter test phone: 254712345678
4. Click "Pay with M-Pesa"
5. Check Render logs for callback

---

## 📚 Full Documentation

- **Render Guide**: See `RENDER_DEPLOYMENT.md` for detailed instructions
- **Implementation**: See `IMPLEMENTATION_CHECKLIST.md`
- **About Mzizination**: See `MZIZINATION_README.md`

---

## 🆘 Troubleshooting

| Problem | Solution |
|---------|----------|
| Build failed | Check PHP version (8.2), composer.json exists |
| Database error | Verify DB URL includes `?sslmode=require` |
| M-Pesa not working | Check `MPESA_CALLBACK_URL` is set correctly |
| Cannot access site | Wait 2-3 minutes for deployment, clear cache |

---

## 🎯 Next Steps

After successful deployment:

1. [ ] Set up custom domain
2. [ ] Update M-Pesa callback URLs in Safaricom portal
3. [ ] Test withdrawal flow
4. [ ] Set up monitoring/alerts
5. [ ] Apply for BCLB license (for production)
6. [ ] Switch M-Pesa from sandbox to production

---

## 💰 Pricing

**Monthly Cost on Render**:
- Web Service (Starter): $7
- PostgreSQL (Starter): $7
- **Total: ~$14/month**

Upgrades available for higher traffic.

---

**Need Help?**  
See `RENDER_DEPLOYMENT.md` for complete guide or check Render documentation.
