import apiClient from './client'

export async function getLowStockReport() {
  const response = await apiClient.get('/reports/low-stock')

  return response.data.data
}

export async function getDailyOrdersReport() {
  const response = await apiClient.get('/reports/daily-orders')

  return response.data.data
}

export async function downloadReportExcel(reportType) {
  const endpoint = reportType === 'daily-orders'
    ? '/reports/daily-orders/export'
    : '/reports/low-stock/export'
  const filename = reportType === 'daily-orders'
    ? 'daily-orders-report.xlsx'
    : 'low-stock-report.xlsx'

  const response = await apiClient.get(endpoint, { responseType: 'blob' })
  const url = URL.createObjectURL(new Blob([response.data], {
    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  }))
  const link = document.createElement('a')

  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  link.remove()
  URL.revokeObjectURL(url)
}
