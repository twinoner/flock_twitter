import client from './client'

export const followUser = (id) => client.post(`/users/${id}/follow`).then((r) => r.data)
export const unfollowUser = (id) => client.delete(`/users/${id}/follow`).then((r) => r.data)
