import { useState } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { getApiError } from '../api/client'
import ErrorMessage from '../components/ErrorMessage'
import { useAuth } from '../context/useAuth'
import { DEFAULT_LOGIN } from '../utils/constants'

function LoginPage() {
  const { isAuthenticated, login } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState(DEFAULT_LOGIN)
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(false)

  if (isAuthenticated) {
    return <Navigate to="/products" replace />
  }

  async function handleSubmit(event) {
    event.preventDefault()
    setLoading(true)
    setErrors({})

    try {
      await login(form)
      navigate('/products', { replace: true })
    } catch (error) {
      setErrors(getApiError(error))
    } finally {
      setLoading(false)
    }
  }

  return (
    <main className="auth-shell">
      <form className="login-panel" onSubmit={handleSubmit}>
        <div>
          <p className="eyebrow">Inventory Orders</p>
          <h1>Sign in</h1>
        </div>
        <label>
          Email
          <input
            value={form.email}
            onChange={(event) => setForm({ ...form, email: event.target.value })}
            type="email"
            required
          />
        </label>
        <label>
          Password
          <input
            value={form.password}
            onChange={(event) => setForm({ ...form, password: event.target.value })}
            type="password"
            required
          />
        </label>
        <button disabled={loading} type="submit">Sign in</button>
        <ErrorMessage errors={errors} />
      </form>
    </main>
  )
}

export default LoginPage
