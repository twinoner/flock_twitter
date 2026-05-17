import client from './client'

export const createTweet = (data) => client.post('/tweets', data).then((r) => r.data)
export const deleteTweet = (id) => client.delete(`/tweets/${id}`).then((r) => r.data)
export const getTimeline = ({ pageParam = null, limit = 20 }) => {
  const params = { limit }
  if (pageParam) params.cursor = pageParam
  return client.get('/timeline', { params }).then((r) => r.data)
}
