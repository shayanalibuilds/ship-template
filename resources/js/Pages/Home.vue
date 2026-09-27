<script setup>
import { usePage } from '@inertiajs/vue3';

const appName = usePage().props.app.name;
</script>

<template>
    <main class="flex min-h-dvh items-center justify-center px-6">
        <div class="text-center">
            <h1 class="text-4xl font-semibold tracking-tight text-gray-900">{{ appName }}</h1>
            <p class="mx-auto mt-4 max-w-xl text-lg text-gray-600">
                A clean, tested base. Fork it, rename it, and ship your product.
            </p>
        </div>
    </main>
</template>
