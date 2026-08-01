<script setup lang="ts">
import type { NuxtError } from '#app';

defineProps<{ error: NuxtError }>();
</script>

<template>
    <main class="flex min-h-screen items-center justify-center px-6 text-center">
        <div class="space-y-3">
            <p class="text-6xl font-bold opacity-20">{{ error.statusCode }}</p>
            <h1 class="text-2xl font-semibold">
                {{ error.statusMessage || 'Algo correu mal.' }}
            </h1>
            <NuxtLink to="/" class="inline-block underline">
                Voltar ao início
            </NuxtLink>
        </div>
    </main>
</template>
