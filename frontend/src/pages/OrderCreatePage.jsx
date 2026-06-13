import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { createOrder } from '../api/orderApi'
import { getProducts } from '../api/productApi'
import ErrorMessage from '../components/ErrorMessage'
import Loading from '../components/Loading'

function OrderCreatePage() {
  const navigate = useNavigate()
  const [products, setProducts] = useState([])
  const [items, setItems] = useState([{ product_id: '', quantity: 1 }])
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function loadProducts() {
      try {
        const response = await getProducts({ per_page: 100 })
        setProducts(response.data)
      } catch {
        setErrors({ general: ['Failed to load products.'] })
      } finally {
        setLoading(false)
      }
    }

    loadProducts()
  }, [])

  function updateItem(index, field, value) {
    setItems(items.map((item, itemIndex) => itemIndex === index ? { ...item, [field]: value } : item))
  }

  async function handleSubmit(event) {
    event.preventDefault()
    setLoading(true)
    setErrors({})

    try {
      await createOrder(items.map((item) => ({
        product_id: Number(item.product_id),
        quantity: Number(item.quantity),
      })))
      navigate('/orders')
    } catch (error) {
      setErrors(error.response?.data?.errors || { general: [error.response?.data?.message || 'Failed to create order.'] })
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <header className="page-header">
        <div>
          <p className="eyebrow">Fulfillment</p>
          <h2>Create order</h2>
        </div>
        <Link className="button-link secondary-link" to="/orders">Back</Link>
      </header>

      {loading && <Loading />}
      <ErrorMessage errors={errors} />

      <form className="order-builder" onSubmit={handleSubmit}>
        {items.map((item, index) => (
          <div className="order-line" key={index}>
            <select value={item.product_id} onChange={(event) => updateItem(index, 'product_id', event.target.value)} required>
              <option value="">Select product</option>
              {products.map((product) => (
                <option key={product.id} value={product.id}>{product.name} ({product.stock_quantity})</option>
              ))}
            </select>
            <input min="1" type="number" value={item.quantity} onChange={(event) => updateItem(index, 'quantity', event.target.value)} required />
          </div>
        ))}
        <div className="actions">
          <button className="secondary" onClick={() => setItems([...items, { product_id: '', quantity: 1 }])} type="button">Add line</button>
          <button disabled={loading} type="submit">Create draft</button>
        </div>
      </form>
    </>
  )
}

export default OrderCreatePage
