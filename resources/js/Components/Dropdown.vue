<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const open = ref(false);

function closeOnEscape(event) {
    if (open.value && event.key === 'Escape') {
        open.value = false;
    }
}

function closeOnOutsideClick(event) {
    if (open.value && !event.target.closest('[data-dropdown]')) {
        open.value = false;
    }
}

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    document.addEventListener('click', closeOnOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.removeEventListener('click', closeOnOutsideClick);
});
</script>

<template>
    <div data-dropdown class="relative">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <div
            v-show="open"
            class="absolute z-50 mt-2 rounded-lg bg-white shadow-lg ring-1 ring-gray-200"
            :class="align === 'right' ? 'right-0' : 'left-0'"
            :style="{ width: width === '48' ? '12rem' : '10rem' }"
            @click="open = false"
        >
            <slot name="content" />
        </div>
    </div>
</template>
