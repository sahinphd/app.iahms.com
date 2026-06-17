# 🚀 Laravel LMS Deployment & Server Setup Guide

This guide details the complete process of deploying the Laravel LMS application on a production server (Ubuntu/Debian, VPS, or shared hosting). It covers server configuration, database migrations, seeding, key generation, and Google Cloud Storage (GCS) integration.

---

## 📋 Table of Contents
1. [Prerequisites & Server Requirements](#1-prerequisites--server-requirements)
2. [Step 1: Clone the Repository](#step-1-clone-the-repository)
3. [Step 2: Install Composer Dependencies](#step-2-install-composer-dependencies)
4. [Step 3: Configure Environment File (.env)](#step-3-configure-environment-file-env)
5. [Step 4: Generate Application Key](#step-4-generate-application-key)
6. [Step 5: Database Migrations & Seeding](#step-5-database-migrations--seeding)
7. [Step 6: Storage Link Creation](#step-6-storage-link-creation)
8. [Step 7: Compiling Frontend Assets (Vite)](#step-7-compiling-frontend-assets-vite)
9. [Step 8: Set Folder Permissions](#step-8-set-folder-permissions)
10. [Step 9: Nginx or Apache Web Server Configuration](#step-9-nginx-or-apache-web-server-configuration)
11. [Step 10: Google Cloud Storage Setup](#step-10-google-cloud-storage-setup)
12. [Step 11: Production Optimization Tasks](#step-11-production-optimization-tasks)
13. [Step 12: Queue & Scheduler Configuration](#step-12-queue--scheduler-configuration)

---

## 1. Prerequisites & Server Requirements

Ensure your server meets the following requirements:
* **PHP:** >= 8.2 with extensions: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo`, `session`, `tokenizer`, `xml`, `xmlwriter`
* **Composer:** >= 2.0
* **Database:** MySQL/MariaDB (or SQLite)
* **Web Server:** Nginx or Apache
* **Node.js & NPM:** For compiling frontend assets
* **Git:** Installed on the server

---

## Step 1: Clone the Repository

SSH into your server, navigate to your web root (usually `/var/www/`), and clone the repository:

```bash
cd /var/www
git clone <your-repository-url> app.iahms.com
cd app.iahms.com
```

---

## Step 2: Install Composer Dependencies

Run composer installation without development dependencies for production speed:

```bash
composer install --no-dev --optimize-autoloader
```

---

## Step 3: Configure Environment File (.env)

Copy the example environment file:

```bash
cp .env.example .env
```

Open the `.env` file using a terminal editor (e.g. `nano` or `vim`) and update the key variables:

```bash
nano .env
```

Configure these critical variables:

```ini
APP_NAME="IAHMS LMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.iahms.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Session & Cache Drivers
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

*(Note: Google Cloud Storage configurations are managed securely directly in the LMS Admin Settings dashboard, which is stored in the database settings table. However, you can also keep the default variables in `.env` empty or set them as backups).*

---

## Step 4: Generate Application Key

Generate the cryptographic key for encrypting user sessions and sensitive data:

```bash
php artisan key:generate
```

---

## Step 5: Database Migrations & Seeding

Run the database migrations and seed the initial seed database (default classes, users, permissions, and theme settings):

```bash
# Runs all table creation migrations and applies the seeders
php artisan migrate --seed --force
```

> [!IMPORTANT]
> Always use the `--force` flag in production. If you have already migrated and want to rebuild (warning: deletes all data), use `php artisan migrate:fresh --seed --force`.

### 👥 Default Seeded Accounts
After running the seeder, the following login credentials will be active:

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@lms.com` | `password` |
| **Teacher** | `teacher@lms.com` | `password` |
| **Teacher (Sain)** | `sain@lms.com` | `password` |
| **Student** | `student@lms.com` | `password` |

---

## Step 6: Storage Link Creation

Create a symbolic link from `public/storage` to `storage/app/public` so that uploaded thumbnails and files are accessible publicly:

```bash
php artisan storage:link
```

---

## Step 7: Compiling Frontend Assets (Vite)

Compile the Blade frontend and Tailwind CSS assets:

```bash
# Install Node modules
npm install

# Build production assets using Vite
npm run build
```

---

## Step 8: Set Folder Permissions

For security and functionality, the web server user (usually `www-data` on Ubuntu) must own and have write access to the `storage/` and `bootstrap/cache/` directories:

```bash
# Set ownership to the web server user
sudo chown -R www-data:www-data /var/www/app.iahms.com
sudo find /var/www/app.iahms.com -type f -exec chmod 644 {} \;
sudo find /var/www/app.iahms.com -type d -exec chmod 755 {} \;

# Give read-write permissions to storage and bootstrap cache
sudo chmod -R 775 /var/www/app.iahms.com/storage
sudo chmod -R 775 /var/www/app.iahms.com/bootstrap/cache
```

---

## Step 9: Nginx or Apache Web Server Configuration

Ensure your web server points to the `/var/www/app.iahms.com/public` directory (not the project root).

### Option A: Nginx Configuration
Create a server block file (e.g., `/etc/nginx/sites-available/app.iahms.com`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name app.iahms.com;
    root /var/www/app.iahms.com/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; # Adjust PHP version sock as needed
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Enable the site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/app.iahms.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Option B: Apache / Shared Hosting (Root `.htaccess` Redirect)
If you are deploying on a shared hosting environment (e.g. cPanel, Hostinger, Namecheap) or an Apache server where you **cannot change the web root directory** to point to the `public/` folder, you can use a root-level `.htaccess` file.

Create a file named `.htaccess` in the **project root folder** (e.g. `/home/username/public_html/` or `/var/www/app.iahms.com/`):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Prevent directory listing for safety
    Options -Indexes

    # Avoid loops by ignoring requests that are already rewritten to public/
    RewriteCond %{REQUEST_URI} !^/public/

    # Rewrite all requests to the public/ directory
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

This will seamlessly forward all traffic to the Laravel `public/` directory without exposing the private root files (such as `.env` and `app/`).

---

## Step 10: Google Cloud Storage Setup

The LMS utilizes Google Cloud Storage to securely store lecture videos and study materials, delivering them directly to enrolled students using secure, short-lived signed URLs.

### 1. Create a Bucket in GCP Console
1. Go to the [Google Cloud Storage Console](https://console.cloud.google.com/storage).
2. Click **Create Bucket**.
3. Set a unique name (e.g. `lms-private-lectures`).
4. Keep the storage class as Standard (or Nearline for cost optimization).
5. **CRITICAL:** Ensure **"Prevent public access"** is checked. Do NOT grant allUsers access.

### 2. Generate GCP Service Account Credentials
1. Navigate to **IAM & Admin > Service Accounts**.
2. Click **Create Service Account**.
3. Name it (e.g., `lms-storage-manager`).
4. Grant the Service Account the **Storage Object Admin** role (or **Storage Object Creator** & **Storage Object Viewer** roles) on your bucket.
5. Once created, click on the service account, go to the **Keys** tab, and click **Add Key > Create New Key**.
6. Select **JSON** format and download the file.

### 3. Apply Credentials in the LMS Admin Panel
1. Log in to the application as the Admin (`admin@lms.com`).
2. Go to the **Admin Settings > Storage Settings** page.
3. Select **GCP (Google Cloud Storage)** as the active driver.
4. Input your **GCP Project ID** and **GCP Bucket Name**.
5. Open your downloaded JSON key file, copy the *entire contents* (the JSON structure), and paste it directly into the **GCP Service Account Key** text field.
6. Click **Save Storage Settings**.

---

## Step 11: Production Optimization Tasks

Run these Laravel optimization commands on deployment:

```bash
# Cache the configuration file
php artisan config:cache

# Cache routes
php artisan route:cache

# Compile and cache Blade views
php artisan view:cache
```

> [!TIP]
> Whenever you modify `.env` or configuration files, remember to clear and rebuild the cache using `php artisan config:clear` or `php artisan optimize`.

---

## Step 12: Queue & Scheduler Configuration

### 1. Laravel Scheduler (Cron)
Add a single cron job to your server to execute the Laravel scheduler every minute:

```bash
* * * * * cd /var/www/app.iahms.com && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Queue Worker (Supervisor)
Since the queue driver is set to `database`, you need a process monitor to keep the queue workers running. Install Supervisor:

```bash
sudo apt-get install supervisor
```

Create a configuration file at `/etc/supervisor/conf.d/lms-worker.conf`:

```ini
[program:lms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app.iahms.com/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/app.iahms.com/storage/logs/worker.log
stopwaitsecs=3600
```

Start the Supervisor job:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start lms-worker:*
```
