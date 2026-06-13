import { Navigate, Route, Routes } from 'react-router-dom'
import Navbar from '../components/Navbar'
import ProtectedRoute from '../components/ProtectedRoute'
import LoginPage from '../pages/LoginPage'
import OrderCreatePage from '../pages/OrderCreatePage'
import OrdersPage from '../pages/OrdersPage'
import ProductFormPage from '../pages/ProductFormPage'
import ProductsPage from '../pages/ProductsPage'
import ReportsPage from '../pages/ReportsPage'

function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route element={<ProtectedRoute />}>
        <Route element={<Navbar />}>
          <Route index element={<Navigate to="/products" replace />} />
          <Route path="/products" element={<ProductsPage />} />
          <Route path="/products/new" element={<ProductFormPage />} />
          <Route path="/products/:productId/edit" element={<ProductFormPage />} />
          <Route path="/orders" element={<OrdersPage />} />
          <Route path="/orders/new" element={<OrderCreatePage />} />
          <Route path="/reports" element={<ReportsPage />} />
        </Route>
      </Route>
      <Route path="*" element={<Navigate to="/products" replace />} />
    </Routes>
  )
}

export default AppRoutes
