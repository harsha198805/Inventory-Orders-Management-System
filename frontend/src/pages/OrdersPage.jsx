import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { cancelOrder, confirmOrder, getOrders } from '../api/orderApi'
import DataTable from '../components/DataTable'
import ErrorMessage from '../components/ErrorMessage'
import Loading from '../components/Loading'
import { ORDER_STATUS } from '../utils/constants'
import { formatDate } from '../utils/formatters'

function OrdersPage() {
  const [orders, setOrders] = useState([])
  const [filters, setFilters] = useState({ status: '' })
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let active = true

    getOrders(filters)
      .then((response) => {
        if (active) setOrders(response.data)
      })
      .catch(() => {
        if (active) setErrors({ general: ['Failed to load orders.'] })
      })
      .finally(() => {
        if (active) setLoading(false)
      })

    return () => {
      active = false
    }
  }, [filters])

  const refreshOrders = useCallback(async () => {
    try {
      const response = await getOrders(filters)
      setOrders(response.data)
    } catch {
      setErrors({ general: ['Failed to load orders.'] })
    }
  }, [filters])

  function handleFilter(nextFilters) {
    setFilters(nextFilters)
  }

  async function updateStatus(orderId, action) {
    setErrors({})

    try {
      if (action === 'confirm') {
        await confirmOrder(orderId)
      } else {
        await cancelOrder(orderId)
      }

      await refreshOrders()
    } catch (error) {
      setErrors(error.response?.data?.errors || { general: [error.response?.data?.message || 'Failed to update order.'] })
    }
  }

  return (
    <>
      <header className="page-header">
        <div>
          <p className="eyebrow">Fulfillment</p>
          <h2>Orders</h2>
        </div>
        <div className="filters">
          <select value={filters.status} onChange={(event) => handleFilter({ ...filters, status: event.target.value })}>
            <option value="">All statuses</option>
            <option value={ORDER_STATUS.draft}>Draft</option>
            <option value={ORDER_STATUS.confirmed}>Confirmed</option>
            <option value={ORDER_STATUS.cancelled}>Cancelled</option>
          </select>
          <Link className="button-link" to="/orders/new">Create order</Link>
        </div>
      </header>

      {loading && <Loading />}
      <ErrorMessage errors={errors} />

      <DataTable
        columns={['Order', 'Status', 'Items', 'Created', 'Actions']}
        rows={orders.map((order) => [
          order.order_number,
          <span className={`badge ${order.status}`}>{order.status}</span>,
          order.items?.reduce((sum, item) => sum + item.quantity, 0) || 0,
          formatDate(order.created_at),
          order.status === ORDER_STATUS.draft ? (
            <div className="row-actions">
              <button className="link" onClick={() => updateStatus(order.id, 'confirm')} type="button">Confirm</button>
              <button className="link" onClick={() => updateStatus(order.id, 'cancel')} type="button">Cancel</button>
            </div>
          ) : '',
        ])}
      />
    </>
  )
}

export default OrdersPage
