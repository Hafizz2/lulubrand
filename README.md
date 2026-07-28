# LULU — Women's Fashion & Couture Ordering Platform

LULU is a luxury women's clothing e-commerce platform designed with a House of CB reference quality bar: editorial imagery, faceted catalog filtering, inline color swatch swapping, fast guest checkout, single-page Inertia.js React admin panel, and direct Telegram Bot integration for instant order alerts and customer push notifications.

---

## 🛠️ Tech Stack

- **Backend**: PHP 8.2+ / Laravel 11
- **Database & Cache**: MySQL 8.0, Redis (session, queue, cache)
- **Storefront**: Laravel Blade + Alpine.js + Tailwind CSS (Server-rendered SEO with AJAX enhancements)
- **Admin Console**: Inertia.js + React + Tailwind CSS (SPA, mobile-first single-handed UX)
- **Telegram Bot**: Native Webhook controller + Redis Queued Jobs

---

## 🔑 Environment Variables Needed

Copy `.env.example` to `.env` and configure the following key variables:

```ini
APP_NAME=LULU
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lulu_db
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Telegram Bot Credentials
TELEGRAM_BOT_TOKEN=your_telegram_bot_token_here
TELEGRAM_WEBHOOK_SECRET=your_telegram_webhook_secret_here
TELEGRAM_OWNER_CHAT_ID=your_personal_chat_id_here
```

---

## 🚀 Setup & Installation

### 1. Install Dependencies & Build Assets
```bash
composer install
npm install
npm run build
```

### 2. Generate Application Key & Link Storage
```bash
php artisan key:generate
php artisan storage:link
```

### 3. Run Database Migrations & Seed Demo Data
```bash
php artisan migrate:fresh --seed
```

This will seed:
- 5 Nested Categories
- 15 Products with Size x Colour variant matrixes and sample stock
- 1 Default Owner Admin Account (`admin@lulu.com` / `password`)

---

## 👤 Creating the First Owner Admin Account

If you wish to create a custom owner admin account manually via CLI:

```bash
php artisan tinker
```
Inside Tinker:
```php
\App\Models\User::create([
    'name' => 'Brand Owner',
    'email' => 'owner@lulu.com',
    'password' => bcrypt('SecurePassword123'),
    'role' => 'owner',
]);
```

---

## 🤖 Setting Up Telegram Webhook & Integration

1. Obtain a Bot Token from [@BotFather](https://t.me/BotFather) on Telegram and set `TELEGRAM_BOT_TOKEN`.
2. Generate a random secret string and set `TELEGRAM_WEBHOOK_SECRET`.
3. Set your admin Telegram chat ID in `TELEGRAM_OWNER_CHAT_ID`.
4. Register the Webhook:
   - **Option A (Via Admin Console)**: Log into `/admin` -> **Telegram Settings** -> Enter your public URL (`https://yourdomain.com/telegram/webhook`) and tap **Register Webhook**.
   - **Option B (Via Artisan Command / Curl)**:
     ```bash
     curl -X POST "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook?url=https://yourdomain.com/telegram/webhook&secret_token=<YOUR_WEBHOOK_SECRET>"
     ```

---

## ⚙️ Running Queue & Local Server

### Start Local Application Server
```bash
php artisan serve
```

### Start Redis Queue Worker (Crucial for Telegram Notifications & Broadcasts)
```bash
php artisan queue:work --tries=3
```

---

## 🧪 Running Test Suite

Run full automated Pest/PHPUnit test suite:
```bash
vendor/bin/phpunit
```
or
```bash
php artisan test
```
