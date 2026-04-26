# Multi-Tenant SaaS Monitoring API

I built this because I needed a lightweight, self-contained monitoring backend for a SaaS product—no frills, just reliable uptime checks across multiple customer orgs. It runs on Lumen 10.x, uses a single database with row-level tenancy, and handles queuing and scheduling out of the box.

---

### What It Does

- **Tenants are isolated by `organization_id`**  
  Every monitor, check result, and user belongs to one org. No cross-org leakage—checks and queries filter by the org from the auth token.

- **Auth with API tokens**  
  `POST /register` creates a user + org (first user auto-grants admin). Subsequent login (`POST /login`) returns a token used in `Authorization: Bearer` headers. RBAC blocks non-admins from modifying monitors.

- **Monitors get checked on schedule**  
  - `monitor:check` Artisan command (run via cron or supervisor) fires off jobs  
  - Queue worker processes checks in the background  
  - Each check hits the URL with Guzzle, records status (up/down/timeout) and response time, and logs a warning/info message

- **History and stats**  
  - `/monitors/{id}/checks` returns paginated results with optional date filters  
  - `/monitors/{id}/statistics` computes uptime % and avg response time for `24h`, `7d`, `30d`, or `90d`

---

### Stack

- PHP 8.1+  
- Lumen 10.x (with Eloquent, facades, and auth)  
- SQLite (development) or MySQL/Postgres (prod)  
- `database` queue connection (jobs table + failed_jobs)  

---

### Setup (90 seconds)

```bash
# 1. Clone or create project
git clone https://github.com/you/monitoring-api.git
cd monitoring-api

# 2. Configure
cp .env.example .env
# Edit .env: set DB_DATABASE=/absolute/path/to/database.sqlite
# and QUEUE_CONNECTION=database

# 3. Enable required services in bootstrap/app.php
$app->withFacades();
$app->withEloquent();
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->configure('queue'); // Important

# 4. Init DB and deps
touch database/database.sqlite
composer install
composer require guzzlehttp/guzzle

# 5. Run migrations
php artisan migrate
```

---

### Running It

```bash
# Web server
php -S localhost:8000 -t public

# Queue worker (in another terminal)
php artisan queue:work

# Scheduler (add to crontab)
* * * * * cd /path/to/monitoring-api && php artisan schedule:run >> /dev/null 2>&1
```

---

### API Summary

| Endpoint | Auth | Role | Description |
|----------|------|------|-------------|
| `POST /register` | — | — | Create user + org (first user = admin) |
| `POST /login` | — | — | Get API token |
| `GET /monitors` | ✅ | any | List your org’s monitors |
| `POST /monitors` | ✅ | admin | Create monitor (`{name,url,check_interval}`) |
| `GET /monitors/{id}` | ✅ | any | Get monitor details |
| `PUT /monitors/{id}` | ✅ | admin | Update monitor (e.g., pause, rename) |
| `DELETE /monitors/{id}` | ✅ | admin | Delete monitor |
| `GET /monitors/{id}/checks` | ✅ | any | Historical checks (supports `?start_date=...&end_date=...&limit=...`) |
| `GET /monitors/{id}/statistics` | ✅ | any | Stats: `?period=24h|7d|30d|90d` |

---

### Notes

- No rate limiting. Add if you need it.  
- Logs to `storage/logs/lumen.log`. Warning-level logs fire on failures.  
- SQLite works fine for dev, but switch to MySQL/Postgres before scaling—this isn’t write-optimized for high concurrency.  
- `check_interval` is in minutes. Minimum: 1, max: 1440.

## More from Karelseaat

For more projects and experiments, visit my GitHub Pages site: [karelseaat.github.io](https://karelseaat.github.io/)

<!-- KEEP-EXPLORING-START -->
## Keep Exploring

If you made it to the bottom, jump straight into a few related repos:

- [Tenant Ops Console](https://github.com/karelseaat/tenant_ops_console)
- [Laravel Dashboard App](https://github.com/karelseaat/laravel_dashboard_app)
- [Workflow Case Tracker](https://github.com/karelseaat/workflow_case_tracker)

- [Full project index](https://karelseaat.github.io/)
<!-- KEEP-EXPLORING-END -->
