// @vendor: nuxt-ui-templates/dashboard-vue@5ace1923f57a10b2209abcba3869008f59f32c37
export function randomInt(min: number, max: number): number {
  return Math.floor(Math.random() * (max - min + 1)) + min
}

export function randomFrom<T>(array: T[]): T {
  return array[Math.floor(Math.random() * array.length)]
}
