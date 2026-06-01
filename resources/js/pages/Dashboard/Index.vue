<script setup lang="ts">
import {ref, shallowRef} from 'vue';
import {Head} from '@inertiajs/vue3';
import {sub} from 'date-fns';
import type {DropdownMenuItem} from '@nuxt/ui';
import AppLayout from '../../layouts/AppLayout.vue';
import HomeStats from '../../components/home/HomeStats.vue';
import HomeChart from '../../components/home/HomeChart.vue';
import HomeSales from '../../components/home/HomeSales.vue';
import HomeDateRangePicker from '../../components/home/HomeDateRangePicker.vue';
import HomePeriodSelect from '../../components/home/HomePeriodSelect.vue';
import {useDashboard} from '../../composables/useDashboard';
import type {Period, Range} from '../../types';

defineOptions({layout: AppLayout});

const {isNotificationsSlideoverOpen} = useDashboard();

const items = [
    [
        {
            label: 'New mail',
            icon: 'i-lucide-send',
            to: '/inbox',
        },
        {
            label: 'New customer',
            icon: 'i-lucide-user-plus',
            to: '/customers',
        },
    ],
] satisfies DropdownMenuItem[][];

const range = shallowRef<Range>({
    start: sub(new Date(), {days: 14}),
    end: new Date(),
});
const period = ref<Period>('daily');
</script>

<template>
    <Head title="Home" />

    <UDashboardPanel id="home">
        <template #header>
            <UDashboardNavbar title="Home" :ui="{right: 'gap-3'}">
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
                                <UIcon
                                    name="i-lucide-bell"
                                    class="size-5 shrink-0"
                                />
                            </UChip>
                        </UButton>
                    </UTooltip>

                    <UDropdownMenu :items="items">
                        <UButton
                            icon="i-lucide-plus"
                            size="md"
                            class="rounded-full"
                        />
                    </UDropdownMenu>
                </template>
            </UDashboardNavbar>

            <UDashboardToolbar>
                <template #left>
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
