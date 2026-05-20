<script setup lang="ts">
import { computed, nextTick, ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineOptions({ layout: AuthLayout })

const useRecoveryCode = ref(false)

const codeInput = ref<HTMLInputElement | null>(null)
const recoveryInput = ref<HTMLInputElement | null>(null)

const form = useForm({
  code: '',
  recovery_code: '',
})

const heading = computed(() =>
  useRecoveryCode.value ? 'Recovery code' : 'Two-factor authentication',
)

const description = computed(() =>
  useRecoveryCode.value
    ? 'Use one of your stored recovery codes to continue.'
    : 'Enter the 6-digit code from your authenticator app.',
)

const toggleRecovery = async (): Promise<void> => {
  useRecoveryCode.value = !useRecoveryCode.value
  form.code = ''
  form.recovery_code = ''
  form.clearErrors()
  await nextTick()
  if (useRecoveryCode.value) {
    recoveryInput.value?.focus()
  } else {
    codeInput.value?.focus()
  }
}

const submit = (): void => {
  form.post('/admin/two-factor-challenge', {
    onFinish: () => {
      form.reset('code', 'recovery_code')
    },
  })
}
</script>

<template>
  <Head title="Two-factor challenge" />

  <div class="space-y-6">
    <header class="space-y-1">
      <h2 class="text-xl font-semibold text-highlighted">
        {{ heading }}
      </h2>
      <p class="text-sm text-muted">
        {{ description }}
      </p>
    </header>

    <form
      class="space-y-5"
      novalidate
      @submit.prevent="submit"
    >
      <UFormField
        v-if="!useRecoveryCode"
        label="Authentication code"
        name="code"
        :error="form.errors.code"
        required
      >
        <UInput
          ref="codeInput"
          v-model="form.code"
          type="text"
          name="code"
          inputmode="numeric"
          autocomplete="one-time-code"
          autofocus
          placeholder="123456"
          class="w-full tracking-widest"
        />
      </UFormField>

      <UFormField
        v-else
        label="Recovery code"
        name="recovery_code"
        :error="form.errors.recovery_code"
        required
      >
        <UInput
          ref="recoveryInput"
          v-model="form.recovery_code"
          type="text"
          name="recovery_code"
          autocomplete="one-time-code"
          autofocus
          placeholder="abcd-1234-efgh"
          class="w-full"
        />
      </UFormField>

      <UButton
        type="submit"
        :loading="form.processing"
        :disabled="form.processing"
        block
        size="lg"
        :label="useRecoveryCode ? 'Verify recovery code' : 'Verify code'"
      />

      <div class="text-center">
        <UButton
          type="button"
          variant="link"
          color="neutral"
          size="sm"
          :label="useRecoveryCode ? 'Use authentication code' : 'Use a recovery code'"
          @click="toggleRecovery"
        />
      </div>
    </form>
  </div>
</template>
