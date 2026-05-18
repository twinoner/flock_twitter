import { useQuery } from '@tanstack/react-query'
import { useState } from 'react'
import { useParams } from 'react-router-dom'
import { getFollowers, getFollowing, getUser, getUserTweets } from '../api/users'
import TweetCard from '../components/Tweet/TweetCard'
import FollowButton from '../components/User/FollowButton'
import UserCard from '../components/User/UserCard'
import { useAuth } from '../contexts/AuthContext'

export default function Profile() {
  const { username } = useParams()
  const { user: authUser } = useAuth()
  const [tab, setTab] = useState('tweets')

  const { data: profile, status } = useQuery({
    queryKey: ['user', username],
    queryFn: () => getUser(username),
  })

  const { data: followersData } = useQuery({
    queryKey: ['followers', username],
    queryFn: () => getFollowers(username),
    enabled: tab === 'followers',
  })

  const { data: followingData } = useQuery({
    queryKey: ['following', username],
    queryFn: () => getFollowing(username),
    enabled: tab === 'following',
  })

  const { data: tweetsData } = useQuery({
    queryKey: ['userTweets', username],
    queryFn: () => getUserTweets(username),
    enabled: tab === 'tweets',
  })

  if (status === 'pending') return <p className="text-center text-gray-400">Loading…</p>
  if (status === 'error') return <p className="text-center text-red-500">User not found.</p>

  const isOwnProfile = authUser?.id === profile.id
  const isFollowing = profile.is_following ?? false

  return (
    <div className="space-y-4">
      <div className="rounded-xl border border-gray-200 bg-white p-6">
        <div className="flex items-start justify-between">
          <div className="flex items-center gap-4">
            <div className="flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-2xl font-bold text-blue-600">
              {profile.name?.[0]?.toUpperCase() ?? '?'}
            </div>
            <div>
              <h1 className="text-xl font-bold text-gray-900">{profile.name}</h1>
              <p className="text-gray-500">@{profile.username}</p>
              {profile.bio && <p className="mt-1 text-sm text-gray-700">{profile.bio}</p>}
            </div>
          </div>
          {!isOwnProfile && (
            <FollowButton userId={profile.id} isFollowing={isFollowing} username={profile.username} />
          )}
        </div>
        <div className="mt-4 flex gap-6 text-sm text-gray-500">
          <button onClick={() => setTab('followers')} className="hover:text-gray-900">
            <span className="font-bold text-gray-900">{profile.followers_count}</span> Followers
          </button>
          <button onClick={() => setTab('following')} className="hover:text-gray-900">
            <span className="font-bold text-gray-900">{profile.following_count}</span> Following
          </button>
        </div>
      </div>

      <div className="flex border-b border-gray-200">
        {['tweets', 'followers', 'following'].map((t) => (
          <button
            key={t}
            onClick={() => setTab(t)}
            className={`flex-1 py-3 text-sm font-medium capitalize transition-colors ${
              tab === t ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500 hover:text-gray-800'
            }`}
          >
            {t}
          </button>
        ))}
      </div>

      <div className="space-y-2">
        {tab === 'tweets' &&
          (tweetsData?.data ?? []).map((tweet) => (
            <TweetCard key={tweet.id} tweet={tweet} />
          ))}
        {tab === 'followers' &&
          (followersData?.data ?? []).map((u) => (
            <UserCard key={u.id} user={u} isFollowing={u.is_following} />
          ))}
        {tab === 'following' &&
          (followingData?.data ?? []).map((u) => (
            <UserCard key={u.id} user={u} isFollowing={u.is_following} />
          ))}
      </div>
    </div>
  )
}
