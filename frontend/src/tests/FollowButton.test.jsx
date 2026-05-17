import React from 'react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it, vi } from 'vitest'
import FollowButton from '../components/User/FollowButton'
import * as followsApi from '../api/follows'

vi.mock('../api/follows')

function renderButton(isFollowing) {
  const qc = new QueryClient({ defaultOptions: { queries: { retry: false } } })
  return render(
    <QueryClientProvider client={qc}>
      <FollowButton userId={2} isFollowing={isFollowing} username="bob" />
    </QueryClientProvider>
  )
}

describe('FollowButton', () => {
  it('shows Follow when not following', () => {
    renderButton(false)
    expect(screen.getByRole('button', { name: /follow/i })).toBeInTheDocument()
  })

  it('shows Following when following', () => {
    renderButton(true)
    expect(screen.getByRole('button', { name: /following/i })).toBeInTheDocument()
  })

  it('calls followUser when clicking Follow', async () => {
    followsApi.followUser.mockResolvedValueOnce({ following: true })
    renderButton(false)
    await userEvent.click(screen.getByRole('button', { name: /follow/i }))
    await waitFor(() => expect(followsApi.followUser).toHaveBeenCalledWith(2))
  })

  it('calls unfollowUser when clicking Following', async () => {
    followsApi.unfollowUser.mockResolvedValueOnce({ following: false })
    renderButton(true)
    await userEvent.click(screen.getByRole('button', { name: /following/i }))
    await waitFor(() => expect(followsApi.unfollowUser).toHaveBeenCalledWith(2))
  })
})
