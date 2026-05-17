import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { BrowserRouter, Navigate, Outlet, Route, Routes } from 'react-router-dom'
import { AuthProvider, useAuth } from './contexts/AuthContext'

// Lazy page imports — create placeholder components for now
// (real pages will be created in Tasks 14–20)
import Login    from './pages/Login'
import Register from './pages/Register'
import Home     from './pages/Home'
import Profile  from './pages/Profile'
import Search   from './pages/Search'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: 1, staleTime: 30_000 },
  },
})

function ProtectedRoute() {
  const { user, loading } = useAuth()
  if (loading) return <div className="flex h-screen items-center justify-center text-gray-400">Loading…</div>
  if (!user)   return <Navigate to="/login" replace />
  return <Outlet />
}

function GuestRoute() {
  const { user, loading } = useAuth()
  if (loading) return null
  if (user)    return <Navigate to="/" replace />
  return <Outlet />
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
            <Route element={<GuestRoute />}>
              <Route path="/login"    element={<Login />} />
              <Route path="/register" element={<Register />} />
            </Route>
            <Route element={<ProtectedRoute />}>
              <Route path="/"              element={<Home />} />
              <Route path="/search"        element={<Search />} />
              <Route path="/:username"     element={<Profile />} />
            </Route>
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </QueryClientProvider>
  )
}
