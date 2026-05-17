import React from 'react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it, vi } from 'vitest'
import TweetForm from '../components/Tweet/TweetForm'
import { AuthContext } from '../contexts/AuthContext'
import * as tweetsApi from '../api/tweets'

vi.mock('../api/tweets')

function renderForm() {
  const qc = new QueryClient({ defaultOptions: { queries: { retry: false } } })
  return render(
    <QueryClientProvider client={qc}>
      <AuthContext.Provider value={{ user: { id: 1, name: 'Alice', username: 'alice' } }}>
        <TweetForm />
      </AuthContext.Provider>
    </QueryClientProvider>
  )
}

describe('TweetForm', () => {
  it('submits a tweet and clears the field', async () => {
    tweetsApi.createTweet.mockResolvedValueOnce({ id: 1, body: 'Hello!' })
    renderForm()
    await userEvent.type(screen.getByPlaceholderText(/what's happening/i), 'Hello!')
    await userEvent.click(screen.getByRole('button', { name: /post/i }))
    await waitFor(() => expect(tweetsApi.createTweet).toHaveBeenCalledWith({ body: 'Hello!' }))
    await waitFor(() => expect(screen.getByPlaceholderText(/what's happening/i)).toHaveValue(''))
  })

  it('disables submit button when body is empty', () => {
    renderForm()
    expect(screen.getByRole('button', { name: /post/i })).toBeDisabled()
  })

  it('shows remaining character count', async () => {
    renderForm()
    await userEvent.type(screen.getByPlaceholderText(/what's happening/i), 'Hi')
    expect(screen.getByText('278')).toBeInTheDocument()
  })
})
