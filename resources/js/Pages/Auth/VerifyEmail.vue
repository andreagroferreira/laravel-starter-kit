<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

defineOptions({ layout: GuestLayout })

const props = defineProps<{
  status?: string | null
}>()

const form = useForm({})

const verificationLinkSent = computed(
  () => props.status === 'verification-link-sent',
)

const submit = (): void => {
  form.post('/admin/email/verification-notification')
}

const logout = (): void => {
  form.post('/admin/logout')
}
</script>

<template>
  <Head title="Verify email" />

  <div class="space-y-6">
    <header class="space-y-1">
      <h2 class="text-xl font-semibold text-highlighted">
        Verify your email address
      </h2>
      <p class="text-sm text-muted">
        Thanks for signing up. Please click the link in the email we just sent
        to verify your address. If you didn't receive it, we can send another.
      </p>
    </header>

    <div
      v-if="verificationLinkSent"
      role="status"
      class="rounded-md bg-success/10 px-3 py-2 text-sm font-medium text-success"
    >
      A new verification link has been sent to your email address.
    </div>

    <form
      class="space-y-4"
      novalidate
      @submit.prevent="submit"
    >
      <UButton
        type="submit"
        :loading="form.processing"
        :disabled="form.processing"
        block
        size="lg"
        label="Resend verification email"
      />

      <UButton
        type="button"
        variant="ghost"
        color="neutral"
        block
        size="sm"
        label="Sign out"
        @click="logout"
      />
    </form>

    <p class="text-center text-xs text-muted">
      Need help?
      <Link
        href="/admin/login"
        class="font-medium text-primary hover:underline"
      >
        Back to sign in
      </Link>
    </p>
  </div>
</template>
