# Rent Payment Management System

This Rent Payment Management System allows property managers to track tenants, house numbers, monthly rent, and payment details. It supports both **automated M-Pesa Paybill updates** and **manual rent entry** with detailed reporting and tenant tracking.

## Features

- Tenant registration and management
- Monthly rent and utility charge tracking (water, electricity, garbage)
- Payment history with M-Pesa transaction linking
- Manual rent payment option with selectable tenant and house number
- Monthly report overview
- Responsive admin dashboard

## Technologies Used

- PHP
- MySQL (Database)
- HTML/CSS (Frontend)
- M-Pesa Integration (Simulated or via API)

---

## Database Structure

### `tenants`
Stores tenant information including full name, national ID, contact details, house number, and status.

### `rent_charges`
Tracks monthly rent and associated utility bills, along with total due and payment status.

### `payments`
Records tenant payments, supporting both automated and manual entry, and links payments to tenants and transactions.

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

---

## Setup Instructions

1. Clone or download this repository.
2. Import the database SQL file into your MySQL database.
3. Configure database connection in your `db.php` file.
4. Place your project in `htdocs` if using XAMPP.
5. Ensure the `screenshots/` folder contains the named images for reference.
6. Access the system via `http://localhost/your-project-folder/`.

---

## License

This project is open-source and free to use for educational or personal rental management purposes.
