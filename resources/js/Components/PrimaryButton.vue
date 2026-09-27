<script setup>
defineProps({
    type: { type: String, default: 'submit' },
});

defineEmits(['click']);
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
        @click="$emit('click')"
    >
        <slot />
    </button>
</template>
