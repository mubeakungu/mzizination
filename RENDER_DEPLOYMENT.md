# Mzizination - Render Deployment Guide

Complete guide to deploy Mzizination on Render.com

## Prerequisites

- ✅ Render account (https://render.com)
- ✅ GitHub repository with this code
- ✅ M-Pesa Daraja credentials
- ✅ Paystack account credentials
- ✅ Domain name (optional, can use Render subdomain)

---

## Step 1: Prepare GitHub Repository

```bash
# Create a new GitHub repository
git init
git add .
git commit -m "Initial Mzizination setup for Render"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/mzizination.git
git push -u origin main
```

---

## Step 2: Create Render.com Account

1. Go to https://render.com
2. Sign up with GitHub
3. Connect your GitHub account
4. Create a new project

---

## Step 3: Deploy on Render

### Option A: Using render.yaml (Recommended)

1. Go to **Render Dashboard**
2. Click **New** → **Web Service**
3. Select **Build and deploy from a Git repository**
4. Connect your GitHub account
5. Select the `mzizination` repository
6. Click **Create Web Service**

Render will automatically detect `render.yaml` and deploy!

### Option B: Manual Configuration

1. Go to **Render Dashboard**
2. Click **New** → **Web Service**
3. Connect GitHub repository
4. Configure:
   - **Name**: mzizination
   - **Environment**: PHP
   - **Region**: Frankfurt (eu-central-1) or choose closest to Kenya
   - **Branch**: main
   - **Build Command**: `sh scripts/build.sh`
   - **Start Command**: `sh scripts/start.sh`

---

## Step 4: Configure Database

### Create PostgreSQL Database

1. In Render Dashboard, click **New** → **PostgreSQL**
2. Configure:
   - **Name**: mzizination-db
   - **Database**: defaultdb
   - **User**: avnadmin
   - **Region**: Same as web service
   - **Plan**: Standard (production recommended)

3. Copy the internal database URL
4. Add to Web Service environment variables

---

## Step 5: Set Environment Variables

In Render Dashboard → Web Service → **Environment**:

```
APP_NAME=Mzizination
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_KEY_HERE  # Will be generated during deployment
APP_URL=https://your-app.onrender.com

# Database (from PostgreSQL service)
DB_CONNECTION=pgsql
DB_HOST=<from postgres service>
DB_PORT=5432
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=<from postgres service>
DB_SSLMODE=require

# Cache (use arrays - no external services on free tier)
CACHE_DRIVER=array
SESSION_DRIVER=cookie
QUEUE_DRIVER=sync

# M-Pesa Daraja
MPESA_SANDBOX=true  # Change to false for production
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=174379
MPESA_PASSKEY=your_passkey
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_CALLBACK_URL=https://your-app.onrender.com/api/payments/mpesa/callback

# Paystack Backup
PAYSTACK_PUBLIC_KEY=pk_live_your_key
PAYSTACK_SECRET_KEY=sk_live_your_key

# Other Settings
APP_TIMEZONE=Africa/Nairobi
APP_LOCALE=en
MPESA_SANDBOX=true
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

---

## Step 6: Deploy

1. Click **Deploy** button in Render Dashboard
2. Monitor deployment logs
3. Once deployed, go to `https://your-app.onrender.com`

---

## Step 7: Verify Deployment

```bash
# Check if API is running
curl https://your-app.onrender.com/api/health

# Check payment methods
curl https://your-app.onrender.com/api/payments/methods

# Check Laravel is working
curl https://your-app.onrender.com/
```

---

## Step 8: Configure Custom Domain (Optional)

1. Go to **Web Service Settings** → **Custom Domain**
2. Enter your domain (e.g., mzizination.co.ke)
3. Add CNAME record to your DNS provider:
   ```
   CNAME: your-app.onrender.com
   ```
4. Wait for DNS propagation (5-30 minutes)

---

## Step 9: Update M-Pesa Callback URLs

Update these URLs in Safaricom Daraja Portal with your Render domain:

- **Deposit Callback**: https://your-domain.com/api/payments/mpesa/callback
- **Timeout Callback**: https://your-domain.com/api/payments/mpesa/timeout
- **Withdrawal Callback**: https://your-domain.com/api/withdrawals/mpesa/callback

---

## Step 10: Test M-Pesa Integration

### Using M-Pesa Sandbox

1. **Get Test Credentials**:
   - Go to https://developer.safaricom.co.ke/
   - Create test account
   - Copy credentials to `.env`

2. **Test STK Push** (Deposit):
   ```bash
   curl -X POST https://your-app.onrender.com/api/payments/create \
     -H "Content-Type: application/json" \
     -d '{
       "amount": 100,
       "phone": "254712345678",
       "method": "mpesa"
     }'
   ```

3. **Check Logs**:
   - Go to Render Dashboard → Logs
   - Look for STK push confirmation

---

## Step 11: Scale for Production

### Upgrade Plan if Needed

- **Starter** ($7/month): Good for testing
- **Standard** ($12/month): Better for production
- **Plus** ($25/month): High traffic

Click **Instance Type** in Render Dashboard to upgrade.

### Enable Auto-Scaling

```yaml
# In render.yaml
minNumInstances: 1
maxNumInstances: 3
```

### Add Redis for Caching

Uncomment Redis section in `render.yaml`:

```yaml
- type: redis
  name: mzizination-redis
  plan: starter
```

---

## Troubleshooting

### Build Fails

**Problem**: `composer install` fails  
**Solution**: Check PHP version in render.yaml (8.2 recommended)

```yaml
phpVersion: 8.2
```

### Database Connection Error

**Problem**: `SQLSTATE[08006]`  
**Solution**: 
1. Verify DB credentials in environment variables
2. Check database URL includes `?sslmode=require`
3. Restart web service

### M-Pesa Callback Not Received

**Problem**: Webhooks not triggering  
**Solution**:
1. Check callback URL is publicly accessible (not localhost)
2. Verify URL in M-Pesa dashboard
3. Check Render logs: `tail -f logs`

### Cannot Access API

**Problem**: 502 Bad Gateway  
**Solution**:
1. Check build logs in Render Dashboard
2. Verify all environment variables are set
3. Restart service: Settings → Restart

### SSL/TLS Error

**Problem**: HTTPS not working  
**Solution**:
1. Render provides free SSL automatically
2. Wait 5 minutes after domain connection
3. Clear browser cache
4. Test: `curl -v https://your-domain.com`

---

## Monitoring

### Enable Logs

In Render Dashboard:
1. Go to Web Service
2. Click **Logs** tab
3. View real-time application logs

### Set Up Email Alerts

1. Settings → Notifications
2. Add email for deployment failures
3. Configure alert thresholds

### Monitor Performance

1. Go to **Metrics** tab
2. View CPU, Memory, Disk usage
3. Check response times

---

## Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong database password
- [ ] Enable SSL/TLS (automatic on Render)
- [ ] Set `SESSION_SECURE_COOKIES=true`
- [ ] Set `SESSION_HTTP_ONLY=true`
- [ ] Keep M-Pesa credentials in environment only
- [ ] Never commit `.env` file to Git
- [ ] Use separate credentials for sandbox vs production
- [ ] Enable IP whitelisting for admin endpoints (if possible)
- [ ] Regular database backups (automatic on Render paid plans)

---

## Backup & Recovery

### Automatic Backups

Render provides automatic backups on **Standard** plan and above.

### Manual Backup

```bash
# Backup database locally
pg_dump $DATABASE_URL > backup.sql

# Backup uploaded files (if using S3)
aws s3 sync s3://your-bucket ./backups/
```

---

## Cost Estimation

| Service | Free | Starter | Standard |
|---------|------|---------|----------|
| Web Service | ❌ | $7/mo | $12/mo |
| PostgreSQL | ❌ | $7/mo | $15/mo |
| **Total** | - | **$14/mo** | **$27/mo** |

---

## Performance Tips

1. **Cache Configuration**:
   - Use array cache for Render's free tier
   - Consider adding Redis for production

2. **Database Optimization**:
   - Add indexes to frequently queried columns
   - Use connection pooling

3. **Asset Compression**:
   - Enable gzip compression
   - Minify CSS/JS

4. **Content Delivery**:
   - Use Render's CDN for static files
   - Cache headers for media

---

## Next Steps

1. ✅ Deploy to Render
2. ✅ Configure M-Pesa sandbox
3. ✅ Test deposit/withdrawal flow
4. ✅ Set up monitoring
5. ✅ Configure custom domain
6. ✅ Get BCLB license
7. ✅ Switch to production M-Pesa
8. ✅ Launch to users

---

## Support

- **Render Docs**: https://render.com/docs
- **Laravel Docs**: https://laravel.com/docs
- **M-Pesa Docs**: https://developer.safaricom.co.ke/docs
- **Paystack Docs**: https://paystack.com/docs/

---

**Last Updated**: August 2026  
**Version**: 1.0
