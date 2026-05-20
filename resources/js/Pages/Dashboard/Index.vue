<!-- @vendor: nuxt-ui-templates/dashboard-vue@5ace1923f57a10b2209abcba3869008f59f32c37 -->
<script setup lang="ts">
import { ref, shallowRef } from 'vue'
import { sub } from 'date-fns'
import type { DropdownMenuItem } from '@nuxt/ui'
import BackofficeLayout from '@/Layouts/BackofficeLayout.vue'
import { useDashboard } from '@/Composables/useDashboard'
import HomeDateRangePicker from '@/Components/Home/DateRangePicker.vue'
import HomePeriodSelect from '@/Components/Home/PeriodSelect.vue'
import HomeStats from '@/Components/Home/Stats.vue'
import HomeChart from '@/Components/Home/Chart.vue'
import HomeSales from '@/Components/Home/Sales.vue'
import type { Period, Range } from '@/types'

defineOptions({ layout: BackofficeLayout })

const { isNotificationsSlideoverOpen } = useDashboard()

const items = [[{
  label: 'New mail',
  icon: 'i-lucide-send',
  to: '/admin/inbox'
}, {
  label: 'New customer',
  icon: 'i-lucide-user-plus',
  to: '/admin/customers'
}]] satisfies DropdownMenuItem[][]

const range = shallowRef<Range>({
  start: sub(new Date(), { days: 14 }),
  end: new Date()
})
const period = ref<Period>('daily')
</script>

<template>
  <UDashboardPanel id="home">
    <template #header>
      <UDashboardNavbar title="Home" :ui="{ right: 'gap-3' }">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>

        <template #right>
          <UTooltip text="Notifications" :shortcuts="['N']">
            <UButton
              color="neutral"
              variant="ghost"
              square
              @click="isNotificationsSlideoverOpen = true"
            >
              <UChip color="error" inset>
                <UIcon name="i-lucide-bell" class="size-5 shrink-0" />
              </UChip>
            </UButton>
          </UTooltip>

          <UDropdownMenu :items="items">
            <UButton icon="i-lucide-plus" size="md" class="rounded-full" />
          </UDropdownMenu>
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar>
        <template #left>
          <!-- NOTE: The `-ms-1` class is used to align with the `DashboardSidebarCollapse` button here. -->
          <HomeDateRangePicker v-model="range" class="-ms-1" />

          <HomePeriodSelect v-model="period" :range="range" />
        </template>
      </UDashboardToolbar>
    </template>

    <template #body>
      <HomeStats :period="period" :range="range" />
      <HomeChart :period="period" :range="range" />
      <HomeSales :period="period" :range="range" />
    </template>
  </UDashboardPanel>
</template>
