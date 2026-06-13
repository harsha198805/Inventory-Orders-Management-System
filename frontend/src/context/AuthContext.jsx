import { useCallback, useMemo, useState } from 'react'
import { login as loginRequest, logout as logoutRequest } from '../api/authApi'
import AuthContext from './authContext'

export function AuthProvider({ children }) {
  const [token, setToken] = useState(localStorage.getItem('token') || '')
  const [user, setUser] = useState(() => {
    const stored = localStorage.getItem('user')
    return stored ? JSON.parse(stored) : null
  })

  const login = useCallback(async (credentials) => {
    const data = await loginRequest(credentials)

    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
    setToken(data.token)
    setUser(data.user)

    return data.user
  }, [])

  const logout = useCallback(async () => {
    if (token) {
      await logoutRequest()
    }

    localStorage.removeItem('token')
    localStorage.removeItem('user')
    setToken('')
    setUser(null)
  }, [token])

  const value = useMemo(() => ({
    isAuthenticated: Boolean(token && user),
    login,
    logout,
    token,
    user,
  }), [login, logout, token, user])

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
