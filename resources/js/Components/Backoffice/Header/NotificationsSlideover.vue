<!-- @vendor: nuxt-ui-templates/dashboard-vue@5ace1923f57a10b2209abcba3869008f59f32c37 -->
<script setup lang="ts">
import { useFetch, formatTimeAgo } from '@vueuse/core'
import { Link } from '@inertiajs/vue3'
import { useDashboard } from '@/Composables/useDashboard'
import type { Notification } from '@/types'

const { isNotificationsSlideoverOpen } = useDashboard()

const { data: notifications } = useFetch('https://dashboard-template.nuxt.dev/api/notifications', { initialData: [] }).json<Notification[]>()
</script>

<template>
  <USlideover
    v-model:open="isNotificationsSlideoverOpen"
    title="Notifications"
  >
    <template #body>
      <Link
        v-for="notification in notifications"
        :key="notification.id"
        :href="`/admin/inbox?id=${notification.id}`"
        class="px-3 py-2.5 rounded-md hover:bg-elevated/50 flex items-center gap-3 relative -mx-3 first:-mt-3 last:-mb-3"
      >
        <UChip
          color="error"
          :show="!!notification.unread"
          inset
        >
          <UAvatar
            v-bind="notification.sender.avatar"
            :alt="notification.sender.name"
            size="md"
          />
        </UChip>

        <div class="text-sm flex-1">
          <p class="flex items-center justify-between">
            <span class="text-highlighted font-medium">{{ notification.sender.name }}</span>

            <time
              :datetime="notification.date"
              class="text-muted text-xs"
              v-text="formatTimeAgo(new Date(notification.date))"
            />
          </p>

          <p class="text-dimmed">
            {{ notification.body }}
          </p>
        </div>
      </Link>
    </template>
  </USlideover>
</template>
