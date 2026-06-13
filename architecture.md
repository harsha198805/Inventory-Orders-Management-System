# Architecture Notes

## Overview

This project has two main parts:

- `backend` - Laravel API
- `frontend` - React/Vite dashboard

I kept the backend and frontend separate because the backend should handle the important business rules, and the frontend should mainly handle the user interface. The React app can hide buttons based on the user role, but the Laravel API is still the final place where permissions are checked.

## Backend Structure

The backend follows this flow:

```text
Route -> Controller -> Request Validation -> Service -> Repository -> Model/Database
```

Controllers are kept small. They receive the request, call the service, and return the response.

Validation is handled using Form Request classes. This keeps rules like required SKU, unique SKU, stock quantity, reorder level, and order item validation out of the controller.

Services are used for business logic. For example, `OrderService` handles order confirmation, cancellation, and stock reduction.

Repositories are used for database queries such as product filters, order listing, and report queries. This makes the code easier to read when the query logic grows.

## Main Models

The main models are:

- `User`
- `Product`
- `Order`
- `OrderItem`
- `AuditLog`

Products have stock quantity and reorder level. Orders have many order items. Each order item belongs to a product.

## User Roles

There are two roles:

- Admin
- Staff

Admin users can create, edit, and delete products.

Staff users can view products, create orders, and view reports, but they cannot manage products.

This is checked in the backend using role middleware. The frontend also hides product action buttons for Staff users, but backend authorization is the main protection.

## Product Management

Products include:

- Name
- SKU
- Stock quantity
- Reorder level

SKU must be unique. Product listing supports search, low-stock filter, sorting, and pagination.

Low stock means:

```text
stock_quantity <= reorder_level
```

## Order Flow

Orders start as draft orders.

Possible status changes:

```text
Draft -> Confirmed
Draft -> Cancelled
```

When an order is confirmed, product stock is reduced. This part is done inside a database transaction so partial updates do not happen.

If one product does not have enough stock, the order confirmation fails and stock values stay unchanged.

Confirming the same order twice should not reduce stock twice. The confirmation logic is idempotent for that reason.

## Reports

The system has two reports:

- Low stock report
- Daily orders summary

Report queries are kept in `ReportRepository`.

Excel export is also available for both reports. It uses `Maatwebsite/Laravel-Excel`, and the export is generated from backend data so the downloaded file matches the API data.

## Frontend Structure

The frontend is a React/Vite app. It is split into:

- `api` - Axios API calls
- `components` - shared UI components
- `pages` - product, order, report, and login pages
- `routes` - route definitions
- `context` - auth state
- `utils` - constants and helpers

The frontend stores the logged-in user and token in local storage. Axios attaches the token to protected API requests.

## Testing

Feature tests are split by area:

- `ProductManagementTest`
- `OrderWorkflowTest`
- `ReportTest`

The tests cover role permissions, product filtering, stock reduction, insufficient stock rollback, duplicate confirmation handling, reports, and Excel export.

## Tradeoffs

Using services and repositories makes the project a little bigger than a simple CRUD app. I used them because stock updates, order status changes, filters, and reports are easier to manage when the logic is separated.

SQLite is easy for quick local testing, but MySQL is better for this project in XAMPP and for testing stock updates with row locks.

Caching product lists can improve performance, but I did not add it now because stock changes can affect product lists and low-stock reports. Correct stock behavior is more important first.
