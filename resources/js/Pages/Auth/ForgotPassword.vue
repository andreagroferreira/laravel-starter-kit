<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

defineOptions({ layout: GuestLayout })

defineProps<{
  status?: string | null
}>()

const form = useForm({
  email: '',
})

const submit = (): void => {
  form.post('/admin/forgot-password')
}
</script>

<template>
  <Head title="Forgot password" />

  <div class="space-y-6">
    <header class="space-y-1">
      <h2 class="text-xl font-semibold text-highlighted">
        Forgot your password?
      </h2>
      <p class="text-sm text-muted">
        No problem. Enter your email and we'll send a reset link.
      </p>
    </header>

    <div
      v-if="status"
      role="status"
      class="rounded-md bg-success/10 px-3 py-2 text-sm font-medium text-success"
    >
      {{ status }}
    </div>

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
          autofocus
          placeholder="you@wizardingcode.io"
          class="w-full"
        />
      </UFormField>

      <UButton
        type="submit"
        :loading="form.processing"
        :disabled="form.processing"
        block
        size="lg"
        label="Send reset link"
      />

      <div class="text-center">
        <Link
          href="/admin/login"
          class="text-sm font-medium text-primary hover:underline"
        >
          Back to sign in
        </Link>
      </div>
    </form>
  </div>
</template>
