<script setup>
defineProps({
    message: { type: String, required: true },
});
</script>

<template>
    <div
        class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
        role="status"
    >
        {{ message }}
    </div>
</template>
