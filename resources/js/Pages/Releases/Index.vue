<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Alert from '../../Components/Alert.vue';
import AppLayout from '../../Components/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    releases: { type: Object, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash);

const excerpt = (body) => (body.length > 140 ? `${body.slice(0, 140)}…` : body);

const day = (date) => date.slice(0, 10);

const pageLabel = (label) => label.replace('&laquo;', 'Previous').replace('&raquo;', 'Next').trim();
</script>

<template>
    <AppLayout>
        <Head title="Releases" />

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Releases</h1>
            <Link
                href="/releases/create"
                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
            >
                New release
            </Link>
        </div>

        <Alert v-if="flash.success" :message="flash.success" class="mt-6" />

        <EmptyState
            v-if="props.releases.data.length === 0"
            title="No releases yet"
            description="Create the first release to get things moving."
            class="mt-8"
        >
            <Link
                href="/releases/create"
                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
            >
                Create a release
            </Link>
        </EmptyState>

        <ul v-else class="mt-8 space-y-4">
            <li
                v-for="release in props.releases.data"
                :key="release.id"
                class="rounded-lg border border-gray-200 bg-white p-6"
            >
                <div class="text-xs text-gray-500">{{ day(release.created_at) }}</div>
                <Link
                    :href="`/releases/${release.slug}`"
                    class="mt-1 block text-lg font-medium text-gray-900 hover:text-gray-600"
                >
                    {{ release.title }}
                </Link>
                <p class="mt-2 text-sm text-gray-600">{{ excerpt(release.body) }}</p>
            </li>
        </ul>

        <nav
            v-if="props.releases.last_page > 1"
            class="mt-8 flex flex-wrap justify-center gap-1"
            aria-label="Pagination"
        >
            <component
                :is="link.url ? Link : 'span'"
                v-for="link in props.releases.links"
                :key="pageLabel(link.label) + link.url"
                :href="link.url ?? '#'"
                class="rounded-md px-3 py-1.5 text-sm"
                :class="[
                    link.active
                        ? 'bg-gray-900 text-white'
                        : link.url
                          ? 'text-gray-700 hover:bg-gray-100'
                          : 'text-gray-400',
                ]"
            >
                {{ pageLabel(link.label) }}
            </component>
        </nav>
    </AppLayout>
</template>
