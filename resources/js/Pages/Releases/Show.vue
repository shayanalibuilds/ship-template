<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/AppLayout.vue';

const props = defineProps({
    release: { type: Object, required: true },
});

const paragraphs = props.release.body.split('\n').filter((line) => line.trim() !== '');
</script>

<template>
    <AppLayout>
        <Head :title="props.release.title" />

        <article class="mx-auto max-w-2xl">
            <Link href="/releases" class="text-sm text-gray-500 hover:text-gray-700">
                &larr; All releases
            </Link>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-gray-900">
                {{ props.release.title }}
            </h1>
            <div class="mt-1 text-xs text-gray-500">
                {{ props.release.created_at.slice(0, 10) }}
            </div>
            <div class="mt-6 space-y-4 text-gray-700">
                <p v-for="(paragraph, index) in paragraphs" :key="index">{{ paragraph }}</p>
            </div>
        </article>
    </AppLayout>
</template>
