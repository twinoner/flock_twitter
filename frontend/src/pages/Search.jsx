import { useQuery } from '@tanstack/react-query'
import { useState } from 'react'
import { searchUsers } from '../api/users'
import UserCard from '../components/User/UserCard'

export default function Search() {
  const [q, setQ] = useState('')

  const { data, status } = useQuery({
    queryKey: ['search', q],
    queryFn: () => searchUsers(q),
    enabled: q.trim().length > 0,
  })

  return (
    <div className="space-y-4">
      <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <input
          type="search"
          value={q}
          onChange={(e) => setQ(e.target.value)}
          placeholder="Search users by name or @username…"
          className="w-full border-0 text-sm text-gray-900 placeholder-gray-400 focus:outline-none"
          autoFocus
        />
      </div>

      {status === 'pending' && q && <p className="text-center text-sm text-gray-400">Searching…</p>}

      {status === 'success' && data.data.length === 0 && (
        <p className="text-center text-sm text-gray-500">No users found for "{q}"</p>
      )}

      <div className="space-y-2">
        {(data?.data ?? []).map((user) => (
          <UserCard key={user.id} user={user} isFollowing={false} />
        ))}
      </div>
    </div>
  )
}
