<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
        <h2 class="text-base font-semibold text-gray-900">{{ title }}</h2>
        <p v-if="description" class="mt-1 text-sm text-gray-500">{{ description }}</p>
        <div v-if="$slots.default" class="mt-6">
            <slot />
        </div>
    </div>
</template>
