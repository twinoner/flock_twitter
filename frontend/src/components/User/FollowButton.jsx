import { useMutation, useQueryClient } from '@tanstack/react-query'
import { followUser, unfollowUser } from '../../api/follows'

export default function FollowButton({ userId, isFollowing, username }) {
  const qc = useQueryClient()
  const invalidate = () => qc.invalidateQueries({ queryKey: ['user', username] })

  const { mutate, isPending } = useMutation({
    mutationFn: (following) => following ? unfollowUser(userId) : followUser(userId),
    onSuccess: invalidate,
  })

  return (
    <button
      onClick={() => mutate(isFollowing)}
      disabled={isPending}
      className={`rounded-full px-4 py-1.5 text-sm font-semibold transition-colors disabled:opacity-50 ${
        isFollowing
          ? 'border border-gray-300 bg-white text-gray-800 hover:border-red-300 hover:text-red-500'
          : 'bg-blue-500 text-white hover:bg-blue-600'
      }`}
    >
      {isPending ? '…' : isFollowing ? 'Following' : 'Follow'}
    </button>
  )
}
