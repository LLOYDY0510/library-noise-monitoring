# LibraryQuiet Monitoring System (LQMS)

A PHP-based web application for real-time noise level monitoring across library zones at **Northern Bukidnon State College (NBSC)**. Sensors report decibel readings per zone, triggering alerts when thresholds are exceeded and providing staff with a live dashboard, zone management, and a full activity audit trail.

---

## Features

- **Live Dashboard** — Real-time dB readings per zone with Chart.js noise history, Leaflet campus map with live markers, and a live activity feed
- **Zone Management** — Add, edit, delete, and override zones; click-to-pick coordinate map for sensor placement
- **Alert System** — Automatic warning/critical alerts with staff messaging thread and resolve workflow
- **Reports** — Generate and log Daily, Weekly, Monthly, Incident, and Sensor Health reports
- **User Management** — Role-based access (Administrator, Library Manager, Library Staff) with bcrypt password storage
- **Activity Log** — Full audit trail of every user action powered by DataTables with search, sort, CSV export, and browser tracking
- **Simulated IoT** — `simulate_noise.php` runs on a 7-minute cron interval to simulate sensor readings

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.x |
| Database | MySQL / MariaDB |
| Frontend | Vanilla JS, CSS custom properties (dark theme) |
| Charts | Chart.js 4.4.1 + chartjs-plugin-annotation |
| Maps | Leaflet.js 1.9.4 + OpenStreetMap |
| Tables | DataTables 2.0.3 + jQuery 3.7.1 |
| Fonts | Plus Jakarta Sans (Google Fonts) |

---

## Project Structure

```
libraryquet/
├── index.php               # Login + Registration
├── dashboard.php           # Main dashboard
├── zones.php               # Zone management
├── alerts.php              # Alert management
├── reports.php             # Report generation
├── users.php               # User management (Admin only)
├── activity_log.php        # Activity log (Admin only)
├── setup.sql               # Database schema + seed data
│
├── includes/
│   ├── config.php          # DB connection, helpers, logActivity()
│   ├── auth.php            # Session auth, role guards
│   ├── layout.php          # Shared HTML shell (sidebar, topbar)
│   └── layout_footer.php   # Shared footer + script tags
│
├── css/
│   ├── main.css            # Design tokens, layout, dark theme
│   ├── components.css      # Reusable component classes
│   └── login.css           # Auth page styles
│
├── js/
│   ├── app.js              # Shared: BASE_URL, clock, toasts, modals
│   ├── charts.js           # Chart.js noise history renderer
│   ├── dashboard.js        # Dashboard: chart, map, activity feed
│   ├── login.js            # Auth: tabs, eye toggle, strength bar
│   ├── zones.js            # Zones: map picker, modals
│   ├── alerts.js           # Alerts: resolve confirm
│   ├── users.js            # Users: role/password/delete modals
│   └── activity_log.js     # DataTables init + auto-refresh
│
├── api/
│   ├── active_alerts_count.php  # Badge poll endpoint
│   ├── zone_levels.php          # Live dB levels JSON
│   ├── zone_map.php             # Map marker data JSON
│   ├── activity_log.php         # Activity feed JSON
│   ├── export_logs.php          # CSV export
│   └── trigger_sim.php          # Manual simulation trigger
│
└── php/
    ├── logout.php               # Session destroy + redirect
    └── simulate_noise.php       # IoT simulation (cron target)
```

---

## Installation

### Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache / Nginx with `mod_rewrite`
- XAMPP (local) or any shared hosting with PHP + MySQL

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/LLOYDY0510/libraryquet.git
cd libraryquet
```

**2. Create the database**

Open phpMyAdmin (or MySQL CLI) and run:
```sql
CREATE DATABASE lqms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Then import `setup.sql`:
```bash
mysql -u root -p lqms_db < setup.sql
```

**3. Configure the database connection**

Open `includes/config.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lqms_db');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

`BASE_URL` is auto-detected — no manual change needed for local or hosted environments.

**4. Set up the simulation cron job** *(optional)*

```bash
# Run every 7 minutes
*/7 * * * * php /path/to/libraryquet/php/simulate_noise.php
```

**5. Access the app**

```
http://localhost/libraryquet/
```

---

## Noise Level Thresholds

| Status | Range | Color |
|---|---|---|
| Quiet | < 40 dB | Green |
| Moderate | 40 – 60 dB | Amber |
| Loud | ≥ 60 dB | Red |

Thresholds are configurable per zone via the Zone Management page.

---

## Roles & Permissions

| Permission | Administrator | Library Manager | Library Staff |
|---|:---:|:---:|:---:|
| View Dashboard | ✅ | ✅ | ✅ |
| View / Resolve Alerts | ✅ | ✅ | ✅ |
| Add / Edit / Delete Zones | ✅ | ✅ | ❌ |
| Override Sensor Readings | ✅ | ✅ | ❌ |
| Generate Reports | ✅ | ✅ | ❌ |
| Manage Users | ✅ | ❌ | ❌ |
| View Activity Log | ✅ | ❌ | ❌ |

---

## Contributing

1. Fork the repository
2. Create your branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m "feat: add your feature"`
4. Push to the branch: `git push origin feature/your-feature`
5. Open a Pull Request

---
