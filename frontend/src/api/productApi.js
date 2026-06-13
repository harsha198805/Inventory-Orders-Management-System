import apiClient from './client'

export async function getProducts(filters = {}) {
  const response = await apiClient.get('/products', {
    params: {
      search: filters.search || undefined,
      low_stock: filters.low_stock ? 1 : undefined,
      page: filters.page || undefined,
      per_page: filters.per_page || 50,
    },
  })

  return response.data
}

export async function getProduct(productId) {
  const response = await apiClient.get(`/products/${productId}`)

  return response.data.data
}

export async function createProduct(data) {
  const response = await apiClient.post('/products', data)

  return response.data.data
}

export async function updateProduct(productId, data) {
  const response = await apiClient.put(`/products/${productId}`, data)

  return response.data.data
}

export async function deleteProduct(productId) {
  await apiClient.delete(`/products/${productId}`)
}
