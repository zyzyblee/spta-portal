# SPTA Payment Monitoring System
### Sta. Catalina National High School — Web-Based Student Portal

A complete PHP + MySQL web application for monitoring Senior High School
SPTA payments across the ABM, STEM, and HUMSS strands. Built to support
the research study *"Proposed Web-Based Student Portal for Monitoring
SPTA Payments Among Grade 11 Students."*

---

## 1. What's included

- **Public portal** — anyone can browse strands → sections → the student
  master list → a student's detailed payment record (read-only, no
  login required, matching the navigation flow in the brief).
- **Admin panel** — login required. Manage strands, sections, students,
  and payment status/dates; view the dashboard and reports.
- Fully dynamic: strands, sections, students, and the six payment
  requirements all live in MySQL. Nothing is hard-coded in the PHP, so
  the admin can rename or add to any of them without touching code.
- Security: hashed passwords (`password_hash` / `password_verify`),
  PHP sessions, CSRF tokens on every admin form, prepared statements
  everywhere, and delete confirmations.

## 2. Folder structure

```
spta-portal/
├── index.php              Public homepage — strand selection
├── login.php               Admin login
├── logout.php              Admin logout
├── setup.php                One-time script that creates the admin account
├── database.sql             Full schema + starter data (strands, sections, fees)
├── config/database.php      Database connection settings (edit this if needed)
├── includes/
│   ├── auth.php              Session + "require login" helper
│   ├── functions.php          Every DB query and calculation lives here
│   ├── header.php              Shared page header / nav
│   └── footer.php               Shared page footer
├── admin/                  Login-protected admin panel
│   ├── dashboard.php, strands.php, sections.php, students.php,
│   │   payments.php, reports.php, change_password.php
├── student/                Public, read-only pages
│   ├── sections.php, students.php, payment_record.php
├── css/style.css            All styling (no external fonts/CDNs)
└── js/script.js              Nav toggle, delete confirms, dropdown logic
```

> **Note on the structure:** the brief's example folder listed a
> `student/strands.php`. Strand selection is the natural homepage, so
> that page is `index.php` at the project root instead — one less
> click for every visitor, and admin/student pages still mirror each
> other 1:1 everywhere else.

---

## 3. Setup — step by step

### Step 1 — Install the required software

You only need to install **one thing**: [XAMPP](https://www.apachefriends.org/).
XAMPP bundles Apache, PHP, and MySQL together — you do **not** need to
install PHP or MySQL separately. phpMyAdmin is also included
automatically. A code editor isn't required to *run* the system, but
[VS Code](https://code.visualstudio.com/) is recommended if you want
to open and edit the files.

### Step 2 — Place the project folder

Copy the whole `spta-portal` folder into XAMPP's `htdocs` directory:

```
C:\xampp\htdocs\spta-portal
```

(On Mac, this is usually `/Applications/XAMPP/htdocs/spta-portal`.)

### Step 3 — Create the database

1. Open the **XAMPP Control Panel** and click **Start** next to
   **Apache** and **MySQL**.
2. Open **phpMyAdmin**: go to `http://localhost/phpmyadmin` in your
   browser.
3. Click **Import** in the top menu.
4. Click **Choose File**, select `database.sql` from the project
   folder, then click **Go** at the bottom. This creates the
   `spta_payment_monitoring` database with all six tables and the
   starter strands/sections/fees already filled in.

**Step 3b — create the admin account.** Open this URL once in your
browser:

```
http://localhost/spta-portal/setup.php
```

This creates the default admin login (see section 5 below) using your
own PHP installation, so the password always works correctly. You can
delete `setup.php` afterward — it also safely refuses to run a second
time once an admin account exists.

### Step 4 — Configure the database connection

Open `config/database.php`. The defaults already match a stock XAMPP
install, so most people won't need to change anything:

```php
$host     = "localhost";
$db_name  = "spta_payment_monitoring";
$username = "root";
$password = "";
```

Only edit this if your MySQL uses a different username/password.

### Step 5 — Run the system

Visit:

```
http://localhost/spta-portal/
```

That's the public homepage (strand selection). To reach the admin
panel, click **Admin Login** in the top-right corner, or go directly
to `http://localhost/spta-portal/login.php`.

---

## 4. Default admin account

| Username | Password    |
|----------|-------------|
| `admin`  | `Admin@123` |

This is created by `setup.php` (Step 3b), not hard-coded in
`database.sql` — see the note inside that file for why. **Change this
password immediately after your first login** using **Account** in
the admin nav bar (`admin/change_password.php`), which verifies your
current password with `password_verify()` before saving a new hash.

---

## 5. How each part works

**Database design.** `student_payments` only gets a row once a status
has actually been set. A student with no row yet for a given fee is
simply treated as "unpaid" by `getStudentPayments()` (a `LEFT JOIN` +
`COALESCE`). That's what makes the requirement "add a new payment
requirement later without rewriting the system" work automatically —
every student picks up the new fee as unpaid with no migration step.

**Public vs. admin.** Every file under `admin/` starts with
`requireAdminLogin()`, which redirects straight to the login page if
`$_SESSION['admin_id']` isn't set — so admin pages can't be reached by
guessing a URL. Everything under `student/` and the root pages need no
login, matching the navigation flow in the brief.

**Totals & balances.** `calculateStudentTotals()` sums the six
requirement amounts and, separately, the ones marked "paid" — the
difference is the balance. This runs fresh on every page load, so
totals are always in sync with the latest edits; nothing is
pre-computed or cached.

**Saving a payment.** `updateStudentPayment()` uses
`INSERT ... ON DUPLICATE KEY UPDATE`, so it works the first time
(no row exists yet) and every time after (row exists, gets updated) —
one query either way. The date is forced to `NULL` server-side
whenever status is "Unpaid", even if a stray date was submitted.

**Security.** Passwords: `password_hash()` / `password_verify()`,
never plain text. SQL: every query is a prepared statement with bound
parameters — no string concatenation of user input, ever. Forms: each
admin POST carries a CSRF token (`csrfToken()` / `verifyCsrfToken()`)
stored in the session and checked before anything is written.
Deletions: every delete button asks for confirmation via
`js/script.js` before the form submits.

**The strand → section dropdown** on the Add/Edit Student form
(`admin/students.php`) is plain JavaScript with no AJAX call: all
sections for all strands are embedded in the page as JSON up front,
and `initStrandSectionCascade()` just swaps which ones are shown when
you change the Strand dropdown.

**Search & filtering.** The master list (public and admin) filters by
name/Student ID via SQL `LIKE`, and by **Fully Paid** / **With
Balance** by comparing each student's calculated balance — this covers
the brief's filtering goal in a way that's actually meaningful (an
individual line item is "Paid" or "Unpaid," but a *student* is more
usefully described as fully paid or carrying a balance).

---

## 6. Scope notes & possible next steps

- The six fee amounts (₱90/₱80/₱70/₱60/₱60/₱40) live in the
  `payment_requirements` table and can be changed directly in
  phpMyAdmin. There's no dedicated admin screen for editing them yet —
  a natural next feature if the study calls for it.
- No lockout/rate-limiting on login attempts — reasonable for a
  prototype, worth adding for production use.
- Student self-login isn't in the brief (payment lookups are public/
  read-only by design), but the `students` table already has
  everything needed if that's wanted later.

---

Built with HTML, CSS, vanilla JavaScript, PHP, and MySQL — no
frameworks, no external CDNs, no build step. Every file can be opened
and read top to bottom.
