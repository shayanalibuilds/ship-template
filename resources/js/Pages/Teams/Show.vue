<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { useRoute } from 'ziggy-js';
import { ref } from 'vue';

const route = useRoute(usePage().props.ziggy);

const props = defineProps({
    team: { type: Object, required: true },
    members: { type: Array, required: true },
    invitations: { type: Array, required: true },
});

const teams = usePage().props.teams;
const success = usePage().props.flash?.success;
const confirmingDeletion = ref(false);

const renameForm = useForm({
    name: props.team.name,
});
const inviteForm = useForm({
    email: '',
});
const busyForm = useForm({});

function renameTeam() {
    renameForm.patch(route('teams.update', props.team.id), {
        preserveScroll: true,
    });
}

function invite() {
    inviteForm.post(route('teams.invitations.store', props.team.id), {
        preserveScroll: true,
        onSuccess: () => inviteForm.reset(),
    });
}

function removeMember(memberId) {
    busyForm.delete(route('teams.members.destroy', { team: props.team.id, user: memberId }), {
        preserveScroll: true,
    });
}

function cancelInvitation(invitationId) {
    busyForm.delete(
        route('teams.invitations.destroy', { team: props.team.id, invitation: invitationId }),
        {
            preserveScroll: true,
        },
    );
}

function switchTeam() {
    busyForm.post(route('teams.switch', props.team.id), {
        preserveScroll: true,
    });
}

function deleteTeam() {
    busyForm.delete(route('teams.destroy', props.team.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-xl space-y-6">
            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">{{ props.team.name }}</h2>
                    <SecondaryButton
                        v-if="teams?.current?.id !== props.team.id"
                        :disabled="busyForm.processing"
                        @click="switchTeam"
                    >
                        Switch to this team
                    </SecondaryButton>
                </div>

                <p v-if="success" class="mt-2 text-sm text-green-700">{{ success }}</p>

                <form v-if="props.team.isOwner" class="mt-4 space-y-4" @submit.prevent="renameTeam">
                    <div>
                        <InputLabel for="name" value="Team name" />
                        <TextInput
                            id="name"
                            v-model="renameForm.name"
                            class="mt-1 block"
                            required
                        />
                        <InputError class="mt-2" :message="renameForm.errors.name" />
                    </div>

                    <PrimaryButton :disabled="renameForm.processing">Rename</PrimaryButton>
                </form>
            </section>

            <section
                v-if="props.team.isOwner"
                class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200"
            >
                <h2 class="text-lg font-semibold text-gray-900">Invite a member</h2>

                <form class="mt-4 space-y-4" @submit.prevent="invite">
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="inviteForm.email"
                            type="email"
                            autocomplete="email"
                            class="mt-1 block"
                            required
                        />
                        <InputError class="mt-2" :message="inviteForm.errors.email" />
                    </div>

                    <PrimaryButton :disabled="inviteForm.processing">Send invitation</PrimaryButton>
                </form>

                <ul v-if="props.invitations.length > 0" class="mt-4 space-y-2">
                    <li
                        v-for="invitation in props.invitations"
                        :key="invitation.id"
                        class="flex items-center justify-between rounded-md bg-gray-50 px-4 py-2"
                    >
                        <span class="text-sm text-gray-800">Pending: {{ invitation.email }}</span>
                        <button
                            type="button"
                            class="text-sm text-gray-600 underline hover:text-gray-900"
                            @click="cancelInvitation(invitation.id)"
                        >
                            Cancel
                        </button>
                    </li>
                </ul>
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Members</h2>

                <p v-if="props.members.length === 0" class="mt-2 text-sm text-gray-600">
                    Nobody joined yet. Invite the first member above.
                </p>

                <ul v-else class="mt-4 space-y-2">
                    <li
                        v-for="member in props.members"
                        :key="member.id"
                        class="flex items-center justify-between rounded-md bg-gray-50 px-4 py-2"
                    >
                        <span class="text-sm text-gray-800">
                            {{ member.name }}
                            <span class="text-gray-500">({{ member.email }})</span>
                        </span>
                        <button
                            v-if="props.team.isOwner"
                            type="button"
                            class="text-sm text-gray-600 underline hover:text-gray-900"
                            @click="removeMember(member.id)"
                        >
                            Remove
                        </button>
                    </li>
                </ul>
            </section>

            <section
                v-if="props.team.isOwner"
                class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200"
            >
                <h2 class="text-lg font-semibold text-gray-900">Delete team</h2>

                <p class="mt-2 text-sm text-gray-600">
                    Deleting the team removes every membership and outstanding invitation. This
                    cannot be undone.
                </p>

                <SecondaryButton
                    v-if="!confirmingDeletion"
                    class="mt-4"
                    @click="confirmingDeletion = true"
                >
                    Delete team
                </SecondaryButton>

                <div v-else class="mt-4 flex items-center gap-3">
                    <PrimaryButton :disabled="busyForm.processing" @click="deleteTeam">
                        Delete team
                    </PrimaryButton>
                    <SecondaryButton @click="confirmingDeletion = false">Cancel</SecondaryButton>
                </div>
            </section>

            <p class="text-sm">
                <Link
                    :href="route('teams.index')"
                    class="text-gray-600 underline hover:text-gray-900"
                >
                    Back to all teams
                </Link>
            </p>
        </div>
    </AuthenticatedLayout>
</template>
