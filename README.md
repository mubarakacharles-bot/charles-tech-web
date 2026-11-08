# Charles Tech Website

Responsive storefront and technology-learning website for Charles High Technology Solution Ltd in Musanze, Rwanda. The site combines a product catalogue, shopping-cart interface, learning and project content, attendance tools, and PHP endpoints for the catalogue and account sessions.

The current storefront uses a blue-and-white visual theme, Bootstrap 5 served locally, vanilla JavaScript, and a progressive web app service worker.

## Features

- Responsive homepage, product catalogue, category browsing, search, product details, and browser-side cart.
- Catalogue loaded from the PHP API when available, with embedded product data for offline use.
- Registration, login, session restore, logout, CSRF protection, hashed passwords, and login-attempt throttling.
- Locally served Bootstrap CSS, installable PWA manifest, offline page and image caching.
- Existing learning, project directory, team, attendance, and information pages retained in the site.
- Local product, brand, and page imagery in `images/`.

## Technology

- HTML, CSS, Bootstrap 5.3.3, and vanilla JavaScript.
- PHP 8.1+ with PDO MySQL.
- MySQL or MariaDB (the documented local development setup uses XAMPP).
- Apache for the PHP API and local development.

## Project layout

| Path | Purpose |
| --- | --- |
| `index.html` | Main site, storefront markup, styles, and client-side application code. |
| `assets/` | Local Bootstrap stylesheet and PWA icons. |
| `images/` | Product and site imagery. |
| `api/` | PHP health and read-only product catalogue endpoints. |
| `api/auth/` | Registration, login, current-session, and logout endpoints. |
| `database/schema.sql` | MariaDB/MySQL schema. |
| `database/seed_products.sql` | Initial product catalogue data. |
| `database/configure_auth_db.php` | CLI-only setup for authentication table and minimum database grants. |
| `database/promote_admin.php` | CLI-only promotion of a registered account to administrator. |
| `sw.js` | PWA service worker and offline caches. |
| `manifest.webmanifest` | Installable web app metadata. |
| `tools/` | Local helper tools. |
| `READ-ME-FIRST.txt` | Additional image, XAMPP, and site maintenance notes. |

## Run locally with XAMPP (Windows)

1. Install XAMPP with Apache, PHP 8.1 or later, and MySQL/MariaDB.
2. Clone this repository into the Apache document root, for example:

   ```powershell
   git clone https://github.com/mubarakacharles-bot/charles-tech-web.git C:\xampp\htdocs\charles-tech
   ```

   If your existing `C:\xampp\htdocs\charles-tech` folder is a junction to the project, do not clone over it. Back up or move the existing folder before choosing a new clone destination.
3. Start **Apache** and **MySQL** in the XAMPP Control Panel.
4. Create the local database by importing `database/schema.sql`, followed by `database/seed_products.sql`, using phpMyAdmin or the MySQL command line.
5. Create the database configuration outside the web root at `C:\xampp\private\charles-tech-db.php`:

   ```php
   <?php
   return [
       'host' => '127.0.0.1',
       'database' => 'charles_tech',
       'username' => 'your_local_database_user',
       'password' => 'your_local_database_password',
   ];
   ```

   Use a least-privilege application account. This file is intentionally not part of the repository; never commit real passwords.
6. Open [http://localhost/charles-tech/](http://localhost/charles-tech/). Check the connection at `/api/health.php` and the catalogue at `/api/products.php`.
7. To enable authentication on a fresh database, open PowerShell in the project directory and run:

   ```powershell
   C:\xampp\php\php.exe database\configure_auth_db.php
   ```

   If MariaDB root has a password, provide it only to that PowerShell session:

   ```powershell
   $env:CHTS_DB_ROOT_PASSWORD = "your-local-root-password"
   C:\xampp\php\php.exe database\configure_auth_db.php
   Remove-Item Env:\CHTS_DB_ROOT_PASSWORD
   ```

8. Register an account through the site. To make the first administrator, run:

   ```powershell
   C:\xampp\php\php.exe database\promote_admin.php your-email@example.com
   ```

   Sign out and back in to refresh the session role.

The PHP API finds its database configuration at a `private\charles-tech-db.php` directory beside the Apache document root. For other hosting environments, adapt the configuration location outside the public document root; do not put credentials in tracked source files.

## API routes

| Method | Route | Purpose |
| --- | --- | --- |
| `GET` | `/api/health.php` | Database connectivity check. |
| `GET` | `/api/products.php` | Product catalogue, optionally filtered by `?category=...`. |
| `POST` | `/api/auth/register.php` | Create an account. |
| `POST` | `/api/auth/login.php` | Start an authenticated session. |
| `GET` | `/api/auth/me.php` | Restore/check the current session. |
| `POST` | `/api/auth/logout.php` | End the current session. |

Authentication writes are protected by CSRF tokens and PHP sessions. Passwords are hashed server-side. The service worker does not cache API responses.

## Current backend scope

The product catalogue and website account/session flows use the PHP and database backend. The schema also defines order, inventory, and business-settings tables, but there are not yet backend write endpoints for product or stock edits, checkout/orders, or business settings. The cart remains browser-side and is not shared across devices. The Attendance & Stock area still has a separate legacy sign-in flow and is not connected to the website account sessions.

The local XAMPP setup is for development only. Before public deployment, configure HTTPS, production database credentials and permissions, backups, and hosting-specific PHP/Apache settings. Do not expose a development database or the local XAMPP server to the public internet.

## PWA and offline behavior

The site can be installed as a PWA on supported browsers. On `localhost` and HTTPS, the service worker caches the application shell and local images. API requests remain live and are not cached. When changing the application shell or adding image paths, increment `VERSION` in `sw.js` so existing installations receive the update.

## Validation

There is no automated test runner configured in this repository. For a local smoke check, verify the homepage and mobile layout, `/api/health.php`, `/api/products.php`, registration/login/logout, and the service-worker cache in a browser.

## License

No license has been specified yet. All rights remain with their respective owners; contact Charles Tech before reuse or redistribution.
