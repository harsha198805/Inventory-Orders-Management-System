# Inventory Orders Management System - Frontend

React/Vite frontend for the Inventory Orders Management System. This app provides the dashboard UI for product management, order processing, and inventory reports while using the Laravel backend API as the source of truth.

## Features

- Login and logout with Laravel Sanctum API tokens
- Admin and Staff role-aware UI
- Product listing with search, low-stock filter, and pagination
- Admin-only product add, edit, and delete controls
- Product form for SKU, stock quantity, and reorder level
- Order listing with status actions
- Create draft orders with multiple product lines
- Low stock report
- Excel report export controls
- Daily orders summary report
- API validation error display
- Responsive dashboard layout

## Tech Stack

- React 19
- Vite
- Axios
- React Router
- ESLint

## Requirements

- Node.js
- npm
- Running Laravel backend API

Backend API default URL:

```text
http://127.0.0.1:8000/api
```

## Installation

From the project root:

```bash
cd frontend
npm install
```

## Environment Setup

Create a `.env` file inside `frontend/` if you need to customize the backend API URL:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

If this value is not provided, the app uses:

```text
http://127.0.0.1:8000/api
```

## Run Frontend

Start the Vite development server:

```bash
npm run dev
```

Default frontend URL:

```text
http://localhost:5173
```

## Login Users

Use the seeded backend users:

```text
Admin: admin@example.com / 12345678
Staff: staff@example.com / 12345678
```

Admin users can add, edit, and delete products. Staff users can view products, create orders, and view reports, but product management actions are hidden.

## Available Pages

```text
/login
/products
/products/new
/products/:productId/edit
/orders
/orders/new
/reports
```

## Frontend Structure

```text
frontend/src/
+-- api/
|   +-- authApi.js
|   +-- client.js
|   +-- orderApi.js
|   +-- productApi.js
|   `-- reportApi.js
+-- components/
|   +-- DataTable.jsx
|   +-- ErrorMessage.jsx
|   +-- Loading.jsx
|   +-- Navbar.jsx
|   +-- Pagination.jsx
|   +-- ProtectedRoute.jsx
|   `-- RoleGuard.jsx
+-- context/
|   +-- AuthContext.jsx
|   +-- authContext.js
|   `-- useAuth.js
+-- pages/
|   +-- LoginPage.jsx
|   +-- OrderCreatePage.jsx
|   +-- OrdersPage.jsx
|   +-- ProductFormPage.jsx
|   +-- ProductsPage.jsx
|   `-- ReportsPage.jsx
+-- routes/
|   `-- AppRoutes.jsx
+-- utils/
|   +-- constants.js
|   `-- formatters.js
+-- App.jsx
`-- main.jsx
```

## Useful Commands

Run development server:

```bash
npm run dev
```

Run linting:

```bash
npm run lint
```

Build production assets:

```bash
npm run build
```

Preview production build:

```bash
npm run preview
```

## Backend Connection Notes

The backend must be running before using the app:

```bash
cd backend
php artisan serve
```

The frontend stores the API token and authenticated user in `localStorage`. If role changes or login data looks stale, logout or clear browser local storage and sign in again.

## Role Handling

- `ProtectedRoute` blocks unauthenticated users.
- `RoleGuard` hides admin-only UI from non-admin users.
- Backend authorization still enforces permissions, so hidden frontend buttons are only for user experience.
