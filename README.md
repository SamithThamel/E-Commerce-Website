# 🛒 NovaCart — PHP + MySQL E-Commerce Website

A fully functional e-commerce web application built with vanilla PHP and MySQL, featuring user authentication, a session-based shopping cart, and a complete admin management panel.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Features](#features)
- [Project Structure](#project-structure)
- [Prerequisites](#prerequisites)
- [Installation & Setup](#installation--setup)
- [Database Schema](#database-schema)
- [Default Credentials](#default-credentials)
- [Usage](#usage)
- [Security](#security)

---

## Overview

NovaCart is a lightweight PHP-based online store that lets customers browse products and manage a shopping cart, while admins can manage users, products, and dynamic site content — all without any external framework.

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Backend    | PHP 7.0+                          |
| Database   | MySQL / MariaDB                   |
| Frontend   | HTML5, inline CSS                 |
| Auth       | Session-based + bcrypt passwords  |
| Web Server | Apache (XAMPP / WAMP)             |

---

## Features

### 👤 Customer Features
- **User Registration** — Create an account with name, email, and password (minimum 6 characters; duplicate email prevention).
- **User Login / Logout** — Secure session-based authentication with bcrypt password verification.
- **User Dashboard** — View account details and role information.
- **Product Browsing** — Browse all active products with names, descriptions, prices, and stock levels.
- **Shopping Cart** — Session-based cart with support for adding items, updating quantities, removing individual items, and clearing the entire cart.

### 🔧 Admin Features
- **Admin Dashboard** — View site-wide statistics (total users, products, and content blocks).
- **User Management** — List all users, toggle roles between `customer` and `admin`, and delete accounts.
- **Product Management** — Add new products, edit existing ones (name, description, price, stock, image URL), activate/deactivate listings, and delete products.
- **Content Management** — Create, edit, and delete dynamic website content blocks (e.g., homepage hero sections).

---

## Project Structure

```
E-Commerce-Website/
├── config/
│   └── database.php          # Database connection (MySQLi)
├── includes/
│   ├── session.php           # Session initialization
│   ├── auth.php              # Authentication helpers (isLoggedIn, isAdmin, etc.)
│   ├── header.php            # Shared HTML header / navigation
│   └── footer.php            # Shared HTML footer
├── index.php                 # Home page — product listing
├── login.php                 # Login page
├── register.php              # Registration page
├── logout.php                # Logout handler
├── dashboard.php             # User dashboard (protected)
├── cart.php                  # Shopping cart (protected)
├── admin.php                 # Admin dashboard (admin only)
├── admin_users.php           # User management panel (admin only)
├── admin_products.php        # Product management panel (admin only)
├── admin_content.php         # Content management panel (admin only)
├── setup.sql                 # Database schema + seed data
└── fix_admin.sql             # Script to reset the admin account
```

---

## Prerequisites

- [XAMPP](https://www.apachefriends.org/) or [WAMP](https://www.wampserver.com/) (Apache + MySQL + PHP)
- PHP **7.0** or higher
- MySQL / MariaDB

---

## Installation & Setup

### 1. Copy project to web root

Place the project folder inside your server's document root:

```
# XAMPP (Windows)
C:\xampp\htdocs\Assignment-2\

# XAMPP (macOS)
/Applications/XAMPP/htdocs/Assignment-2/

# WAMP (Windows)
C:\wamp64\www\Assignment-2\
```

### 2. Start services

Launch **XAMPP Control Panel** (or WAMP) and start both **Apache** and **MySQL**.

### 3. Create the database

Open **phpMyAdmin** at `http://localhost/phpmyadmin`, then:

1. Click **Import** in the top menu.
2. Select `setup.sql` from the project folder and click **Go**.

This will create the `novacart` database with all tables and seed data (including the default admin account).

### 4. Configure the database connection

Open (or create) `config/database.php` and update the credentials to match your environment:

```php
<?php
$host     = 'localhost';
$dbname   = 'novacart';
$username = 'root';    // your MySQL username
$password = '';        // your MySQL password

$connection = new mysqli($host, $username, $password, $dbname);

if ($connection->connect_error) {
    die('Database connection failed: ' . $connection->connect_error);
}
```

### 5. Open in browser

```
http://localhost/Assignment-2/index.php
```

---

## Database Schema

### `users`

| Column          | Type                        | Notes                     |
|-----------------|-----------------------------|---------------------------|
| `id`            | INT (PK, auto-increment)    |                           |
| `name`          | VARCHAR(100)                |                           |
| `email`         | VARCHAR(150), UNIQUE        |                           |
| `password_hash` | VARCHAR(255)                | bcrypt hashed             |
| `role`          | ENUM('customer', 'admin')   | Default: `customer`       |
| `created_at`    | TIMESTAMP                   | Auto-set on insert        |

### `products`

| Column        | Type                     | Notes                          |
|---------------|--------------------------|--------------------------------|
| `id`          | INT (PK, auto-increment) |                                |
| `name`        | VARCHAR(200)             |                                |
| `description` | TEXT                     |                                |
| `image_url`   | VARCHAR(500)             |                                |
| `price`       | DECIMAL(10, 2)           |                                |
| `stock`       | INT                      |                                |
| `is_active`   | TINYINT(1)               | 1 = visible, 0 = hidden        |
| `created_by`  | INT (FK → users.id)      |                                |
| `created_at`  | TIMESTAMP                |                                |
| `updated_at`  | TIMESTAMP                |                                |

### `site_content`

| Column        | Type                     | Notes                          |
|---------------|--------------------------|--------------------------------|
| `id`          | INT (PK, auto-increment) |                                |
| `content_key` | VARCHAR(100), UNIQUE     | Identifier, e.g. `home_hero`  |
| `title`       | VARCHAR(255)             |                                |
| `body`        | TEXT                     |                                |
| `updated_by`  | INT (FK → users.id)      |                                |
| `updated_at`  | TIMESTAMP                |                                |

---

## Default Credentials

> ⚠️ Change these credentials immediately in a production environment.

| Role  | Email                  | Password  |
|-------|------------------------|-----------|
| Admin | `admin@novacart.test`  | `admin123` |

To reset the admin account, run `fix_admin.sql` in phpMyAdmin.

---

## Usage

| URL                                        | Description                         | Access        |
|--------------------------------------------|-------------------------------------|---------------|
| `/index.php`                               | Home — browse products              | Public        |
| `/register.php`                            | Create a new account                | Public        |
| `/login.php`                               | Log in                              | Public        |
| `/logout.php`                              | End session and redirect to login   | Authenticated |
| `/dashboard.php`                           | User account dashboard              | Authenticated |
| `/cart.php`                                | View and manage shopping cart       | Authenticated |
| `/admin.php`                               | Admin overview                      | Admin only    |
| `/admin_users.php`                         | Manage users                        | Admin only    |
| `/admin_products.php`                      | Manage products                     | Admin only    |
| `/admin_content.php`                       | Manage site content                 | Admin only    |

---

## Security

- **Passwords** are hashed using PHP's `password_hash()` with `PASSWORD_DEFAULT` (bcrypt).
- **SQL Injection** is prevented via prepared statements (`mysqli`).
- **XSS** is mitigated by escaping all output with `htmlspecialchars()`.
- **Role-based access control** restricts admin pages to users with the `admin` role.
- Unauthenticated users are redirected to the login page when accessing protected routes.

---

*Built with PHP + MySQL — no external frameworks required.*
