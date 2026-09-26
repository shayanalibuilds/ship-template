<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const appName = usePage().props.app.name;
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gray-50">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex h-16 w-full max-w-5xl items-center justify-between px-6">
                <Link href="/" class="text-lg font-semibold text-gray-900">{{ appName }}</Link>
                <nav class="flex items-center gap-6 text-sm">
                    <Link href="/releases" class="text-gray-600 hover:text-gray-900">Releases</Link>
                    <Link
                        href="/releases/create"
                        class="rounded-md bg-gray-900 px-3 py-1.5 font-medium text-white hover:bg-gray-700"
                    >
                        New release
                    </Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-5xl flex-1 px-6 py-10">
            <slot />
        </main>

        <footer class="border-t border-gray-200 bg-white">
            <div class="mx-auto w-full max-w-5xl px-6 py-6 text-sm text-gray-500">
                {{ appName }} — a ship template for AI-assisted product delivery.
            </div>
        </footer>
    </div>
</template>
