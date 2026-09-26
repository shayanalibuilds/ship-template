<script setup>
import { usePage } from '@inertiajs/vue3';

const appName = usePage().props.app.name;
</script>

<template>
    <main
        class="mx-auto flex min-h-screen max-w-2xl flex-col items-center justify-center gap-4 px-6 text-center"
    >
        <h1 class="text-3xl font-semibold tracking-tight text-gray-900">{{ appName }}</h1>
        <p class="text-gray-600">A Laravel ship template for AI-assisted product delivery.</p>
    </main>
</template>
