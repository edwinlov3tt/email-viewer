# Railway Deployment

## Overview

| | |
|---|---|
| **Platform** | Railway |
| **Project Name** | `devoted-nature` |
| **Service Name** | `email-viewer` |
| **Production URL** | https://email-viewer.up.railway.app/ |
| **Purpose** | Production hosting for Flask application |
| **Documentation** | https://docs.railway.app/ |
| **Dashboard** | https://railway.app/dashboard |
| **Last Updated** | December 2025 |

## Configuration

### Environment Variables

| Variable | Purpose | Required |
|----------|---------|----------|
| `PORT` | Server port (auto-set by Railway) | Auto |
| `FLASK_ENV` | Set to `production` | Yes |

### Deployment Files

#### Procfile
```
web: gunicorn app:app --bind 0.0.0.0:$PORT
```

#### runtime.txt
```
python-3.13.7
```

#### requirements.txt
```
Flask==3.0.0
Flask-CORS==4.0.0
extract-msg==0.48.7
beautifulsoup4==4.12.2
lxml==5.1.0
gunicorn==21.2.0
```

## Deployment

### Initial Setup

1. **Connect GitHub Repository**
   ```bash
   # Or use Railway CLI
   railway login
   railway init
   railway link
   ```

2. **Set Environment Variables**
   - Go to Railway Dashboard
   - Select your project
   - Go to Variables tab
   - Add: `FLASK_ENV=production`

3. **Deploy**
   ```bash
   # Via CLI
   railway up

   # Or push to GitHub (auto-deploy)
   git push origin main
   ```

### Via GitHub (Recommended)

Railway auto-deploys when you push to the connected branch:

```bash
git add .
git commit -m "Deploy update"
git push origin main
```

### Via Railway CLI

```bash
# Login
railway login

# Initialize (first time)
railway init

# Deploy
railway up

# View logs
railway logs
```

## Monitoring

### View Logs
```bash
railway logs
```

Or in the Railway Dashboard:
- Project → Deployments → Select deployment → Logs

### Health Check
```bash
curl https://your-app.up.railway.app/health
```

Expected response:
```json
{"status": "healthy", "service": "email-viewer"}
```

## Common Issues

### Build Failures
- Check `requirements.txt` for typos
- Ensure `runtime.txt` specifies valid Python version
- Check build logs in Railway dashboard

### Import Errors
- Verify all dependencies in `requirements.txt`
- Check Python version compatibility

### 404 Errors
- Ensure `Procfile` is in root directory
- Check that Flask routes are defined correctly
- Verify `gunicorn` is installed

### Memory Issues
- Large email files may cause memory spikes
- Consider upgrading Railway plan if needed
- In-memory storage (`email_storage`) may need database for scale

## Costs

| Plan | Cost | Included |
|------|------|----------|
| Hobby | $5/month | 512MB RAM, $5 credits |
| Pro | $20/month | More resources |

Free tier available with limitations.

## URL

After deployment, your app is available at:
- `https://your-project.up.railway.app`

Custom domains can be configured in Railway Dashboard.

## Local Development

To mimic production locally:

```bash
# Install gunicorn
pip install gunicorn

# Run with gunicorn
gunicorn app:app --bind 0.0.0.0:5000

# Or use Flask's dev server
python app.py
```

## Repository

- **GitHub**: https://github.com/edwinlov3tt/email-viewer
- **Main Branch**: `main`
