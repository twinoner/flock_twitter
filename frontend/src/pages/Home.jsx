import { useInfiniteQuery } from '@tanstack/react-query'
import { useEffect, useRef } from 'react'
import { getTimeline } from '../api/tweets'
import TweetForm from '../components/Tweet/TweetForm'
import TweetCard from '../components/Tweet/TweetCard'

export default function Home() {
  const loaderRef = useRef(null)

  const { data, fetchNextPage, hasNextPage, isFetchingNextPage, status } = useInfiniteQuery({
    queryKey: ['timeline'],
    queryFn: ({ pageParam }) => getTimeline({ pageParam }),
    getNextPageParam: (lastPage) => lastPage.next_cursor ?? undefined,
    initialPageParam: null,
  })

  useEffect(() => {
    const el = loaderRef.current
    if (!el) return
    const observer = new IntersectionObserver(
      (entries) => { if (entries[0].isIntersecting && hasNextPage) fetchNextPage() },
      { threshold: 0.1 }
    )
    observer.observe(el)
    return () => observer.disconnect()
  }, [hasNextPage, fetchNextPage])

  const tweets = data?.pages.flatMap((p) => p.data) ?? []

  return (
    <div className="space-y-4">
      <TweetForm />
      {status === 'pending' && (
        <p className="text-center text-sm text-gray-400">Loading timeline…</p>
      )}
      {status === 'success' && tweets.length === 0 && (
        <p className="rounded-xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
          Follow some users to see their tweets here.
        </p>
      )}
      {tweets.map((tweet) => (
        <TweetCard key={tweet.id} tweet={tweet} />
      ))}
      <div ref={loaderRef} className="h-8 flex items-center justify-center">
        {isFetchingNextPage && <span className="text-xs text-gray-400">Loading more…</span>}
      </div>
    </div>
  )
}
