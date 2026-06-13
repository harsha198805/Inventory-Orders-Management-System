import apiClient from './client'

export async function getLowStockReport() {
  const response = await apiClient.get('/reports/low-stock')

  return response.data.data
}

export async function getDailyOrdersReport() {
  const response = await apiClient.get('/reports/daily-orders')

  return response.data.data
}
