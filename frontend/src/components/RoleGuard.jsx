import { useAuth } from '../context/useAuth'

function RoleGuard({ allowedRoles, children, fallback = null }) {
  const { user } = useAuth()
  const role = user?.role?.trim().toLowerCase()

  if (!role || !allowedRoles.includes(role)) {
    return fallback
  }

  return children
}

export default RoleGuard
