import { useMutation, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { createTweet } from '../../api/tweets'
import { useAuth } from '../../contexts/AuthContext'

const MAX = 280

export default function TweetForm() {
  const { user } = useAuth()
  const [body, setBody] = useState('')
  const qc = useQueryClient()

  const { mutate, isPending } = useMutation({
    mutationFn: () => createTweet({ body }),
    onSuccess: () => {
      setBody('')
      qc.invalidateQueries({ queryKey: ['timeline'] })
    },
  })

  const remaining = MAX - body.length
  const overLimit = remaining < 0

  return (
    <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
      <div className="flex gap-3">
        <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-600">
          {user?.name[0]?.toUpperCase()}
        </div>
        <div className="flex-1">
          <textarea
            value={body}
            onChange={(e) => setBody(e.target.value)}
            placeholder="What's happening?"
            rows={3}
            className="w-full resize-none border-0 text-gray-900 placeholder-gray-400 focus:outline-none text-sm"
          />
          <div className="mt-2 flex items-center justify-between border-t border-gray-100 pt-2">
            <span className={`text-xs ${overLimit ? 'text-red-500' : 'text-gray-400'}`}>
              {remaining}
            </span>
            <button
              onClick={() => mutate()}
              disabled={isPending || body.trim() === '' || overLimit}
              className="rounded-full bg-blue-500 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-600 disabled:opacity-50"
            >
              {isPending ? 'Posting…' : 'Post'}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
