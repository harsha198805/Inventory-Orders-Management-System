import { useEffect, useState } from 'react'
import { getDailyOrdersReport, getLowStockReport } from '../api/reportApi'
import DataTable from '../components/DataTable'
import ErrorMessage from '../components/ErrorMessage'
import Loading from '../components/Loading'

function ReportsPage() {
  const [activeReport, setActiveReport] = useState('low-stock')
  const [lowStock, setLowStock] = useState([])
  const [dailyOrders, setDailyOrders] = useState([])
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function loadReports() {
      try {
        const [lowStockData, dailyOrdersData] = await Promise.all([
          getLowStockReport(),
          getDailyOrdersReport(),
        ])
        setLowStock(lowStockData)
        setDailyOrders(dailyOrdersData)
      } catch {
        setErrors({ general: ['Failed to load reports.'] })
      } finally {
        setLoading(false)
      }
    }

    loadReports()
  }, [])

  return (
    <>
      <header className="page-header">
        <div>
          <p className="eyebrow">Control</p>
          <h2>Reports</h2>
        </div>
        <div className="segmented">
          <button
            className={activeReport === 'low-stock' ? 'active' : ''}
            onClick={() => setActiveReport('low-stock')}
            type="button"
          >
            Low stock
          </button>
          <button
            className={activeReport === 'daily-orders' ? 'active' : ''}
            onClick={() => setActiveReport('daily-orders')}
            type="button"
          >
            Daily summary
          </button>
        </div>
      </header>

      {loading && <Loading />}
      <ErrorMessage errors={errors} />

      {activeReport === 'low-stock' && (
        <section className="report-page">
          <h3>Low stock</h3>
          <DataTable
            columns={['Product', 'SKU', 'Stock', 'Reorder']}
            rows={lowStock.map((product) => [product.name, product.sku, product.stock_quantity, product.reorder_level])}
          />
        </section>
      )}

      {activeReport === 'daily-orders' && (
        <section className="report-page">
          <h3>Daily orders</h3>
          <DataTable
            columns={['Date', 'Orders', 'Total items']}
            rows={dailyOrders.map((row) => [row.order_date, row.order_count, row.total_items])}
          />
        </section>
      )}
    </>
  )
}

export default ReportsPage
