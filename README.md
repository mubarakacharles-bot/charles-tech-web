# Charles Tech Website

Responsive storefront and technology-learning website for Charles High Technology Solution Ltd in Musanze, Rwanda. The site combines a product catalogue, shopping-cart interface, learning and project content, attendance tools, and PHP endpoints for the catalogue and account sessions.

The current storefront uses a blue-and-white visual theme, Bootstrap 5 served locally, vanilla JavaScript, and a progressive web app service worker.

## Features

- Responsive homepage, product catalogue, category browsing, search, product details, and browser-side cart.
- Catalogue loaded from the PHP API when available, with embedded product data for offline use.
- Registration, login, session restore, logout, CSRF protection, hashed passwords, and login-attempt throttling. Sign-in can optionally remember an email on the device; plaintext passwords are never saved by the site.
- Locally served Bootstrap CSS, installable PWA manifest, offline page and image caching.
- Existing learning, project directory, team, attendance, and information pages retained in the site.
- Trainers can publish scheduled online-class tasks with a secure meeting link; learners can join remotely, see an upcoming-class agenda, download 60-minute calendar events with a 15-minute alert, discuss each task, submit work, and receive trainer feedback.
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
| `student-portal.html` | Isolated Attendance & Stock application loaded in the portal frame. |
| `assets/` | Local Bootstrap stylesheet and PWA icons. |
| `images/` | Product and site imagery. |
| `api/` | PHP health, product catalogue, and administrator-only portal data endpoints. |
| `api/auth/` | Registration, login, current-session, and logout endpoints. |
| `database/schema.sql` | MariaDB/MySQL schema. |
| `database/seed_products.sql` | Initial product catalogue data. |
| `database/configure_auth_db.php` | CLI-only setup for authentication and portal tables and minimum database grants. |
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
7. To enable authentication and Student Portal storage on a fresh or existing database, open PowerShell in the project directory and run:

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
| `GET` | `/api/products.php` | Product catalogue, optionally filtered by `?category=...`. Variant `available` is public; exact `stock` is returned only for an active administrator session. |
| `POST` | `/api/auth/register.php` | Create an account. |
| `POST` | `/api/auth/login.php` | Start an authenticated session. |
| `GET` | `/api/auth/me.php` | Restore/check the current session. |
| `POST` | `/api/auth/logout.php` | End the current session. |
| `GET` | `/api/portal.php` | Load the Student Portal database copy (website administrators only). |
| `POST` | `/api/portal.php` | Reset or deactivate a student account (website administrators only; CSRF protected). |
| `PUT` | `/api/portal.php` | Import/update the Student Portal database copy (website administrators only; CSRF protected). |
| `GET`, `POST`, `PUT` | `/api/portal-students.php` | Student registration, sign-in, scoped portal data, submissions, discussion, and password changes. |

Authentication writes are protected by CSRF tokens and PHP sessions. Passwords are hashed server-side. The service worker does not cache API responses.

## Current backend scope

The product catalogue and website account/session flows use the PHP and database backend. The schema also defines order, inventory, and business-settings tables, but there are not yet backend write endpoints for product or stock edits, checkout/orders, or business settings. Shop product creation, edits, removal, variant changes, and stock adjustments use the administrator-only `POST /api/admin-products.php` endpoint. The Attendance & Stock application is isolated in `student-portal.html`; its administrator can use the website's server-side administrator session to load and synchronize portal data.

### Student Portal data assessment

The portal's admin-managed attendance, portal stock, tasks, submissions, discussions, and settings are stored in the MySQL `student_portal_state` snapshot. Online-class meeting details are saved as task fields through the existing portal data flow; no additional backend endpoint is required. Student credentials are stored separately in `student_portal_accounts` using server-side password hashes. Students sign in or register with the server, can see only their own attendance and submissions, and their phone changes, submissions, discussion posts, and password changes are saved through the student API. The server filters each student's response and accepts only that student's permitted changes. Portal stock remains separate from the storefront's `product_variants` and `inventory_movements` tables.

On a fresh or existing installation, run `database/configure_auth_db.php` after importing the updated schema to create the portal tables and grant the application database account its minimum required privileges. Sign into the website as an administrator, open Attendance & Stock, then review the portal record counts and database status in **Admin & security**. Download a backup before using **Back up, then upload this browser**: uploading replaces the shared portal snapshot with that browser's existing data and student credentials. Use **Load the database copy here** only when the database has the records you want on this browser. Once connected, administrator changes and student activity are saved to MySQL; the dashboard reports database write failures instead of showing them as synced. The local browser backup is retained.

