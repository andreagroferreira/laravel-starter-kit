<script setup lang="ts">
import { ref } from 'vue';
import WcInput from './WcInput.vue';

const name = ref('');
const email = ref('user@example.com');
const invalid = ref('not-an-email');
const password = ref('');
</script>

<template>
    <Story
        title="Forms/WcInput"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcInput
                v-model="name"
                label="Full name"
                placeholder="John Doe"
            />
        </Variant>

        <Variant title="With hint">
            <WcInput
                v-model="email"
                label="Email"
                hint="We will never share your email."
                placeholder="you@example.com"
                type="email"
            />
        </Variant>

        <Variant title="With error">
            <WcInput
                v-model="invalid"
                label="Email"
                error="Please enter a valid email address."
                type="email"
            />
        </Variant>

        <Variant title="Required">
            <WcInput
                v-model="password"
                label="Password"
                type="password"
                :required="true"
                placeholder="••••••••"
            />
        </Variant>
    </Story>
</template>
