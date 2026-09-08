# LibraSync — Deployment Guide

This is the step-by-step checklist for moving LibraSync from your laptop
(local development, SQLite) to a real server that Students, Teachers,
Library Staffs, and the Super Admin can all reach from their own
devices over a URL.

---

## 0. Decide where it will live

- **School local network** — a PC/server on the school LAN that stays on.
  Reachable only from inside the school network (library, computer labs,
  staff Wi-Fi). No hosting cost, no public internet needed.
- **Public hosting** — a hosting provider gives you a real URL reachable
  from anywhere. Costs money (or a free tier for a demo).

Everything below applies either way — only the "where you upload it" part
changes.

---

## 0.5 Campus LAN quick start with XAMPP

This project's `.env` is now pre-configured for MySQL (not SQLite) so it's
ready to run on XAMPP. Steps, on the PC that will act as the server:

1. Install [XAMPP](https://www.apachefriends.org/) (includes Apache, MySQL,
   and PHP together).
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin`, click **New**, and create a database
   named `librasync` (matches `DB_DATABASE` already set in `.env`). Leave
   it empty — migrations will build the tables.
4. Copy the whole `LibraSync` project folder into XAMPP's `htdocs` folder
   (e.g. `C:\xampp\htdocs\LibraSync`).
5. Open a terminal **inside that folder** and run:
   ```bash
   composer install
   npm install && npm run build
   php artisan key:generate
   php artisan migrate
   php artisan storage:link
   ```
6. Find this PC's local network IP (Windows: `ipconfig`, look for
   "IPv4 Address", e.g. `192.168.1.50`).
7. Point Apache's document root at this project's `public/` folder (edit
   `C:\xampp\apache\conf\extra\httpd-vhosts.conf`, or simplest for a demo:
   just keep using `php artisan serve --host=0.0.0.0 --port=8000` instead
   of Apache — see below).
8. **Anyone else on the same Wi-Fi/LAN** — Super Admin, Library Staffs,
   Students, Teachers — opens their own phone/laptop browser and visits:
   ```
   http://192.168.1.50:8000
   ```
   (using your PC's actual IP from step 6). No installation needed on
   their device — it's just a website to them.

The simplest way to serve it to the LAN without touching Apache config at
all is:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
`--host=0.0.0.0` is what makes it reachable from *other* devices on the
network — plain `php artisan serve` only listens on `127.0.0.1`, which is
why other devices couldn't reach it before.

---

## 1. Server requirements

Whoever hosts this needs:
- PHP 8.2 or higher, with these extensions enabled: `pdo_mysql`, `mbstring`,
  `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `zip` (the `zip` extension
  is required for the Excel/.xlsx import feature).
- MySQL 5.7+ / MariaDB (see step 3 — switching off SQLite).
- Composer (to install PHP dependencies on the server, unless your host
  lets you upload the `vendor/` folder directly).
- Node.js + npm (only needed once, to build the CSS/JS — you can also just
  build it locally and upload the compiled `public/build` folder instead).

---

## 2. Upload the project

- Shared hosting: use their file manager or FTP/SFTP to upload everything
  in the `LibraSync` folder.
- Git-based hosting (Railway, Render, etc.): push this project to a Git
  repo and connect it.
- School server: copy the project folder onto that machine directly.

---

## 3. Switch the database from SQLite to MySQL

Locally we used SQLite (a single `database.sqlite` file) at first because
it needs zero setup. **This is already switched to MySQL in `.env`** as of
this version (see section 0.5 above for the XAMPP/campus LAN setup). If
you're instead deploying to public hosting, just update `.env` there with
that host's own MySQL credentials:

1. Create a MySQL database through your host's control panel (or XAMPP's
   phpMyAdmin if hosting on a school PC). Note the database name,
   username, and password.
2. Update `.env` on the server (see `.env.production` template below).
3. Run the migrations to build all the tables fresh in that database:
   ```bash
   php artisan migrate
   ```
   This creates empty tables — it does **not** copy your local test data
   over, which is what you want for a real launch.

---

## 4. Production `.env` checklist

See `.env.production` in this same folder for a ready-to-fill template.
The key differences from your local `.env`:

| Setting | Local (dev) | Production |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` (**important** — don't show detailed error pages/file paths to real users) |
| `APP_URL` | `http://localhost` | your real domain, e.g. `https://librasync.pap.edu.ph` |
| `APP_KEY` | (existing dev key) | generate a **new** one with `php artisan key:generate` — never reuse the dev key |
| `DB_CONNECTION` | `sqlite` | `mysql` |
| `DB_HOST` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | (unused) | your host's MySQL credentials |

---

## 5. Post-upload commands (run these on the server)

```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build      # skip this if you upload a pre-built public/build folder instead
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan config:cache
php artisan route:cache
```

If any of these say a directory "must be present and writable," create it
(the same issue we hit locally with `bootstrap/cache`):
```bash
mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
```

---

## 6. Point Apache/Nginx (or your host's settings) at `public/`

The **document root must be the `public/` folder**, not the project root.
This is true for XAMPP/Apache configs and most hosting control panels —
if you point it at the top-level `LibraSync` folder instead, visitors will
see your `.env` file and source code instead of the app.

---

## 7. HTTPS

If this is public-internet hosting, turn on HTTPS (most hosts offer free
Let's Encrypt certificates with one click). Browsers increasingly warn or
block plain HTTP sites, and login credentials should never travel over
plain HTTP.

---

## 8. Before real users touch it

- [ ] Clear out test/dummy accounts and books (don't let your panel see
      "Juan Dela Cruz" test data).
- [ ] Create the real Super Admin account directly in production with a
      real password — don't carry over a dev password.
- [ ] Take a backup immediately using the built-in Backup & Restore page,
      so you have a clean starting snapshot to fall back on.
- [ ] If your host supports it, set up a cron job so scheduled tasks (due
      reminders, semester archiving) actually run automatically:
      ```
      * * * * * cd /path/to/LibraSync && php artisan schedule:run >> /dev/null 2>&1
      ```
      Without this, those tasks only run when triggered manually via
      `php artisan librasync:...` commands.

---

## 9. What does NOT change

- The code itself doesn't need edits to go from SQLite to MySQL — Laravel
  talks to either through the same code, only the `.env` values differ.
- Every feature (registration, borrowing, notifications, reports, etc.)
  behaves the same way in production as it did locally — the only
  difference is *where* the data lives and *who* can reach it.
