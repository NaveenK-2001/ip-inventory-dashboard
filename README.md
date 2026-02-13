# IP Inventory Dashboard

A PHP + MySQL application for tracking and reporting IP allocations across locations, zones, and VLANs.

## Features
- Dashboard with status cards, charts, and location-wise summary.
- IP inventory browsing by location, zone, and VLAN.
- IP management (create, update, delete).
- VLAN management for each location/zone.
- Reports for status, location, VLAN, used IPs, and free IPs.
- CSV export/import support for inventory operations.

## Requirements
- **Operating system**: Linux/macOS/Windows (any OS that can run PHP + MySQL).
- **PHP**: 8.3 or higher.
- **PHP extensions**:
  - `pdo`
  - `pdo_mysql`
  - `json`
- **MySQL**: 8.0 or higher.
- **Web browser**: modern browser (Chrome, Firefox, Edge, Safari).

## Project structure
- `index.php` - dashboard landing page.
- `inventory.php` - inventory browsing by VLAN.
- `manage.php` - IP CRUD management page.
- `reports.php` - reporting and CSV import/export page.
- `status.php` - filtered status list view.
- `api/*.php` - backend API endpoints.
- `includes/config.php` - database connection settings.
- `sql/schema.sql` - database schema and seed data.

## Installation & setup

### 1) Clone and open the repository
```bash
git clone <your-repository-url>
cd ip-inventory-dashboard
```

### 2) Create the database schema
```bash
mysql -u root -p < sql/schema.sql
```
This creates the `ip_inventory` database and required tables.

### 3) Configure database connection
The app reads DB settings from environment variables (with defaults):
- `DB_HOST` (default: `127.0.0.1`)
- `DB_PORT` (default: `3306`)
- `DB_NAME` (default: `ip_inventory`)
- `DB_USER` (default: `root`)
- `DB_PASS` (default: empty)

Example:
```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=ip_inventory
export DB_USER=root
export DB_PASS=your_password
```

### 4) Start the PHP server
```bash
php -S 0.0.0.0:8000
```

### 5) Open the application
- Dashboard: `http://localhost:8000/index.php`
- Inventory: `http://localhost:8000/inventory.php`
- Manage IPs: `http://localhost:8000/manage.php`
- Reports: `http://localhost:8000/reports.php`

## Quick verification
1. Open the dashboard and confirm cards/charts load.
2. Open **Manage IPs**, add an IP record, and verify it appears in **Inventory**.
3. Open **Reports** and run/export a report.

## Troubleshooting
- **Database connection error**: verify MySQL is running and DB environment variables are correct.
- **Blank/failed API data**: check PHP error output and confirm `pdo_mysql` is enabled.
- **Port already in use**: run on another port, e.g. `php -S 0.0.0.0:8080`.
