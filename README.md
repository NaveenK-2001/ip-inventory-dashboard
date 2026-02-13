# IP Inventory Dashboard (PHP 8.3 + MySQL)

The full project has now been converted from static HTML/localStorage to a PHP + MySQL-backed app.

## Converted pages
- `index.php` (dashboard)
- `inventory.php` (inventory + VLAN/IP management)
- `manage.php` (search/edit/delete IPs)
- `reports.php` (import/export reports)
- `status.php` (filtered status listing)

Legacy `.html` files now redirect to these PHP pages.

## Backend APIs
- `api/dashboard.php` - dashboard counters/charts/location summary
- `api/ips.php` - list/create/update/delete IP addresses
- `api/vlans.php` - list/create/delete VLAN definitions

## Database schema
Run:
```bash
mysql -u root -p < sql/schema.sql
```

Creates:
- `ip_addresses`
- `vlans`

## Configuration
Database credentials are read from environment variables (with defaults):
- `DB_HOST` (`127.0.0.1`)
- `DB_PORT` (`3306`)
- `DB_NAME` (`ip_inventory`)
- `DB_USER` (`root`)
- `DB_PASS` (empty)

## Run locally
```bash
php -S 0.0.0.0:8000
```

Open `http://localhost:8000/index.php`.
