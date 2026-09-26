<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/AppLayout.vue';

const form = useForm({
    title: '',
    body: '',
});

const submit = () => {
    form.post('/releases');
};
</script>

<template>
    <AppLayout>
        <Head title="Create a release" />

        <form class="mx-auto max-w-xl" @submit.prevent="submit">
            <h1 class="text-2xl font-semibold text-gray-900">Create a release</h1>

            <div class="mt-8">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"
                    :class="{ 'border-red-300': form.errors.title }"
                />
                <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">
                    {{ form.errors.title }}
                </p>
            </div>

            <div class="mt-6">
                <label for="body" class="block text-sm font-medium text-gray-700">Body</label>
                <textarea
                    id="body"
                    v-model="form.body"
                    rows="8"
                    class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"
                    :class="{ 'border-red-300': form.errors.body }"
                ></textarea>
                <p v-if="form.errors.body" class="mt-2 text-sm text-red-600">
                    {{ form.errors.body }}
                </p>
            </div>

            <div class="mt-8 flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-md bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700 disabled:opacity-50"
                >
                    {{ form.processing ? 'Creating…' : 'Create release' }}
                </button>
            </div>
        </form>
    </AppLayout>
</template>
