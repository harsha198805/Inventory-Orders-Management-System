import apiClient from './client'

export async function getOrders(filters = {}) {
  const response = await apiClient.get('/orders', {
    params: {
      status: filters.status || undefined,
      per_page: filters.per_page || 50,
    },
  })

  return response.data
}

export async function createOrder(items) {
  const response = await apiClient.post('/orders', { items })

  return response.data.data
}

export async function confirmOrder(orderId) {
  const response = await apiClient.post(`/orders/${orderId}/confirm`)

  return response.data.data
}

export async function cancelOrder(orderId) {
  const response = await apiClient.post(`/orders/${orderId}/cancel`)

  return response.data.data
}
