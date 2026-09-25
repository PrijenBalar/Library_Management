# Online Library Management System

A college project built with **PHP 7/8 + MySQL + HTML/CSS + jQuery/AJAX**, runs on XAMPP.

## Folder layout

```
Library Management System/
├── library/          <- the website (copy this folder to C:\xampp\htdocs\)
│   ├── admin/        <- admin module
│   ├── ajax/         <- AJAX endpoints (student side)
│   ├── assets/       <- css, js (jQuery included, works offline)
│   ├── uploads/books <- book cover pictures
│   ├── database/     <- library_db.sql (import in phpMyAdmin)
│   └── includes/     <- config.php, functions.php, header, footer
├── xampp setup/
│   └── xampp-windows-x64-8.2.12-installer.exe   <- XAMPP installer (no download needed)
├── Online_Library_Management_System_Project_Documentation.docx   <- project report (edit in Word)
├── Online_Library_Management_System_Project_Documentation.pdf    <- project report (PDF)
├── demo video/
│   └── Library Management System Demo.mp4       <- 5 min walkthrough of every feature
└── read me/
    ├── installation.txt   <- set up on a new Windows 10/11 PC
    ├── how to run.txt     <- start the server + use every feature
    └── pass.txt           <- admin / student / database login details
```

## Quick start

1. Run `xampp setup\xampp-windows-x64-8.2.12-installer.exe` and install to `C:\xampp`.
2. Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. Copy `library` to `C:\xampp\htdocs\library`.
4. Import `library/database/library_db.sql` in phpMyAdmin (`http://localhost/phpmyadmin`).
5. Open `http://localhost/library/` (student) or `http://localhost/library/admin/` (admin).

Admin login: `admin` / `Password@123` — Student demo: `SID10001` / `Student@123`

## Features

**Look:** modern dashboard design with a dark sidebar menu, a top bar with the page title and user avatar, stat cards with icon badges, rounded cards, and book cover pictures on every book list. The home page has a colourful banner with the student login.

**Admin:** dashboard, add/update/delete categories, authors and books (with cover picture upload and copies for sale), issue a book to a student (AJAX lookup by Student ID and ISBN), update return details with fine, search student by Student ID (AJAX), view student details and history, block/unblock students, manage book orders (confirm, dispatch, deliver, cancel, mark paid), change password.

**Student:** register and receive a Student ID, login, dashboard, view listed books as cover cards with live search, view issued books with issue and return date-time, update profile, change password, recover password.

**Book Store (selling module):** browse books for sale with covers and stock, add to cart with AJAX, change quantity, checkout with Cash on Delivery or Pay at Library Counter, order number and progress tracker, order history, cancel an order before dispatch. Stock is reduced when an order is placed and restored when it is cancelled.
