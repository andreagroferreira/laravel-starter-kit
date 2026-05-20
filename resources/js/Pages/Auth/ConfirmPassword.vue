<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineOptions({ layout: AuthLayout })

const form = useForm({
  password: '',
})

const submit = (): void => {
  form.post('/admin/user/confirm-password', {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Confirm password" />

  <div class="space-y-6">
    <header class="space-y-1">
      <h2 class="text-xl font-semibold text-highlighted">
        Confirm your password
      </h2>
      <p class="text-sm text-muted">
        This is a secure area. Please confirm your password to continue.
      </p>
    </header>

    <form
      class="space-y-5"
      novalidate
      @submit.prevent="submit"
    >
      <UFormField
        label="Password"
        name="password"
        :error="form.errors.password"
        required
      >
        <UInput
          v-model="form.password"
          type="password"
          name="password"
          autocomplete="current-password"
          autofocus
          placeholder="••••••••"
          class="w-full"
        />
      </UFormField>

      <UButton
        type="submit"
        :loading="form.processing"
        :disabled="form.processing"
        block
        size="lg"
        label="Confirm"
      />
    </form>
  </div>
</template>
