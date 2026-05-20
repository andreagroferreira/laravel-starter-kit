<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineOptions({ layout: AuthLayout })

withDefaults(defineProps<{
  canResetPassword?: boolean
  status?: string | null
}>(), {
  canResetPassword: true,
  status: null,
})

const form = useForm({
  email: '',
  password: '',
  remember: false as boolean,
})

const submit = (): void => {
  form.post('/admin/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Sign in" />

  <div class="space-y-6">
    <header class="space-y-1">
      <h2 class="text-xl font-semibold text-highlighted">
        Sign in to your account
      </h2>
      <p class="text-sm text-muted">
        Enter your credentials to access the admin area.
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
          placeholder="••••••••"
          class="w-full"
        />
      </UFormField>

      <div class="flex items-center justify-between">
        <UCheckbox
          v-model="form.remember"
          name="remember"
          label="Remember me"
        />
        <Link
          v-if="canResetPassword"
          href="/admin/forgot-password"
          class="text-sm font-medium text-primary hover:underline"
        >
          Forgot password?
        </Link>
      </div>

      <UButton
        type="submit"
        :loading="form.processing"
        :disabled="form.processing"
        block
        size="lg"
        label="Sign in"
      />
    </form>
  </div>
</template>
