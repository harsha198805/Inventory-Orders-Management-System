import apiClient from './client'

export async function login(credentials) {
  const response = await apiClient.post('/login', credentials)

  return response.data
}

export async function logout() {
  const response = await apiClient.post('/logout')

  return response.data
}

export async function getMe() {
  const response = await apiClient.get('/me')

  return response.data.user
}
