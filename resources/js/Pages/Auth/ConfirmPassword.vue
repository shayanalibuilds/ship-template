<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-semibold text-gray-900">Confirm password</h1>

        <p class="mt-2 text-sm text-gray-600">
            This is a secure area. Please confirm your password before continuing.
        </p>

        <form class="mt-6 space-y-4" @submit.prevent="form.post(route('password.confirm'))">
            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    class="mt-1 block"
                    required
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing">Confirm</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
