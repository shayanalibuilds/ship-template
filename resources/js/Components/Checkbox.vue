<script setup>
defineProps({
    name: { type: String, default: undefined },
    value: { type: String, default: undefined },
});

const model = defineModel({ type: Boolean, default: false });
</script>

<template>
    <input
        v-model="model"
        type="checkbox"
        :name="name"
        :value="value"
        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
    />
</template>
