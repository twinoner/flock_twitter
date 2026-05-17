import { createContext, useContext, useEffect, useState } from 'react'
import * as authApi from '../api/auth'

export const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser]       = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const token = localStorage.getItem('token')
    if (!token) {
      setLoading(false)
      return
    }
    authApi.me()
      .then(setUser)
      .catch(() => localStorage.removeItem('token'))
      .finally(() => setLoading(false))
  }, [])

  const login = async (credentials) => {
    const data = await authApi.login(credentials)
    localStorage.setItem('token', data.token)
    setUser(data.user)
    return data
  }

  const register = async (credentials) => {
    const data = await authApi.register(credentials)
    localStorage.setItem('token', data.token)
    setUser(data.user)
    return data
  }

  const logout = async () => {
    await authApi.logout().catch(() => {})
    localStorage.removeItem('token')
    setUser(null)
  }

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => useContext(AuthContext)
