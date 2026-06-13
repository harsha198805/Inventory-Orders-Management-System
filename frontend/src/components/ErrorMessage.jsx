import { flattenErrors } from '../utils/formatters'

function ErrorMessage({ errors }) {
  const messages = flattenErrors(errors)

  if (messages.length === 0) return null

  return (
    <ul className="errors">
      {messages.map((error, index) => <li key={index}>{error}</li>)}
    </ul>
  )
}

export default ErrorMessage
