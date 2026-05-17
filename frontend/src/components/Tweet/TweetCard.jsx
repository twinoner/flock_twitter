import { useMutation, useQueryClient } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { likeTweet, unlikeTweet } from '../../api/likes'
import { deleteTweet } from '../../api/tweets'
import { useAuth } from '../../contexts/AuthContext'

export default function TweetCard({ tweet }) {
  const { user } = useAuth()
  const qc = useQueryClient()

  const invalidate = () => qc.invalidateQueries({ queryKey: ['timeline'] })

  const { mutate: toggleLike } = useMutation({
    mutationFn: tweet.liked_by_auth_user ? () => unlikeTweet(tweet.id) : () => likeTweet(tweet.id),
    onSuccess: invalidate,
  })

  const { mutate: remove } = useMutation({
    mutationFn: () => deleteTweet(tweet.id),
    onSuccess: invalidate,
  })

  const time = new Date(tweet.created_at).toLocaleDateString('en-US', {
    month: 'short', day: 'numeric',
  })

  return (
    <article className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <div className="flex gap-3">
        <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-600">
          {tweet.user.name[0].toUpperCase()}
        </div>
        <div className="min-w-0 flex-1">
          <div className="flex items-baseline justify-between gap-2">
            <div className="flex items-baseline gap-1 truncate">
              <Link to={`/${tweet.user.username}`} className="font-semibold text-gray-900 hover:underline">
                {tweet.user.name}
              </Link>
              <span className="text-sm text-gray-500">@{tweet.user.username}</span>
            </div>
            <span className="flex-shrink-0 text-xs text-gray-400">{time}</span>
          </div>
          <p className="mt-1 whitespace-pre-wrap break-words text-sm text-gray-800">{tweet.body}</p>
          <div className="mt-3 flex items-center gap-4">
            <button
              onClick={() => toggleLike()}
              className={`flex items-center gap-1 text-sm transition-colors ${
                tweet.liked_by_auth_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'
              }`}
            >
              {tweet.liked_by_auth_user ? '❤️' : '🤍'} {tweet.likes_count}
            </button>
            {user?.id === tweet.user_id && (
              <button
                onClick={() => remove()}
                className="text-sm text-gray-400 hover:text-red-500"
              >
                Delete
              </button>
            )}
          </div>
        </div>
      </div>
    </article>
  )
}
