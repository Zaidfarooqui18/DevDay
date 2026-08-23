# DevDay — Personal Daily Work & Development Reporting System

> **Turn your actual developer work into executive HTML email reports delivered directly to your manager.**

DevDay is a lightweight, high-craft daily work management and professional report dispatching application built for developers, software engineers, interns, students, and employees.

Unlike generic To-Do lists, DevDay is built around the end-to-end developer productivity loop:

$$\textbf{Plan} \longrightarrow \textbf{Work} \longrightarrow \textbf{Track} \longrightarrow \textbf{Complete} \longrightarrow \textbf{Review} \longrightarrow \textbf{Generate Report} \longrightarrow \textbf{Preview} \longrightarrow \textbf{Email Manager}$$

---

## 🚀 Key Features

* **Daily Developer Workspace**: View today's agenda, manage tasks with categories (Coding, DSA, DevOps, Research, etc.), priorities (Low, Medium, High, Urgent), and expected deliverables.
* **Timestamp-Accurate Focus Timer**: Start and pause focus sessions with timestamp-calculated durations ($t_{\text{elapsed}} = t_{\text{ended}} - t_{\text{started}}$) that stay accurate across page refreshes and browser reloads.
* **Learning & Blocker Logs**: Record what you learned, what you built, difficulty level, and blockers whenever tasks are completed.
* **End-of-Day Review**: Capture your biggest daily achievement, main blocker, and tomorrow's priority plan.
* **Carry Forward Engine**: Unfinished tasks can be carried forward to the next working day while preserving historical linkage.
* **Executive HTML Email Report Generation**: Compile the day's real MySQL metrics into an email-client-safe (Gmail, Outlook, Apple Mail) HTML document featuring inline CSS, metric cards, deliverables, and learnings.
* **Interactive Report Preview**: Inspect the exact HTML email before dispatching, with editable recipient and subject line.
* **PHPMailer SMTP Dispatch**: Deliver reports directly to your manager via SMTP with automated fallbacks and zero credential exposure.
* **Immutable Report Archive**: Historical reports are stored as immutable snapshots, viewable and resendable at any time.
* **Productivity Insights & Analytics**: Weekly KPIs and Chart.js graphs (Focus time by day, Tasks completed by day, and Category distribution).
* **Multi-Tenant Security & Isolation**: Strict user-level database scoping on every single query, CSRF protection, session fixation mitigation, and bcrypt password hashing.

---

## 🛠 Technology Stack

### Backend
* **PHP**: 8.2+
* **Database**: MySQL 8+ / SQLite 3 (PDO with prepared statements, foreign key cascades, and indexes)
* **Email Engine**: PHPMailer 6.9+ (SMTP with TLS/SSL encryption)
* **Authentication**: Native PHP sessions with secure cookies (`HttpOnly`, `SameSite=Lax`) & `password_hash()` (bcrypt)
* **Architecture**: REST-style modular service layer (`ReportService`, `MailService`, `AuthService`)

### Frontend
* **Markup & Style**: HTML5, CSS3, Tailwind CSS (modern dark developer theme)
* **Typography**: Inter & JetBrains Mono (Google Fonts)
* **Icons**: Lucide Icons
* **Charts**: Chart.js
* **Logic**: Vanilla JavaScript with AJAX / Fetch API and full CSRF header management

---

## 📋 System Requirements

* **PHP**: `8.2` or higher (CLI or Web Server) with `pdo_mysql` or `pdo_sqlite`, `curl`, `mbstring`, `openssl`
* **Composer**: `2.0+`
* **MySQL**: `8.0+` (optional: embedded SQLite works out-of-the-box for local zero-config development)
* **Web Server**: Built-in PHP server, Apache, Nginx, or Caddy

---

## 📦 Installation & Setup

### 1. Clone or Open Workspace
```bash
cd /path/to/DevDay
```

### 2. Install PHP Dependencies (PHPMailer)
```bash
php composer.phar install
# Or if composer is installed globally:
composer install
```

### 3. Configure Environment (`.env`)
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Edit `.env` to configure your database and SMTP settings:
```env
APP_NAME=DevDay
APP_ENV=development
APP_URL=http://localhost:8000
APP_SECRET=your_random_32_character_secret_key

# Database Connection (mysql or sqlite)
DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devday
DB_USERNAME=root
DB_PASSWORD=
DB_SQLITE_PATH=database/devday.sqlite

# SMTP Configuration (PHPMailer)
SMTP_HOST=smtp.mailtrap.io
SMTP_PORT=587
SMTP_USERNAME=your_smtp_username
SMTP_PASSWORD=your_smtp_password
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=reports@devday.local
SMTP_FROM_NAME="DevDay Work Reports"
```

