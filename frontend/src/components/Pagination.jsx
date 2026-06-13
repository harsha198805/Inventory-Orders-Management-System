function Pagination({ meta, onPageChange }) {
  if (!meta || meta.last_page <= 1) return null

  return (
    <div className="pagination">
      <button
        className="secondary"
        disabled={meta.current_page <= 1}
        onClick={() => onPageChange(meta.current_page - 1)}
        type="button"
      >
        Previous
      </button>
      <span>Page {meta.current_page} of {meta.last_page}</span>
      <button
        className="secondary"
        disabled={meta.current_page >= meta.last_page}
        onClick={() => onPageChange(meta.current_page + 1)}
        type="button"
      >
        Next
      </button>
    </div>
  )
}

export default Pagination
