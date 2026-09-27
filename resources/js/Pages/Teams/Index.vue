<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const teams = usePage().props.teams;
const invitations = usePage().props.invitations;
const success = usePage().props.flash?.success;

const createForm = useForm({
    name: '',
});

function createTeam() {
    createForm.post(route('teams.store'), {
        onSuccess: () => createForm.reset(),
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-xl space-y-6">
            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Create a team</h2>

                <p v-if="success" class="mt-2 text-sm text-green-700">{{ success }}</p>

                <form class="mt-4 space-y-4" @submit.prevent="createTeam">
                    <div>
                        <InputLabel for="name" value="Team name" />
                        <TextInput
                            id="name"
                            v-model="createForm.name"
                            class="mt-1 block"
                            required
                        />
                        <InputError class="mt-2" :message="createForm.errors.name" />
                    </div>

                    <PrimaryButton :disabled="createForm.processing">Create team</PrimaryButton>
                </form>
            </section>

            <section
                v-if="invitations && invitations.length > 0"
                class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200"
            >
                <h2 class="text-lg font-semibold text-gray-900">Invitations</h2>

                <ul class="mt-4 space-y-2">
                    <li
                        v-for="invitation in invitations"
                        :key="invitation.id"
                        class="flex items-center justify-between rounded-md bg-gray-50 px-4 py-2"
                    >
                        <span class="text-sm text-gray-800">{{ invitation.team }}</span>
                        <Link
                            :href="invitation.acceptUrl"
                            class="text-sm font-medium text-gray-900 underline"
                        >
                            Accept
                        </Link>
                    </li>
                </ul>
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Your teams</h2>

                <p v-if="teams && teams.all.length === 0" class="mt-2 text-sm text-gray-600">
                    You are not part of a team yet. Create one above or accept an invitation.
                </p>

                <ul v-else class="mt-4 space-y-2">
                    <li
                        v-for="team in teams?.all ?? []"
                        :key="team.id"
                        class="flex items-center justify-between rounded-md bg-gray-50 px-4 py-2"
                    >
                        <Link
                            :href="route('teams.show', team.id)"
                            class="text-sm font-medium text-gray-900 hover:underline"
                        >
                            {{ team.name }}
                        </Link>
                        <span
                            v-if="teams?.current?.id === team.id"
                            class="rounded-md bg-green-50 px-2 py-1 text-xs text-green-800"
                        >
                            current
                        </span>
                    </li>
                </ul>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
