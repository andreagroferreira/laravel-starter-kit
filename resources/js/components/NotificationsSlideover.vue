<script setup lang="ts">
import {computed, watch} from 'vue';
import {Link, router, usePage} from '@inertiajs/vue3';
import {formatTimeAgo} from '@vueuse/core';
import {useDashboard} from '../composables/useDashboard';
import type {Notification} from '../types';

const {isNotificationsSlideoverOpen} = useDashboard();

const page = usePage<{notifications?: Notification[]}>();
const notifications = computed(() => page.props.notifications ?? []);

// `notifications` is an Inertia optional prop, so fetch it on demand the first
// time the slideover opens instead of shipping it with every page load.
watch(isNotificationsSlideoverOpen, (isOpen) => {
    if (isOpen && page.props.notifications === undefined) {
        router.reload({only: ['notifications']});
    }
});
</script>

<template>
    <USlideover v-model:open="isNotificationsSlideoverOpen" title="Notifications">
        <template #body>
            <Link
                v-for="notification in notifications"
                :key="notification.id"
                :href="`/inbox?id=${notification.id}`"
                class="px-3 py-2.5 rounded-md hover:bg-elevated/50 flex items-center gap-3 relative -mx-3 first:-mt-3 last:-mb-3"
            >
                <UChip color="error" :show="!!notification.unread" inset>
                    <UAvatar
                        v-bind="notification.sender.avatar"
                        :alt="notification.sender.name"
                        size="md"
                    />
                </UChip>

                <div class="text-sm flex-1">
                    <p class="flex items-center justify-between">
                        <span class="text-highlighted font-medium">{{
                            notification.sender.name
                        }}</span>

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