Existing students must sign in from a browser after the administrator has imported their records. New student accounts can register directly against the database. Database snapshots use optimistic revisions to detect stale administrator writes; student updates are merged server-side into the shared snapshot while preserving server-controlled attendance, tasks, and review results. Keep HTTPS enabled, take regular backups, and keep the database private.

The local XAMPP setup is for development only. Before public deployment, configure HTTPS, production database credentials and permissions, backups, and hosting-specific PHP/Apache settings. Do not expose a development database or the local XAMPP server to the public internet.

## PWA and offline behavior

The site can be installed as a PWA on supported browsers. On `localhost` and HTTPS, the service worker caches the application shell and local images. API requests remain live and are not cached. When changing the application shell or adding image paths, increment `VERSION` in `sw.js` so existing installations receive the update.

## Validation

There is no automated test runner configured in this repository. For a local smoke check, verify the homepage and mobile layout, `/api/health.php`, `/api/products.php`, registration/login/logout, and the service-worker cache in a browser.

## License

No license has been specified yet. All rights remain with their respective owners; contact Charles Tech before reuse or redistribution.

Stock visibility: the catalogue API checks the current user role in MySQL on each request. Guests and customers receive availability only. Stock counts are held in page memory and cleared when accounts change; public HTML exports contain availability rather than inventory quantities. Checkout remains a browser preview: final quantities must be confirmed by the business until server-side order validation is implemented.

## Vercel deployment

Import this repository into Vercel. Set framework to Other and configure CHTS_BACKEND_URL as the public HTTPS URL of the PHP application (without /api). The checked-in build command packages only public assets and proxies /api to that backend. PHP/MySQL must remain on a public PHP host, with database configuration outside its web root. The build intentionally fails without a backend URL so login and database features are not silently published broken. Verify login/logout, admin-only stock and Student Portal after deployment.

### Attendance and stock persistence improvements

Portal saves capture an immutable snapshot at the time of each save and persist snapshots in order. Database loads wait for pending writes and retain the previous browser copy at `sas_data_v1_before_database_load`; explicit uploads download a JSON backup first. The admin API validates record IDs, stock quantities, movement directions, and Present/Absent attendance marks before writing, and rolls back failed transactions. Existing roles, student projections, scoring, revision checks and tables are retained. Run `C:\xampp\php\php.exe tools/test-portal-validation.php` for record validation checks.

Student self-registration creates a student record automatically when the submitted registration number is new. Existing registration numbers retain their linked student identity and metadata. All new accounts use the student role; existing uniqueness constraints, password hashing, session/CSRF rules and administrator-only stock access remain in place. Run `C:\xampp\php\php.exe tools/test-portal-registration.php` for registration checks.

Administrator sign-in supports an optional unique website username as well as email. Existing installations must run `database/configure_auth_db.php` to add the nullable username column before deploying the updated login API. The Attendance & Stock sign-in screen has a separate Administrator sign in button that verifies the website session and database role. Local account recovery uses the CLI-only `database/set_admin_login.php existing-email username`; provide the new password on stdin. This command updates only the specified existing account and verifies the password hash. Do not commit account passwords.

### Administrator product management

Use Admin Dashboard > Stock Levels to add/edit products and prices, remove products, and maintain quantities per variant. Writes require an active database administrator role and CSRF token; stock writes check the previous quantity to prevent overwriting another session. Removed products are deactivated to retain order and inventory history. Stock movements record actor, time and quantity delta. Public catalogue responses omit exact stock and movement history; the storefront refreshes from the server every 30 seconds while visible. Admins can use Refresh live stock to update without losing unsaved edits automatically. Run `database/configure_auth_db.php` on existing installations to grant the application its product write permissions. Product photos, deals and checkout remain existing browser features.

### Photographic presentation

The visual layer in `assets/css/photographic.css` uses the existing Charles Tech images and content: a full-screen home hero, alternating course sections, and taller Learn/Hot Deals carousels. Hero controls support next/previous and pause/play; automatic image changes stop offscreen, in hidden tabs and for reduced-motion users. Service-worker installation downloads only the opening image rather than the entire image catalogue; viewed images are still cached on demand. Existing navigation, catalogue, portal and database controls are retained.
