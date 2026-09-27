<script setup>
defineProps({
    type: { type: String, default: 'text' },
    autocomplete: { type: String, default: undefined },
});

const model = defineModel({ required: true });
</script>

<template>
    <input
        v-model="model"
        :type="type"
        :autocomplete="autocomplete"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
    />
</template>
