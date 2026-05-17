import React from 'react'
import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it, vi } from 'vitest'
import Login from '../pages/Login'
import { AuthContext } from '../contexts/AuthContext'

const mockLogin = vi.fn()
const mockNavigate = vi.fn()

vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom')
  return { ...actual, useNavigate: () => mockNavigate }
})

function renderLogin() {
  return render(
    <AuthContext.Provider value={{ login: mockLogin, user: null, loading: false }}>
      <MemoryRouter>
        <Login />
      </MemoryRouter>
    </AuthContext.Provider>
  )
}

describe('Login page', () => {
  it('renders email and password fields', () => {
    renderLogin()
    expect(screen.getByLabelText(/email/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/password/i)).toBeInTheDocument()
  })

  it('calls login and navigates on success', async () => {
    mockLogin.mockResolvedValueOnce({})
    renderLogin()
    await userEvent.type(screen.getByLabelText(/email/i), 'alice@example.com')
    await userEvent.type(screen.getByLabelText(/password/i), 'password123')
    await userEvent.click(screen.getByRole('button', { name: /sign in/i }))
    await waitFor(() => expect(mockLogin).toHaveBeenCalledWith({ email: 'alice@example.com', password: 'password123' }))
    await waitFor(() => expect(mockNavigate).toHaveBeenCalledWith('/'))
  })

  it('shows error message on failed login', async () => {
    mockLogin.mockRejectedValueOnce({ response: { data: { message: 'Invalid credentials' } } })
    renderLogin()
    await userEvent.type(screen.getByLabelText(/email/i), 'alice@example.com')
    await userEvent.type(screen.getByLabelText(/password/i), 'wrong')
    await userEvent.click(screen.getByRole('button', { name: /sign in/i }))
    await waitFor(() => expect(screen.getByRole('alert')).toHaveTextContent('Invalid credentials'))
  })
})
