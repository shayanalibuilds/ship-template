<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const status = usePage().props.status;

const form = useForm({});
</script>

<template>
    <GuestLayout>
        <h1 class="text-xl font-semibold text-gray-900">Verify your email</h1>

        <p class="mt-2 text-sm text-gray-600">
            A verification link was sent to your email address. Click it to finish setting up your
            account.
        </p>

        <div
            v-if="status === 'verification-link-sent'"
            class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700"
        >
            A fresh verification link has been sent to your email address.
        </div>

        <div class="mt-6 flex items-center justify-between">
            <PrimaryButton
                :disabled="form.processing"
                @click="form.post(route('verification.send'))"
            >
                Resend verification email
            </PrimaryButton>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Log Out
            </Link>
        </div>
    </GuestLayout>
</template>
