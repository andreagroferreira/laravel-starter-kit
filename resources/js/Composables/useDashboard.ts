// @vendor: nuxt-ui-templates/dashboard-vue@5ace1923f57a10b2209abcba3869008f59f32c37
import { ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { createSharedComposable } from '@vueuse/core'
import { defineShortcuts } from '@nuxt/ui/composables'

const _useDashboard = () => {
  const page = usePage()
  const isNotificationsSlideoverOpen = ref(false)

  defineShortcuts({
    'g-h': () => router.visit('/admin/dashboard'),
    'g-i': () => router.visit('/admin/inbox'),
    'g-c': () => router.visit('/admin/customers'),
    'g-s': () => router.visit('/admin/settings'),
    'n': () => isNotificationsSlideoverOpen.value = !isNotificationsSlideoverOpen.value
  })

  watch(() => page.url, () => {
    isNotificationsSlideoverOpen.value = false
  })

  return {
    isNotificationsSlideoverOpen
  }
}

export const useDashboard = createSharedComposable(_useDashboard)