### 4. Run Database Migrations & Seeder
Execute the automated migration runner to create tables, indexes, and development seed data:
```bash
php database/migrate.php
```

### 5. Start the Development Server
Run the application using the included router:
```bash
php -S 127.0.0.1:8000 public/router.php
```
Or start directly with document root:
```bash
php -S 127.0.0.1:8000 -t public
```

Open your browser at **`http://127.0.0.1:8000`**.

---

## 👤 Development Seed Credentials

| Role | Email | Password | Manager |
| :--- | :--- | :--- | :--- |
| **Primary Developer** | `zaid@example.com` | `password123` | Alex Vance (`alex.vance@techcorp.io`) |
| **Test User 2** | `sarah@example.com` | `password123` | Marcus Brody (`marcus@innovate.co`) |

---

## 🧪 Automated Testing

Run the comprehensive unit and integration test suites:

### Database & Service Verification
```bash
php tests/verify_devday.php
```

### End-to-End Live HTTP Integration Suite
```bash
php tests/http_integration_test.php
```

---

## 🔒 Security Specifications

* **SQL Injection Prevention**: 100% of database queries use PDO prepared statements with parameter binding. Raw string concatenation in SQL is prohibited.
* **User Isolation**: All data queries enforce `WHERE user_id = :authenticated_user_id`. Modifying resource IDs cannot access or mutate other users' records.
* **CSRF Protection**: All state-changing POST requests require a valid `X-CSRF-Token` header or `_csrf_token` payload verified against the active session.
* **Password Hashing**: Passwords are saved exclusively as bcrypt hashes via `password_hash($pass, PASSWORD_DEFAULT)`. Plaintext passwords and hashes are never exposed.
* **XSS Prevention**: Output is safely sanitized using `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
* **SMTP Credential Protection**: Mail credentials remain strictly server-side in `.env` and are never serialized or leaked to client JavaScript or error logs.

---

## 📁 Directory Structure

```text
DevDay/
├── api/                       # RESTful API Controllers
│   ├── auth.php               # Login, registration, session validation
│   ├── assignments.php        # Task CRUD, filters, carry-forward
│   ├── focus.php              # Focus timer sessions & duration calculation
│   ├── learning.php           # What learned / built logs
│   ├── reviews.php            # Daily achievements & tomorrow plan
│   ├── projects.php           # Project management & assignment links
│   ├── reports.php            # Readiness check, snapshot compiler, dispatch
│   ├── insights.php           # Chart.js analytics datasets
│   └── settings.php           # User profile & manager configuration
├── config/                    # Core Configuration
│   ├── app.php                # Session & environment boots
│   ├── database.php           # PDO singleton & connection manager
│   └── mail.php               # SMTP configuration
├── database/                  # Schema & Migrations
│   ├── schema.sql             # MySQL 8+ DDL schema
│   ├── seed.sql               # Development seed dataset
│   └── migrate.php            # Migration runner
├── helpers/                   # Utility & Security Helpers
│   ├── CSRF.php               # CSRF token manager
│   ├── Env.php                # .env parser
│   ├── Response.php           # Standardized JSON response envelopes
│   ├── Sanitizer.php          # XSS escaping & time formatting
│   └── Validator.php          # Input validation
├── middleware/                # Route & API Guard Middleware
│   └── AuthMiddleware.php     # Session authentication protection
├── models/                    # Domain Models
│   ├── User.php
│   ├── Project.php
│   ├── Assignment.php
│   ├── FocusSession.php
│   ├── LearningLog.php
│   ├── DailyReview.php
│   └── DailyReport.php
├── public/                    # Public Document Root & Views
│   ├── index.php              # Main Daily Workspace Dashboard
│   ├── login.php              # Authentication Login
│   ├── register.php           # Registration & Manager Setup
│   ├── logout.php             # Session Destruction
│   ├── projects.php           # Projects Overview & CRUD
│   ├── reports.php            # Report History & Snapshot Viewer
│   ├── insights.php           # Productivity Analytics & Charts
│   ├── settings.php           # Developer Profile & Settings
│   ├── router.php             # Development Server Router
│   └── assets/
│       ├── css/app.css        # Modern Developer Dark Theme
│       └── js/                # Modular Frontend Controllers
├── services/                  # Business Logic Services
│   ├── AuthService.php
│   ├── ReportService.php
│   └── MailService.php
├── templates/                 # View Layouts & Templates
│   ├── layout/                # Header, Nav, Footer, Global Modals
│   └── email/                 # Responsive HTML Email Report Template
│       └── daily-report.php
├── tests/                     # Automated Test Suites
│   ├── verify_devday.php
│   └── http_integration_test.php
├── .env.example
├── composer.json
└── README.md
```

---

## 📄 License
MIT License. Built with craftsmanship for developers.
