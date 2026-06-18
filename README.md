# 🎉 Event Management System

A complete Event Management System developed using **PHP**, **MySQL**, **HTML**, **CSS**, **Bootstrap**, and **JavaScript**. This web application helps users manage events efficiently by allowing event creation, registration, event viewing, and administration through a user-friendly interface.

---

# 📌 Project Overview

The Event Management System is a web-based application that enables organizers to create and manage events while allowing users to browse and register for events online.

The system provides an easy-to-use dashboard for administrators and a responsive frontend for users.

---

# 🚀 Features

## User Features

* User Registration
* User Login & Logout
* Browse Available Events
* View Event Details
* Register for Events
* Update User Profile
* View Registered Events
* Responsive Design

## Admin Features

* Secure Admin Login
* Dashboard Overview
* Add New Events
* Edit Events
* Delete Events
* Manage Event Categories
* Manage Users
* View Event Registrations
* Search Events
* Generate Reports

---

# 🛠 Technologies Used

### Frontend

* HTML5
* CSS3
* Bootstrap 5
* JavaScript
* jQuery

### Backend

* PHP (Core PHP)

### Database

* MySQL

### Server

* Apache Server (XAMPP/WAMP/LAMP)

---

# 📂 Project Structure

```text
event-management-system/
│
├── index.php
├── login.php
├── register.php
├── logout.php
│
├── admin/
│   ├── dashboard.php
│   ├── add-event.php
│   ├── edit-event.php
│   ├── delete-event.php
│   ├── users.php
│   └── registrations.php
│
├── user/
│   ├── profile.php
│   ├── my-events.php
│   └── event-details.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── config/
│   └── database.php
│
└── database/
    └── event_management.sql
```

---

# ⚙️ Installation Guide

## Step 1: Download Project

Clone the repository:

```bash
git clone https://github.com/yourusername/event-management-system.git
```

Or download ZIP and extract it.

---

## Step 2: Move Project

Copy the project folder into:

### XAMPP

```text
C:\xampp\htdocs\
```

### WAMP

```text
C:\wamp64\www\
```

---

## Step 3: Create Database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Create a database:

```sql
event_management
```

---

## Step 4: Import SQL File

Import:

```text
database/event_management.sql
```

into phpMyAdmin.

---

## Step 5: Configure Database

Open:

```php
config/database.php
```

Update database credentials:

```php
<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "event_management";

$conn = mysqli_connect($host, $user, $password, $database);

if(!$conn){
    die("Database Connection Failed");
}

?>
```

---

## Step 6: Run Project

Start:

* Apache
* MySQL

Open browser:

```text
http://localhost/event-management-system
```

---

# 🗄 Database Tables

## Users Table

| Field      | Type      |
| ---------- | --------- |
| id         | INT       |
| name       | VARCHAR   |
| email      | VARCHAR   |
| password   | VARCHAR   |
| created_at | TIMESTAMP |

---

## Events Table

| Field       | Type      |
| ----------- | --------- |
| id          | INT       |
| title       | VARCHAR   |
| description | TEXT      |
| venue       | VARCHAR   |
| event_date  | DATE      |
| category    | VARCHAR   |
| image       | VARCHAR   |
| created_at  | TIMESTAMP |

---

## Registrations Table

| Field             | Type      |
| ----------------- | --------- |
| id                | INT       |
| user_id           | INT       |
| event_id          | INT       |
| registration_date | TIMESTAMP |

---

# 🔒 Security Features

* Password Hashing
* Session Management
* SQL Injection Prevention
* Form Validation
* Authentication & Authorization
* Input Sanitization

---

# 📱 Responsive Design

The system is fully responsive and compatible with:

* Desktop
* Laptop
* Tablet
* Mobile Devices

---

# 📈 Future Enhancements

* Online Payment Gateway
* Email Notifications
* QR Code Event Tickets
* Event Analytics Dashboard
* Attendance Tracking
* Certificate Generation
* SMS Notifications

---

# 👨‍💻 Author

Developed By:

**Imdadul Haque**

MCA Graduate | PHP Developer | Web Developer | Future AI Innovator

---

# 📄 License

This project is developed for educational and learning purposes.

Free to use and modify.
