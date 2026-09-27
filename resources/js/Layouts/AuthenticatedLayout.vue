<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';

const appName = computed(() => usePage().props.app.name);
const user = computed(() => usePage().props.auth.user);
const features = computed(() => usePage().props.features);
const teams = computed(() => usePage().props.teams);
const showingNavigation = ref(false);
</script>

<template>
    <div class="min-h-dvh bg-gray-50">
        <nav class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
                <Link href="/" class="text-lg font-semibold tracking-tight text-gray-900">
                    {{ appName }}
                </Link>

                <button
                    type="button"
                    class="rounded-md px-3 py-1 text-sm text-gray-600 hover:text-gray-900 md:hidden"
                    @click="showingNavigation = !showingNavigation"
                >
                    Menu
                </button>

                <div class="hidden items-center gap-2 md:flex">
                    <Dropdown v-if="features.teams" align="right" width="56">
                        <template #trigger>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md px-3 py-2 text-sm text-gray-600 hover:text-gray-900"
                            >
                                {{ teams?.current?.name ?? 'Teams' }}
                            </button>
                        </template>

                        <template #content>
                            <template v-for="team in teams?.all ?? []" :key="team.id">
                                <span
                                    v-if="teams?.current?.id === team.id"
                                    class="block w-full px-4 py-2 text-sm font-medium text-gray-900"
                                >
                                    {{ team.name }}
                                </span>
                                <Link
                                    v-else
                                    :href="route('teams.switch', team.id)"
                                    method="post"
                                    as="button"
                                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    {{ team.name }}
                                </Link>
                            </template>

                            <div class="my-1 border-t border-gray-100" />

                            <Link
                                :href="route('teams.index')"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Manage teams
                            </Link>
                        </template>
                    </Dropdown>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md px-3 py-2 text-sm text-gray-600 hover:text-gray-900"
                            >
                                {{ user.name }}
                            </button>
                        </template>

                        <template #content>
                            <Link
                                :href="route('profile.edit')"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Profile
                            </Link>

                            <Link
                                v-if="features.two_factor"
                                :href="route('security.show')"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Security
                            </Link>

                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                            >
                                Log Out
                            </Link>
                        </template>
                    </Dropdown>
                </div>
            </div>

            <div v-show="showingNavigation" class="border-t border-gray-200 px-6 py-3 md:hidden">
                <Link :href="route('profile.edit')" class="block py-2 text-sm text-gray-700">
                    Profile
                </Link>

                <Link
                    v-if="features.two_factor"
                    :href="route('security.show')"
                    class="block py-2 text-sm text-gray-700"
                >
                    Security
                </Link>

                <Link
                    v-if="features.teams"
                    :href="route('teams.index')"
                    class="block py-2 text-sm text-gray-700"
                >
                    Teams
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="block py-2 text-left text-sm text-gray-700"
                >
                    Log Out
                </Link>
            </div>
        </nav>

        <main class="mx-auto max-w-6xl px-6 py-10">
            <slot />
        </main>
    </div>
</template>
