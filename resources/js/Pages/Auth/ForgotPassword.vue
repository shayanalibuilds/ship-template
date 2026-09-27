<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const status = usePage().props.status;

const form = useForm({
    email: '',
});
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-semibold text-gray-900">Forgot password</h1>

        <p class="mt-2 text-sm text-gray-600">
            Enter your email and we will send you a reset link.
        </p>

        <div v-if="status" class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
            {{ status }}
        </div>

        <form class="mt-6 space-y-4" @submit.prevent="form.post(route('password.email'))">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    class="mt-1 block"
                    required
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing">Email reset link</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
