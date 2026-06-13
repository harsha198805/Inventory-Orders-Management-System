# Architecture Notes

## Overview

This project uses a separated API and UI architecture:

- `backend/`: Laravel API responsible for authentication, authorization, validation, business rules, persistence, and reporting.
- `frontend/`: React/Vite application responsible for screens, forms, tables, filters, and API integration.

The backend is the source of truth for permissions, stock calculations, order status transitions, and reporting. The frontend should provide a clean workflow, but it should not be trusted to enforce business rules on its own.

## Backend Layers

The Laravel backend follows a layered structure:

```text
Controller -> Form Request -> Service -> Repository -> Model/Database
```

### Controllers

Controllers under `app/Http/Controllers/Api` handle HTTP-level concerns only:

- Accept validated requests
- Call the correct service method
- Return API resources or JSON responses
- Avoid direct stock, status, and reporting logic

Implemented controllers:

- `AuthController`
- `ProductController`
- `OrderController`
- `ReportController`

### Form Requests

Request classes under `app/Http/Requests` hold validation rules and authorization checks.

Examples:

- Product SKU is required and unique
- Stock quantity and reorder level must be non-negative integers
- Order items must reference existing products
- Order item quantities must be positive integers

Keeping validation in request classes prevents controllers from becoming large and makes rules easier to test.

### Services

Services contain business logic:

- `ProductService`: product create, update, delete, filtering support
- `OrderService`: order creation, confirmation, cancellation, stock reduction
- `ReportService`: low stock and daily order summaries

The most important service is `OrderService`, because order confirmation must be safe and transactional.

### Repositories

Repositories isolate database queries that are reused or likely to grow:

- `ProductRepository`: filtered product listing, SKU lookups, low-stock base query
- `OrderRepository`: filtered order listing, order item persistence, status queries
- `ReportRepository`: aggregate queries for reports

For a small assignment, repositories add some ceremony. The benefit is clearer separation between business decisions and query construction, which helps when filters, reporting, or persistence rules grow.

### Models

Core models:

- `User`
- `Product`
- `Order`
- `OrderItem`

Important relationships:

```text
User has many Orders
Order belongs to User
Order has many OrderItems
OrderItem belongs to Order
OrderItem belongs to Product
Product has many OrderItems
```

### Enums

`OrderStatus` stores the allowed order states:

```text
Draft
Confirmed
Cancelled
```

Using an enum avoids scattered string literals and makes invalid status transitions easier to prevent.

## Role-Based Access

The system supports two roles:

- Admin: can manage products and use order/report features
- Staff: can create and view orders and reports, but cannot create, update, or delete products

Authorization is enforced in the backend with role middleware and Form Request authorization. The frontend also hides restricted product actions for Staff users, but backend authorization remains the source of truth.

## Product Management

Products include:

- Name
- SKU
- Stock quantity
- Reorder level

Product listing should support:

- Pagination
- Search by name or SKU
- Filtering by low-stock state
- Sorting by common fields where needed

SKU should be unique at the database level as well as validated in the application layer.

## Order Lifecycle

Orders move through three statuses:

```text
Draft -> Confirmed
Draft -> Cancelled
```

Confirmed orders should not be confirmed again, and cancelled orders should not reduce stock.

Order confirmation must:

1. Start a database transaction.
2. Lock or safely read the selected products.
3. Check available stock for every item.
4. Reduce stock quantities.
5. Mark the order as confirmed.
6. Commit the transaction.

If any product has insufficient stock, the transaction must roll back and return a clear validation error.

## Transaction And Stock Safety

Stock updates are the highest-risk part of the system. A database transaction is required so that partial updates cannot occur.

Implemented approach:

- Use `DB::transaction()`.
- Re-read products inside the transaction.
- Use row-level locks where supported, such as `lockForUpdate()`.
- Validate all item quantities before writing stock changes.
- Keep confirmation idempotent by returning the existing confirmed order if the same order is submitted again.
- Create audit log entries for order confirmation and cancellation.

