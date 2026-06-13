# Inventory Orders Management System - Backend

Laravel API backend for a mini inventory and order management system. This backend handles authentication, role-based authorization, product stock management, order processing, and reports for the React frontend.

## Features

- Laravel Sanctum API authentication
- Admin and Staff user roles
- Admin-only product create, update, and delete
- Product listing with search, low-stock filter, sorting, and pagination
- SKU uniqueness validation
- Stock quantity and reorder level validation
- Order creation with multiple products
- Draft, Confirmed, and Cancelled order statuses
- Transaction-safe stock reduction when confirming orders
- Idempotent order confirmation to avoid duplicate stock reduction
- Low stock report
- Daily orders summary report
- Feature tests for core business rules

## Tech Stack

- PHP 8.2+
- Laravel 12
- Laravel Sanctum
- Eloquent ORM
- MySQL or SQLite
- PHPUnit

## Requirements

- PHP 8.2 or later
- Composer
- MySQL through XAMPP, or SQLite for simple local setup

## Installation

From the project root:

```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
```

For XAMPP MySQL, update `backend/.env` like this:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_erp
DB_USERNAME=root
DB_PASSWORD=
```

Create the `laravel_erp` database in phpMyAdmin before running migrations.

Then run:

```bash
php artisan migrate --seed
```

## Run Backend

```bash
php artisan serve
```

Default backend URL:

```text
http://127.0.0.1:8000
```

API base URL:

```text
http://127.0.0.1:8000/api
```

## Seeded Login Users

```text
Admin: admin@example.com / 12345678
Staff: staff@example.com / 12345678
```

Admin users can manage products. Staff users can view products, create orders, and view reports, but cannot create, edit, or delete products.

## API Endpoints

### Authentication

```text
POST /api/login
GET  /api/me
POST /api/logout
```

### Products

```text
GET    /api/products
POST   /api/products
GET    /api/products/{product}
PUT    /api/products/{product}
DELETE /api/products/{product}
```

Product create, update, and delete routes require the `admin` role.

Supported product listing query parameters:

```text
search
low_stock
sort_by
sort_dir
page
per_page
```

### Orders

```text
GET  /api/orders
POST /api/orders
GET  /api/orders/{order}
POST /api/orders/{order}/confirm
POST /api/orders/{order}/cancel
```

### Reports

```text
GET /api/reports/low-stock
GET /api/reports/daily-orders
```

## Example Login Request

```bash
curl -X POST http://127.0.0.1:8000/api/login ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"admin@example.com\",\"password\":\"12345678\"}"
```

Use the returned token as a Bearer token for protected API requests.

## Backend Structure

```text
backend/
+-- app/
|   +-- Http/
|   |   +-- Controllers/Api/
|   |   +-- Requests/
|   |   +-- Resources/
|   |   `-- Middleware/
|   +-- Models/
|   +-- Services/
|   +-- Repositories/
|   `-- Enums/
+-- database/
|   +-- migrations/
|   `-- seeders/
+-- routes/
|   `-- api.php
`-- tests/
    `-- Feature/
```

## Business Rules

- Product SKU must be unique.
- Stock quantity and reorder level must be non-negative integers.
- Only Admin users can create, update, or delete products.
- Orders are created as drafts.
- Confirming an order reduces product stock inside a database transaction.
- If stock is insufficient, the confirmation fails and stock remains unchanged.
- Confirming an already confirmed order does not reduce stock again.
- Cancelled orders cannot reduce stock.
- Low stock means `stock_quantity <= reorder_level`.

## Run Tests

```bash
php artisan test
```

Current feature coverage includes:

- Admin can create products
- Staff cannot create products
- Product search and low-stock filtering
- Order confirmation reduces stock
- Insufficient stock rolls back confirmation
- Re-confirming an order is idempotent
- Low-stock and daily-order reports

## Useful Commands

```bash
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
php artisan serve
```

## Frontend Connection

The React frontend should use this API base URL:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

Run the frontend from the project root in a second terminal:

```bash
cd frontend
npm install
npm run dev
```
