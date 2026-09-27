<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const appName = computed(() => usePage().props.app.name);
</script>

<template>
    <main class="flex min-h-dvh flex-col items-center justify-center bg-gray-50 px-6 py-12">
        <a href="/" class="text-xl font-semibold tracking-tight text-gray-900">{{ appName }}</a>

        <div class="mt-6 w-full max-w-md rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <slot />
        </div>
    </main>
</template>
