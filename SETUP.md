# Laravel CRM (Krayin) – Setup Instructions

Step-by-step guide to get the project running on your machine.

---

## 1. Requirements

Before starting, ensure you have:

| Requirement | Version |
|------------|---------|
| **PHP** | 8.2 or higher |
| **Composer** | 2.5 or higher |
| **Node.js** | 8.11.3 LTS or higher (for frontend assets) |
| **MySQL** | 5.7.23+ **or** **MariaDB** 10.2.7+ |
| **RAM** | 3 GB or higher recommended |

**Required PHP extensions:** `gd`, `zip` (and others your PHP build may omit). Composer will fail with “lock file does not contain a compatible set of packages” if these are missing. See [Troubleshooting](#9-troubleshooting) below to enable them.

Optional for local mail testing: [Mailpit](https://github.com/mailpit/mailpit) or [MailHog](https://github.com/mailhog/MailHog).

---

## 2. Clone / Open the project

If you haven’t already:

```bash
cd c:\Code\laravel-crm
```

---

## 3. Install PHP dependencies

```bash
composer install
```

If you see memory or timeout issues, try:

```bash
composer install --no-scripts
composer run-script post-autoload-dump
```

---

## 4. Environment file

Create your local environment file from the example:

```bash
copy .env.example .env
```

Then edit `.env` and set at least:

- **`APP_URL`** – URL you’ll use to open the app (e.g. `http://localhost:8000`).
- **Database** – connection and credentials for an existing empty database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel-crm
DB_USERNAME=root
DB_PASSWORD=your_password
DB_PREFIX=
```

Create the database if it doesn’t exist (e.g. in MySQL: `CREATE DATABASE `laravel-crm`;`).

You can leave **Mail** as-is for local dev, or point to Mailpit/MailHog if you use them.

---

## 5. Run the Krayin installer

This command will:

- Generate `APP_KEY` if missing
- Run migrations (fresh)
- Seed initial data (locales, currencies, attributes, lead pipelines, etc.)
- Publish config/assets and create the storage link
- Optionally create an admin user

```bash
php artisan krayin-crm:install
```

When prompted:

1. **Application name** – e.g. `Krayin CRM`
2. **Application URL** – e.g. `http://localhost:8000`
3. **Database** – connection type, host, port, database name, username, password (and optional prefix)
4. **Locale** – e.g. English
5. **Currency** – e.g. USD
6. **Admin user** – name, email, password (e.g. `admin@example.com` / `admin123`)

To skip interactive env/database prompts (e.g. CI or you’ve already configured `.env`):

```bash
php artisan krayin-crm:install --skip-env-check --skip-admin-creation
```

Note: `--skip-admin-creation` means no admin user is created; you’d need to create one manually or run the installer again without that flag.

---

## 6. Frontend assets (optional for full UI)

Install Node dependencies and build or run the dev server:

```bash
npm install
```

- **Development** (with hot reload):

  ```bash
  npm run dev
  ```

- **Production build**:

  ```bash
  npm run build
  ```

Keep `npm run dev` running in a separate terminal when developing the frontend.

---

## 7. Run the application

**Local development:**

```bash
php artisan route:clear
php artisan serve
```

Then open in your browser: **http://localhost:8000** (or whatever you set in `APP_URL`).

**Admin login:**

- URL: `http://localhost:8000/admin` (or `APP_URL/admin`)
- Default credentials (if you used the installer prompts):  
  - Email: `admin@example.com`  
  - Password: `admin123`  
  (or whatever you entered during install.)

---

## 8. Quick reference commands

| Task | Command |
|------|--------|
| Clear route cache | `php artisan route:clear` |
| Clear all caches | `php artisan optimize:clear` |
| Run migrations only | `php artisan migrate` |
| Fresh migrate + seed (destructive) | `php artisan migrate:fresh` then re-run installer or seeders as needed |
| Start dev server | `php artisan serve` |
| Frontend dev | `npm run dev` |
| Frontend build | `npm run build` |

---

## 9. Troubleshooting

- **“Verifying lock file contents can be installed on current platform” → “Your lock file does not contain a compatible set of packages”**  
  Composer is checking that your PHP has the extensions required by the lock file. Often the error lists missing extensions such as **ext-gd** or **ext-zip**.

  1. **Enable the extensions** (recommended):  
     - Find your `php.ini` (run `php --ini` in a terminal; often `C:\xampp\php\php.ini` on XAMPP).  
     - Uncomment the lines for the missing extensions by removing the leading `;`:
       ```ini
       ;extension=gd    →  extension=gd
       ;extension=zip   →  extension=zip
       ```
     - Restart the web server if you use Apache/Nginx, then run `composer install` again.

  2. **Temporary workaround** (not for production): install while ignoring those platform requirements:
     ```bash
     composer install --ignore-platform-req=ext-gd --ignore-platform-req=ext-zip
     ```
     Some features (e.g. PDF export, Excel, IMAP) may not work until the extensions are enabled.

- **“Class not found” or autoload errors**  
  Run: `composer dump-autoload`

- **Permission errors (storage/logs, bootstrap/cache)**  
  Ensure the web server / CLI user can write to `storage/` and `bootstrap/cache/`.

- **Database connection refused**  
  Check MySQL/MariaDB is running and that `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` are correct.

- **Blank or broken CSS/JS**  
  Run `npm run dev` or `npm run build` and ensure `APP_URL` in `.env` matches the URL you use in the browser.

- **Installer fails or prompts again**  
  Ensure `.env` exists and has valid `APP_KEY` and database settings. You can run with `--skip-env-check` if `.env` is already configured.

For more details and Docker setup, see the main [README](README.md) and [Krayin CRM Docs](https://devdocs.krayincrm.com/).
