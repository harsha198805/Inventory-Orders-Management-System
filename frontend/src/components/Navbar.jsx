import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/useAuth'

function Navbar() {
  const { logout, user } = useAuth()
  const navigate = useNavigate()

  async function handleLogout() {
    await logout()
    navigate('/login', { replace: true })
  }

  return (
    <main className="app-shell">
      <aside className="sidebar">
        <div>
          <p className="eyebrow">ERP Slice</p>
          <h1>Inventory</h1>
          <p className="muted">{user.name} - {user.role}</p>
        </div>
        <nav>
          <NavLink to="/products">Products</NavLink>
          <NavLink to="/orders">Orders</NavLink>
          <NavLink to="/reports">Reports</NavLink>
        </nav>
        <button className="secondary" onClick={handleLogout} type="button">Logout</button>
      </aside>

      <section className="workspace">
        <Outlet />
      </section>
    </main>
  )
}

export default Navbar
