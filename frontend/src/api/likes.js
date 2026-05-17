import client from './client'

export const likeTweet = (id) => client.post(`/tweets/${id}/like`).then((r) => r.data)
export const unlikeTweet = (id) => client.delete(`/tweets/${id}/like`).then((r) => r.data)
