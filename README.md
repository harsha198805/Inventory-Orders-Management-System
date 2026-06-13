# Inventory Orders Management System

Mini Inventory and Orders module built as an ERP slice for product management, stock control, order processing, and reporting.

## Stack

- Backend: Laravel 12, PHP 8.2, Laravel Sanctum, PHPUnit
- Frontend: React 19, Vite, Axios, React Router
- Database: SQLite for local development by default, with Laravel migrations for portability to MySQL or PostgreSQL

## Project Structure

```text
Inventory-Orders-Management-System/
+-- backend/      # Laravel API project
+-- frontend/     # React/Vite UI project
+-- README.md
`-- architecture.md
```

## Backend Target Structure

The Laravel backend is organized around controllers, request validation, resources, services, repositories, models, and enums.

```text
backend/
+-- app/
|   +-- Http/
|   |   +-- Controllers/Api/
|   |   |   +-- AuthController.php
|   |   |   +-- ProductController.php
|   |   |   +-- OrderController.php
|   |   |   `-- ReportController.php
|   |   +-- Requests/
|   |   |   +-- Product/
|   |   |   `-- Order/
|   |   +-- Resources/
|   |   `-- Middleware/
|   +-- Models/
|   |   +-- User.php
|   |   +-- Product.php
|   |   +-- Order.php
|   |   `-- OrderItem.php
|   +-- Services/
|   |   +-- ProductService.php
|   |   +-- OrderService.php
|   |   `-- ReportService.php
|   +-- Repositories/
|   |   +-- ProductRepository.php
|   |   +-- OrderRepository.php
|   |   `-- ReportRepository.php
|   `-- Enums/
|       `-- OrderStatus.php
+-- database/
|   +-- migrations/
|   `-- seeders/
+-- routes/
|   `-- api.php
`-- tests/
    +-- Feature/
    `-- Unit/
```

## Main Features

- Authentication with Admin and Staff roles
- Admin-only product management
- Product CRUD with SKU, stock quantity, and reorder level
- Product listing with pagination and filters
- Order creation with multiple order items
- Draft, Confirmed, and Cancelled order statuses
- Stock reduction inside a database transaction when an order is confirmed
- Order listing with pagination and filters
- Low stock report
- Daily orders summary with order count and total items
- Consistent request validation and JSON error responses
- Feature tests for key business rules
- Idempotent order confirmation to avoid duplicate stock reduction
- Audit log records for order confirmation and cancellation

## Local Setup

### Requirements

- PHP 8.2 or later
- Composer
- Node.js and npm
- SQLite, MySQL, or PostgreSQL

### Backend

```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

By default the project can use `database/database.sqlite`. For MySQL or PostgreSQL, update the database values in `backend/.env` before running migrations.

### Frontend

Open a second terminal:

```bash
cd frontend
npm install
npm run dev
```

The React app runs on the Vite dev server. Configure the API base URL in the frontend environment file if needed.

Example:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

Seeded users:

```text
Admin: admin@example.com / password
Staff: staff@example.com / password
```

## Testing

Run backend tests:

```bash
cd backend
php artisan test
```

Current result:

```text
Tests: 7 passed (24 assertions)
```

Run frontend linting:

```bash
cd frontend
npm run lint
```

Build frontend:

```bash
cd frontend
npm run build
```

Recommended backend test coverage:

- Admin can create, update, and delete products
- Staff cannot manage products
- Product listing supports pagination and filters
- Confirming an order reduces stock inside a transaction
- Order confirmation fails when stock is insufficient
- Cancelled orders cannot reduce stock again
- Low stock report returns products at or below reorder level
- Daily orders summary returns count and total item quantity

## API Overview

API routes:

```text
POST   /api/login
POST   /api/logout
GET    /api/products
POST   /api/products
GET    /api/products/{product}
PUT    /api/products/{product}
DELETE /api/products/{product}
GET    /api/orders
POST   /api/orders
GET    /api/orders/{order}
POST   /api/orders/{order}/confirm
POST   /api/orders/{order}/cancel
GET    /api/reports/low-stock
GET    /api/reports/daily-orders
```

## Third-Party Libraries

- Laravel: Backend framework, routing, validation, ORM, migrations, testing
- Laravel Sanctum: API authentication
- React: Frontend UI
- Vite: Frontend build tool and dev server
- Axios: HTTP client
- React Router: Client-side routing
- PHPUnit: Backend automated tests

## Submission Notes

Deliverables expected by the assignment:

- Source code as a ZIP file or Git repository link
- Database migrations and seeders
- `README.md` with setup and run instructions
- `architecture.md` with key design decisions and tradeoffs
- Test instructions and test results
