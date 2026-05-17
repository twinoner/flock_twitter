import { describe, expect, it } from 'vitest'

describe('test setup', () => {
  it('vitest and jsdom are configured', () => {
    expect(document.body).toBeDefined()
  })
})
