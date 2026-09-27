<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    code: '',
});
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-semibold text-gray-900">Two-factor authentication</h1>

        <p class="mt-2 text-sm text-gray-600">
            Enter the six digit code from your authenticator app. A recovery code works here too.
        </p>

        <form
            class="mt-6 space-y-4"
            @submit.prevent="form.post(route('two-factor.challenge.store'))"
        >
            <div>
                <InputLabel for="code" value="Authentication code" />
                <TextInput
                    id="code"
                    v-model="form.code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                    class="mt-1 block"
                    required
                />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing">Continue</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
