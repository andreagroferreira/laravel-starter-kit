<script setup lang="ts">
import WcMasterDetail from './WcMasterDetail.vue';
</script>

<template>
    <Story
        title="Data/WcMasterDetail"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcMasterDetail>
                <template #master>
                    <div class="p-4">
                        <p class="text-sm font-medium">Master list</p>
                        <ul class="mt-2 space-y-1 text-sm text-muted">
                            <li>Item one</li>
                            <li>Item two</li>
                            <li>Item three</li>
                        </ul>
                    </div>
                </template>
                <template #detail>
                    <div class="p-4">
                        <p class="text-sm font-medium">Detail pane</p>
                        <p class="mt-2 text-sm text-muted">
                            Select an item from the master list to see details.
                        </p>
                    </div>
                </template>
            </WcMasterDetail>
        </Variant>
    </Story>
</template>
