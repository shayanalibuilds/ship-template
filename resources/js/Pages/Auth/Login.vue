<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const canResetPassword = usePage().props.canResetPassword;

const form = useForm({
    email: '',
    password: '',
    remember: false,
});
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-semibold text-gray-900">Log in</h1>

        <form class="mt-6 space-y-4" @submit.prevent="form.post(route('login'))">
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

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <Checkbox v-model="form.remember" name="remember" />
                Remember me
            </label>

            <div class="flex items-center justify-between">
                <a
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-gray-600 underline hover:text-gray-900"
                >
                    Forgot your password?
                </a>

                <PrimaryButton :disabled="form.processing">Log in</PrimaryButton>
            </div>
        </form>

        <p class="mt-6 text-sm text-gray-600">
            No account yet?
            <a :href="route('register')" class="font-medium text-gray-900 underline"> Register </a>
        </p>
    </GuestLayout>
</template>