This avoids common issues such as double submission, negative stock, and partially confirmed orders.

## Reporting

Reports are read-only endpoints:

- Low stock report: products where `stock_quantity <= reorder_level`
- Daily orders summary: grouped by date, with order count and total item quantity
- Excel exports for both reports are generated by the backend with Maatwebsite/Laravel-Excel so exported data matches the API source of truth.

Report queries should live in `ReportRepository` so aggregate SQL stays out of controllers.

## Error Handling

API responses should be consistent:

- Validation errors return `422`
- Unauthorized requests return `401`
- Forbidden role access returns `403`
- Missing records return `404`
- Unexpected failures return `500` with a safe generic message

Laravel's exception handler can be used to normalize JSON responses for API routes.

## Frontend Architecture

The React app is currently implemented as a compact Vite dashboard with API integration in `App.jsx`. For a larger production version, it can be split into pages, API clients, reusable components, and route guards.

Suggested growth structure:

```text
frontend/src/
+-- api/
|   +-- client.js
|   +-- authApi.js
|   +-- productApi.js
|   +-- orderApi.js
|   `-- reportApi.js
+-- components/
|   +-- DataTable.jsx
|   +-- ErrorMessage.jsx
|   +-- Loading.jsx
|   +-- Navbar.jsx
|   +-- Pagination.jsx
|   +-- ProtectedRoute.jsx
|   `-- RoleGuard.jsx
+-- pages/
|   +-- LoginPage.jsx
|   +-- OrderCreatePage.jsx
|   +-- ProductsPage.jsx
|   +-- ProductFormPage.jsx
|   +-- OrdersPage.jsx
|   `-- ReportsPage.jsx
+-- routes/
|   `-- AppRoutes.jsx
+-- context/
|   +-- AuthContext.jsx
|   +-- authContext.js
|   `-- useAuth.js
+-- utils/
|   +-- constants.js
|   `-- formatters.js
+-- App.jsx
`-- main.jsx
```

Frontend responsibilities:

- Login and logout
- Store the authenticated user state
- Attach API tokens or session credentials
- Render role-aware navigation
- Show paginated/filterable product and order tables
- Provide order creation forms
- Display validation errors returned by Laravel
- Show reports in simple tables or summary cards

## Testing Strategy

At least five meaningful backend tests should be included. The strongest coverage should focus on business rules:

- Admin product management succeeds
- Staff product management is forbidden
- Order creation stores order items correctly
- Order confirmation reduces product stock
- Order confirmation rolls back when stock is insufficient
- Confirmed orders cannot be confirmed twice
- Reports return correct low-stock and daily summary data

Unit tests are useful for service-level status rules. Feature tests are better for authentication, authorization, validation, and database behavior.

## Performance Considerations

- Use pagination for product and order lists.
- Add indexes for SKU, order status, order date, and foreign keys.
- Use eager loading for order items and products to avoid N+1 queries.
- Cache product list responses only when filters are stable and invalidation is handled after product or stock changes.
- Keep report queries aggregated in SQL rather than calculating totals in PHP after loading many rows.

## Security Considerations

- Use Laravel Sanctum for API authentication.
- Hash passwords with Laravel's default password hashing.
- Enforce roles in the backend.
- Validate all request data with Form Requests.
- Do not trust client-provided totals; calculate totals and stock changes on the backend.
- Avoid exposing stack traces or internal exception details in production.

## Tradeoffs

The service and repository layers make the codebase slightly larger than a simple CRUD-only Laravel app. For this assignment, the added structure is justified because stock confirmation, status transitions, filters, and reports are business rules that benefit from clear boundaries.

SQLite is convenient for quick local setup, but MySQL or PostgreSQL is preferable for production-like concurrency testing, especially when validating row locks during stock updates.

Caching the product list can improve read performance, but it adds invalidation complexity because stock changes during order confirmation can affect product list and low-stock results. For correctness, caching should be added only after the core transaction flow is reliable.
