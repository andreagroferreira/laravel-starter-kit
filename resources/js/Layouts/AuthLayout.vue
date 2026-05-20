<script setup lang="ts">
withDefaults(defineProps<{
  subtitle?: string
}>(), {
  subtitle: 'Boilerplate admin area'
})
</script>

<template>
  <div class="min-h-screen bg-default flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-highlighted tracking-tight">
          WizardingCode
        </h1>
        <p class="text-sm text-muted mt-2">
          {{ subtitle }}
        </p>
      </div>

      <UCard
        :ui="{
          root: 'shadow-lg',
          body: 'sm:p-8',
        }"
      >
        <slot />
      </UCard>

      <p class="text-center text-xs text-muted mt-6">
        &copy; {{ new Date().getFullYear() }} WizardingCode &middot; All rights reserved
      </p>
    </div>
  </div>
</template>
