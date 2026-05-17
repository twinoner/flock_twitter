import { Link } from 'react-router-dom'
import FollowButton from './FollowButton'
import { useAuth } from '../../contexts/AuthContext'

export default function UserCard({ user, isFollowing }) {
  const { user: authUser } = useAuth()

  return (
    <div className="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4">
      <div className="flex items-center gap-3">
        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-600">
          {user.name?.[0]?.toUpperCase() ?? '?'}
        </div>
        <div>
          <Link to={`/${user.username}`} className="font-semibold text-gray-900 hover:underline">
            {user.name}
          </Link>
          <p className="text-sm text-gray-500">@{user.username}</p>
        </div>
      </div>
      {authUser?.id !== user.id && (
        <FollowButton userId={user.id} isFollowing={isFollowing} username={user.username} />
      )}
    </div>
  )
}
