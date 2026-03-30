# Assignment 2 - PHP + MySQL Online Store

This project is a dynamic online store built with PHP and MySQL.

## Features

- User registration and login with hashed passwords.
- Session-based authentication with admin role handling.
- Session-based shopping cart (add, update quantity, remove, clear cart).
- Database with related tables:
  - `users`
  - `products` (linked to users via `created_by`)
  - `site_content` (linked to users via `updated_by`)
- Admin panel:
  - Manage users (toggle role, delete)
  - Manage products (add, edit, delete, activate/deactivate)
  - Manage website content blocks

## Default Admin Login

- Email: `admin@novacart.test`
- Password: `admin123`

## Setup Steps (XAMPP / WAMP)

1. Copy project folder into your web server root (e.g., `htdocs/Assignment-2`).
2. Start Apache and MySQL.
3. Open phpMyAdmin and run `setup.sql`.
4. Update DB credentials in `config/database.php` if needed.
5. Open in browser:
   - `http://localhost/Assignment-2/index.php`

## Suggested Submission

- Zip the whole `Assignment-2` folder and upload to Google Drive.
- Share a public-access link in LMS.
- Make sure your link permissions are set to "Anyone with the link can view".
