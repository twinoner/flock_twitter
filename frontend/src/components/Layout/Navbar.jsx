import { Link } from 'react-router-dom'
import { useAuth } from '../../contexts/AuthContext'

export default function Navbar() {
  const { user, logout } = useAuth()

  return (
    <nav className="sticky top-0 z-10 border-b border-gray-200 bg-white">
      <div className="mx-auto flex h-14 max-w-5xl items-center justify-between px-4">
        <Link to="/" className="text-xl font-bold text-blue-500">🐦 Flock</Link>
        <div className="flex items-center gap-3">
          <Link to="/search" className="text-sm text-gray-600 hover:text-gray-900">Search</Link>
          <Link to={`/${user?.username}`} className="text-sm font-medium text-gray-800">@{user?.username}</Link>
          <button onClick={logout} className="rounded-lg bg-gray-100 px-3 py-1 text-sm text-gray-600 hover:bg-gray-200">
            Logout
          </button>
        </div>
      </div>
    </nav>
  )
}
