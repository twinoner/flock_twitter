import client from './client'

export const getUser = (username) => client.get(`/users/${username}`).then((r) => r.data)
export const getFollowers = (username) => client.get(`/users/${username}/followers`).then((r) => r.data)
export const getFollowing = (username) => client.get(`/users/${username}/following`).then((r) => r.data)
export const searchUsers = (q) => client.get('/users/search', { params: { q } }).then((r) => r.data)
