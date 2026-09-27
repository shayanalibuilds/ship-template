<script setup>
defineProps({
    type: { type: String, default: 'button' },
});

defineEmits(['click']);
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
        @click="$emit('click')"
    >
        <slot />
    </button>
</template>
