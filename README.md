# 🗂️ NEU Library Visitor Log System

A web-based visitor logging system built for the **New Era University Library**. Designed to replace traditional paper logbooks with a fast, digital, and organized solution.

---

## 🌐 Live App

🔗 [https://neulibraryapp.infinityfreeapp.com](https://neulibraryapp.infinityfreeapp.com)

---

## 💡 About

Managing library foot traffic manually is slow and messy. This system solves that by letting students, staff, and faculty log their visits digitally — either through their NEU Google account or their RFID/Student ID. Admins get a real-time dashboard to monitor everything from visit counts to individual records.

---

## ⚡ Features

### 👤 Visitors
- Sign in via **NEU Google Account** (`@neu.edu.ph`) or **RFID/Student ID**
- Select reason for visit, program, and visitor type
- Returning visitors are recognized automatically — just pick a reason and go
- Welcome screen displayed after every successful log-in
- **QR Code generation** for quick visitor identification

### 🔐 Admins
- Secure admin dashboard to monitor all visitor logs
- View real-time visit counts and individual records
- Export and manage visitor data easily

---

## 🛠️ Technologies Used

| Technology | Purpose |
|---|---|
| PHP | Backend server-side logic |
| MySQL | Database for visitor records |
| Google OAuth 2.0 | NEU Google account sign-in |
| JavaScript | Frontend interactivity |
| HTML / CSS | UI and layout |

---

## 🗄️ Database Setup

1. Import `database.sql` into your MySQL server
2. Configure `db.php` with your database credentials:
```php
define('DB_HOST', 'your_host');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database_name');
```

---

## 🚀 How to Run Locally

1. Install [XAMPP](https://www.apachefriends.org/)
2. Clone or download this repository into `C:\xampp\htdocs\NEULibproject\`
3. Import `database.sql` into phpMyAdmin
4. Start Apache and MySQL in XAMPP
5. Open your browser and go to `http://localhost/NEULibproject`

---

## 👥 Developer

- 👩‍💻 **Angel Grace Jordan**

---

## 📄 License

This project was made for academic purposes at **New Era University**.
