import { useEffect, useState } from 'react'
import { Link, Navigate, useLocation, useNavigate, useParams } from 'react-router-dom'
import { createProduct, getProduct, updateProduct } from '../api/productApi'
import ErrorMessage from '../components/ErrorMessage'
import Loading from '../components/Loading'
import { useAuth } from '../context/useAuth'
import { USER_ROLES } from '../utils/constants'

const emptyForm = {
  name: '',
  sku: '',
  stock_quantity: 0,
  reorder_level: 0,
}

function ProductFormPage() {
  const { user } = useAuth()
  const { productId } = useParams()
  const location = useLocation()
  const navigate = useNavigate()
  const [form, setForm] = useState(location.state?.product || emptyForm)
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(Boolean(productId && !location.state?.product))
  const isEditing = Boolean(productId)

  useEffect(() => {
    if (!productId || location.state?.product) return

    async function loadProduct() {
      try {
        setForm(await getProduct(productId))
      } catch {
        setErrors({ general: ['Failed to load product.'] })
      } finally {
        setLoading(false)
      }
    }

    loadProduct()
  }, [location.state, productId])

  if (user.role !== USER_ROLES.admin) {
    return <Navigate to="/products" replace />
  }

  async function handleSubmit(event) {
    event.preventDefault()
    setErrors({})
    setLoading(true)

    try {
      if (isEditing) {
        await updateProduct(productId, form)
      } else {
        await createProduct(form)
      }

      navigate('/products')
    } catch (error) {
      setErrors(error.response?.data?.errors || { general: [error.response?.data?.message || 'Failed to save product.'] })
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <header className="page-header">
        <div>
          <p className="eyebrow">Catalog</p>
          <h2>{isEditing ? 'Edit product' : 'Create product'}</h2>
        </div>
        <Link className="button-link secondary-link" to="/products">Back</Link>
      </header>

      {loading && <Loading />}
      <ErrorMessage errors={errors} />

      <form className="toolbar-form product-form" onSubmit={handleSubmit}>
        <input placeholder="Name" value={form.name} onChange={(event) => setForm({ ...form, name: event.target.value })} required />
        <input placeholder="SKU" value={form.sku} onChange={(event) => setForm({ ...form, sku: event.target.value })} required />
        <input min="0" type="number" value={form.stock_quantity} onChange={(event) => setForm({ ...form, stock_quantity: Number(event.target.value) })} required />
        <input min="0" type="number" value={form.reorder_level} onChange={(event) => setForm({ ...form, reorder_level: Number(event.target.value) })} required />
        <button disabled={loading} type="submit">{isEditing ? 'Update product' : 'Create product'}</button>
      </form>
    </>
  )
}

export default ProductFormPage
