# Rent Payment Management System

This Rent Payment Management System allows property managers to track tenants, house numbers, monthly rent, and payment details. It supports both **automated M-Pesa Paybill updates** and **manual rent entry** with detailed reporting and tenant tracking.

## Features

- Tenants registration and management
- Monthly rent and utility charge tracking
- Payment history with M-Pesa transaction linking
- Manual rent payment option with selectable tenant and house number
- Easy to navigate admin interface

## Technologies Used

- PHP
- MySQL (Database)
- HTML/CSS (Frontend)
- M-Pesa Integration (Simulated/API-ready)

---

## Database Tables

### 1. `tenants`
Holds tenant details such as name, contact, house number, and account status.

### 2. `rent_charges`
Tracks rent, water, electricity, garbage fees, and payment status monthly.

### 3. `payments`
Stores records of all payments made by tenants, including M-Pesa and manual methods.

---

## Screenshots

### Dashboard View
![Dashboard](screenshots/dashboard.jpg)

### Add Tenant Page
![Add bills](screenshots/add bills.jpg)

### View Payments Page
![View bills](screenshots/bills.jpg)

### Manual Rent Payment Form
![Month report](screenshots/month report.jpg)

### M-Pesa Payment Auto-update Notification
![Tenant list](screenshots/tenant list.jpg)

### Monthly Rent Charges Overview
![add tenant payment](screenshots/add tenant payment.jpg)

---

## Setup Instructions

1. Clone or download this repository.
2. Import the SQL tables into your MySQL database.
3. Configure your database connection in `db.php`.
4. Ensure the `screenshots/` folder contains the relevant UI previews.
5. Run `viewpayments.php` on your local or hosted server (e.g., XAMPP).

---

## License

Dm for database and business