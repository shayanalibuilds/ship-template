<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../Components/AppLayout.vue';

const appName = usePage().props.app.name;
</script>

<template>
    <AppLayout>
        <section class="py-16 text-center">
            <h1 class="text-4xl font-semibold tracking-tight text-gray-900">{{ appName }}</h1>
            <p class="mx-auto mt-4 max-w-xl text-lg text-gray-600">
                A Laravel ship template for AI-assisted product delivery. Fork it, rename it, ship
                your product from a clean, tested base.
            </p>
            <div class="mt-8 flex justify-center gap-4">
                <Link
                    href="/releases"
                    class="rounded-md bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700"
                >
                    Browse releases
                </Link>
                <Link
                    href="/releases/create"
                    class="rounded-md border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Create a release
                </Link>
            </div>
        </section>
    </AppLayout>
</template>
