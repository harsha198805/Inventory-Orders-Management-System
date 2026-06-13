import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { deleteProduct, getProducts } from '../api/productApi'
import DataTable from '../components/DataTable'
import ErrorMessage from '../components/ErrorMessage'
import Loading from '../components/Loading'
import Pagination from '../components/Pagination'
import RoleGuard from '../components/RoleGuard'
import { USER_ROLES } from '../utils/constants'

function ProductsPage() {
  const [products, setProducts] = useState([])
  const [filters, setFilters] = useState({ search: '', low_stock: false, page: 1, per_page: 10 })
  const [meta, setMeta] = useState(null)
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let active = true

    getProducts(filters)
      .then((response) => {
        if (active) {
          setProducts(response.data)
          setMeta(response.meta)
        }
      })
      .catch(() => {
        if (active) setErrors({ general: ['Failed to load products.'] })
      })
      .finally(() => {
        if (active) setLoading(false)
      })

    return () => {
      active = false
    }
  }, [filters])

  const refreshProducts = useCallback(async () => {
    try {
      const response = await getProducts(filters)
      setProducts(response.data)
      setMeta(response.meta)
    } catch {
      setErrors({ general: ['Failed to load products.'] })
    }
  }, [filters])

  function handleFilter(nextFilters) {
    setFilters({ ...nextFilters, page: 1 })
  }

  function handlePageChange(page) {
    setFilters({ ...filters, page })
  }

  async function handleDelete(productId) {
    setErrors({})

    try {
      await deleteProduct(productId)
      await refreshProducts()
    } catch {
      setErrors({ general: ['Failed to delete product.'] })
    }
  }

  return (
    <>
      <header className="page-header">
        <div>
          <p className="eyebrow">Catalog</p>
          <h2>Products</h2>
        </div>
        <div className="filters">
          <input
            aria-label="Search products"
            placeholder="Search name or SKU"
            value={filters.search}
            onChange={(event) => handleFilter({ ...filters, search: event.target.value })}
          />
          <label className="check">
            <input
              checked={filters.low_stock}
              onChange={(event) => handleFilter({ ...filters, low_stock: event.target.checked })}
              type="checkbox"
            />
            Low stock
          </label>
          <RoleGuard allowedRoles={[USER_ROLES.admin]}>
            <Link className="button-link" to="/products/new">Add product</Link>
          </RoleGuard>
        </div>
      </header>

      {loading && <Loading />}
      <ErrorMessage errors={errors} />

      <DataTable
        columns={['Name', 'SKU', 'Stock', 'Reorder', 'State', 'Actions']}
        rows={products.map((product) => [
          product.name,
          product.sku,
          product.stock_quantity,
          product.reorder_level,
          <span className={product.is_low_stock ? 'badge danger' : 'badge'}>{product.is_low_stock ? 'Low' : 'OK'}</span>,
          <RoleGuard key={product.id} allowedRoles={[USER_ROLES.admin]}>
            <div className="row-actions">
              <Link className="link" to={`/products/${product.id}/edit`} state={{ product }}>Edit</Link>
              <button className="link" onClick={() => handleDelete(product.id)} type="button">Delete</button>
            </div>
          </RoleGuard>,
        ])}
      />
      <Pagination meta={meta} onPageChange={handlePageChange} />
    </>
  )
}

export default ProductsPage
