# ClassPulse — Backend Setup Guide

## Folder Structure
```
ClassPulse/
├── index.html
├── student/
│   └── student.html
├── teacher/
│   ├── auth.html
│   ├── dashboard.html
│   └── js/views/overview.js
└── api/
    ├── config.php          ← DB connection + helpers
    ├── setup.sql           ← Run this ONCE to create the database
    ├── auth/
    │   ├── register.php
    │   ├── login.php
    │   ├── logout.php
    │   └── check.php
    ├── sessions/           ← Coming next
    ├── questions/          ← Coming next
    ├── answers/            ← Coming next
    └── analytics/          ← Coming next
```

---

## Step 1 — XAMPP Setup

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy the entire `ClassPulse/` folder into:
   ```
   C:\xampp\htdocs\ClassPulse\
   ```
3. Open your browser at:
   ```
   http://localhost/ClassPulse/
   ```

---

## Step 2 — Create the Database

**Option A — phpMyAdmin (easiest)**
1. Go to `http://localhost/phpmyadmin`
2. Click **Import** in the top menu
3. Choose `ClassPulse/api/setup.sql`
4. Click **Go**

**Option B — MySQL CLI**
```bash
mysql -u root -p < C:\xampp\htdocs\ClassPulse\api\setup.sql
```

---

## Step 3 — Test Auth

1. Open `http://localhost/ClassPulse/teacher/auth.html`
2. Click **Sign Up** — create a test account
3. You should be redirected to the dashboard automatically
4. Click **Log Out** — you should return to auth page
5. Log back in — dashboard should load

### Quick API test (optional)
Open browser console on the auth page and run:
```js
fetch('/ClassPulse/api/auth/check.php', { credentials: 'include' }).then(r => r.json()).then(console.log)
```
Should return `{ success: false, message: "Not authenticated." }` before login,
and `{ success: true, teacher: {...} }` after.

---

## API Reference — Auth

| Method | Endpoint | Body | Returns |
|--------|----------|------|---------|
| POST | `/api/auth/register.php` | `{ full_name, course, password, confirm_password }` | `{ success, teacher }` |
| POST | `/api/auth/login.php` | `{ full_name, password }` | `{ success, teacher }` |
| POST | `/api/auth/logout.php` | — | `{ success }` |
| GET  | `/api/auth/check.php` | — | `{ success, teacher }` or 401 |

All responses are JSON. All errors return `{ success: false, message: "..." }`.

---

## What's Next (Build Order)

| Priority | Module | Files |
|----------|--------|-------|
| ✅ Done | Auth | `api/auth/` |
| 🔜 Next | Sessions | `api/sessions/create.php`, `list.php`, `end.php` |
| 🔜 | Questions | `api/questions/save.php`, `launch.php`, `current.php` |
| 🔜 | Students | `api/students/join.php`, `list.php` |
| 🔜 | Answers | `api/answers/submit.php`, `results.php` |
| 🔜 | Analytics | `api/analytics/report.php` |
| 🔜 | AI Gen | `api/ai/generate.php` (Gemini) |
