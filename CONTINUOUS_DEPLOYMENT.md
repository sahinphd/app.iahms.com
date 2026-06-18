# 🔄 Laravel LMS Continuous Deployment (CD) Guide

This guide details how to set up an automated **Continuous Integration & Continuous Deployment (CI/CD)** pipeline for the Laravel LMS application. It includes a automated deployment script (`deploy.sh`), a GitHub Actions workflow, and step-by-step server instructions.

---

## 📋 Table of Contents
1. [Workflow Architecture](#1-workflow-architecture)
2. [The Deployment Script (`deploy.sh`)](#2-the-deployment-script-deploysh)
3. [GitHub Actions Workflow Configuration](#3-github-actions-workflow-configuration)
4. [Setting Up SSH Keys & Secrets](#4-setting-up-ssh-keys--secrets)
5. [Automating with Git Hooks (Alternative CD)](#5-automating-with-git-hooks-alternative-cd)
6. [Troubleshooting & Maintenance Commands](#6-troubleshooting--maintenance-commands)

---

## 1. Workflow Architecture

```mermaid
graph TD
    A[Local Code Push] -->|Push to main branch| B[GitHub Repository]
    B --> C[GitHub Actions runner]
    C -->|1. Setup environment| D[Run PHPUnit Tests]
    D -->|2. If Tests Pass| E[Trigger Deploy Step]
    E -->|3. SSH Connection| F[Production Server]
    F -->|4. Runs deploy.sh| G[Application Updated]
```

Every time code is pushed to the `main` (or `production`) branch:
1. GitHub Actions spins up an environment, installs dependencies, and runs the test suite (`php artisan test`).
2. If tests pass, GitHub Actions establishes a secure SSH connection to your production server.
3. The runner triggers the `deploy.sh` script located in your project root, which performs a zero-downtime update.

---

## 2. The Deployment Script (`deploy.sh`)

Create a script named `deploy.sh` at the root of your project `/var/www/app.iahms.com/deploy.sh` to automate the server-side deployment tasks.

### Code for `deploy.sh`:
```bash
#!/bin/bash
# Exit immediately if any command returns a non-zero exit status
set -e

echo "🚀 Starting Deployment..."

# 1. Activate Maintenance Mode
echo "🚧 Activating Maintenance Mode..."
php artisan down --message="The system is updating. Please try again in a moment." || true

# 2. Fetch the Latest Code
echo "📥 Pulling latest commits from Git..."
git pull origin main

# 3. Install Composer Dependencies (optimized for production)
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 4. Install NPM Dependencies & Build Production Assets
echo "🎨 Building frontend assets..."
if [ -f "package-lock.json" ]; then
    npm ci
else
    npm install
fi
npm run build

# 5. Run Database Migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 6. Optimize and Cache Configurations
echo "⚡ Caching configurations and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart Queue Workers (Required since queue driver is 'database')
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# 8. Deactivate Maintenance Mode
echo "✨ Deactivating Maintenance Mode..."
php artisan up

echo "✅ Deployment finished successfully!"
```

### Make the Script Executable:
Run this terminal command on the server to make the script executable:
```bash
chmod +x /var/www/app.iahms.com/deploy.sh
```

---

## 3. GitHub Actions Workflow Configuration

Create a file named `.github/workflows/deploy.yml` in your project repository to configure the automated CI/CD pipeline.

### File Path: `.github/workflows/deploy.yml`
```yaml
name: Deploy Application

on:
  push:
    branches: [ main ]

jobs:
  laravel-tests:
    name: Run Test Suite
    runs-on: ubuntu-latest

    steps:
    - name: Checkout Code
      uses: actions/checkout@v4

    - name: Setup PHP Environment
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, xml, ctype, iconv, pdo, sqlite, pdo_sqlite, bcmath
        coverage: none

    - name: Copy Env Example File
      run: php -r "file_exists('.env') || copy('.env.example', '.env');"

    - name: Install Dependencies (Composer)
      run: composer install --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist

    - name: Run Application Tests
      run: php artisan test

  deploy:
    name: Deploy to Production
    needs: laravel-tests
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'

    steps:
    - name: Execute Remote Deployment Script via SSH
      uses: appleboy/ssh-action@v1.0.3
      with:
        host: ${{ secrets.SSH_HOST }}
        username: ${{ secrets.SSH_USERNAME }}
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        port: ${{ secrets.SSH_PORT || 22 }}
        script: |
          cd /var/www/app.iahms.com
          ./deploy.sh
```

---

## 4. Setting Up SSH Keys & Secrets

For GitHub Actions to log into your server securely without hardcoding credentials, you must configure SSH key pairs.

### Step 1: Generate a Deploy Key Pair
On your local machine or server, run this command to generate an SSH key pair (without a passphrase):
```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy_key
```

### Step 2: Add Public Key to the Server
Append the public key content to the `/home/YOUR_USER/.ssh/authorized_keys` file on your production server:
```bash
cat ~/.ssh/github_deploy_key.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

### Step 3: Add Private Key & Secrets to GitHub Settings
1. Go to your GitHub repository.
2. Navigate to **Settings > Secrets and variables > Actions**.
3. Click **New repository secret** and add the following values:

| Secret Name | Value Example | Description |
|---|---|---|
| `SSH_HOST` | `203.0.113.5` or `app.iahms.com` | Your production server IP or hostname |
| `SSH_USERNAME` | `ubuntu` or `deploy` | The SSH user to log in with (must have write access to `/var/www/app.iahms.com`) |
| `SSH_PRIVATE_KEY` | `-----BEGIN OPENSSH PRIVATE KEY-----...` | Copy the entire contents of `~/.ssh/github_deploy_key` (including headers) |
| `SSH_PORT` | `22` (default) | Custom SSH port if your server does not use port 22 |

---

## 5. Automating with Git Hooks (Alternative CD)

If you are using a Git VPS (e.g. Webhook, self-hosted Git, Gitea) or prefer a direct server-side push trigger without GitHub Actions, you can configure a **Post-Receive Git Hook**.

### Step 1: Initialize Bare Git Repository on Server
Create a bare repository outside your public directory (e.g. at `/var/git/app.iahms.com.git`):
```bash
mkdir -p /var/git/app.iahms.com.git
cd /var/git/app.iahms.com.git
git init --bare
```

### Step 2: Create a `post-receive` Hook
Create a file at `/var/git/app.iahms.com.git/hooks/post-receive`:
```bash
nano /var/git/app.iahms.com.git/hooks/post-receive
```

Add the following deployment commands to check out the branch and run the script:
```bash
#!/bin/bash
TARGET="/var/www/app.iahms.com"
GIT_DIR="/var/git/app.iahms.com.git"

# Checkout code to target folder
git --work-tree=$TARGET --git-dir=$GIT_DIR checkout -f main

# Run deploy script
cd $TARGET
chmod +x deploy.sh
./deploy.sh
```

Make the hook executable:
```bash
chmod +x /var/git/app.iahms.com.git/hooks/post-receive
```

### Step 3: Add Git Remote Locally
Add the remote destination target on your local development computer:
```bash
git remote add production user@your-server-ip:/var/git/app.iahms.com.git
```

Now you can deploy by simply running:
```bash
git push production main
```

---

## 6. Troubleshooting & Maintenance Commands

Use these SSH terminal commands to debug the deployment or perform manual maintenance tasks:

### View Deployment logs
```bash
tail -n 50 /var/www/app.iahms.com/storage/logs/laravel.log
```

### Manually Rebuild Config Caches
```bash
cd /var/www/app.iahms.com
php artisan optimize:clear
php artisan optimize
```

### Check Queue Worker Status
```bash
sudo supervisorctl status
```

### Check Failed Jobs & Retry
```bash
# List failed queue jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Clear all failed jobs
php artisan queue:flush
```
