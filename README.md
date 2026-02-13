# IP Inventory Dashboard

This repository now includes a **PHP 8.3 + MySQL** backend for the dashboard page.

## What changed
- `index.php` is the new dashboard entry point.
- Dashboard metrics and location summary are loaded from MySQL via `api/dashboard.php`.
- Database connection is centralized in `includes/config.php`.
- SQL schema is provided in `sql/schema.sql`.
- `index.html` now redirects to `index.php`.

## Requirements
- PHP 8.3+
- MySQL 8+

## Setup
1. Create database/table:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
2. Configure DB credentials via environment variables (optional defaults shown):
   - `DB_HOST` (default `127.0.0.1`)
   - `DB_PORT` (default `3306`)
   - `DB_NAME` (default `ip_inventory`)
   - `DB_USER` (default `root`)
   - `DB_PASS` (default empty)
3. Start PHP built-in server:
   ```bash
   php -S 0.0.0.0:8000
   ```
4. Open:
   - `http://localhost:8000/index.php`

## Notes
- Existing pages (`inventory.html`, `manage.html`, `reports.html`, `status.html`) still use browser localStorage.
- The dashboard page is now server-backed with MySQL.
