import { describe, expect, it } from 'vitest'
import client from '../api/client'

describe('api client', () => {
  it('has the correct baseURL', () => {
    expect(client.defaults.baseURL).toBe('http://localhost:8000/api')
  })

  it('sends Accept: application/json header', () => {
    expect(client.defaults.headers.Accept).toBe('application/json')
  })
})
