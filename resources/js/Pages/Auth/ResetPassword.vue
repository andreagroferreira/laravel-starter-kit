<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

defineOptions({ layout: GuestLayout })

const props = defineProps<{
  token: string
  email: string
}>()

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = (): void => {
  form.post('/admin/reset-password', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Reset password" />

  <div class="space-y-6">
    <header class="space-y-1">
      <h2 class="text-xl font-semibold text-highlighted">
        Reset your password
      </h2>
      <p class="text-sm text-muted">
        Choose a strong new password to secure your account.
      </p>
    </header>

    <form
      class="space-y-5"
      novalidate
      @submit.prevent="submit"
    >
      <UFormField
        label="Email"
        name="email"
        :error="form.errors.email"
        required
      >
        <UInput
          v-model="form.email"
          type="email"
          name="email"
          autocomplete="username"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="New password"
        name="password"
        :error="form.errors.password"
        required
      >
        <UInput
          v-model="form.password"
          type="password"
          name="password"
          autocomplete="new-password"
          autofocus
          placeholder="••••••••"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Confirm password"
        name="password_confirmation"
        :error="form.errors.password_confirmation"
        required
      >
        <UInput
          v-model="form.password_confirmation"
          type="password"
          name="password_confirmation"
          autocomplete="new-password"
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
        label="Reset password"
      />
    </form>
  </div>
</template>
