export function formatDate(value) {
  if (!value) return '-'

  return new Date(value).toLocaleDateString()
}

export function flattenErrors(errors) {
  return Object.values(errors || {}).flat()
}
