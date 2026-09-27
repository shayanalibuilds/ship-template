<script setup>
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const mustVerifyEmail = usePage().props.mustVerifyEmail;
const status = usePage().props.status;
const user = usePage().props.auth.user;

const confirmingDeletion = ref(false);

const profileForm = useForm({
    name: user.name,
    email: user.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({
    password: '',
});

function updateProfile() {
    profileForm.patch(route('profile.update'));
}

function updatePassword() {
    passwordForm.put(route('password.update'), {
        onSuccess: () => passwordForm.reset(),
    });
}

function deleteAccount() {
    deleteForm.delete(route('profile.destroy'), {
        onSuccess: () => closeDeletion(),
    });
}

function closeDeletion() {
    confirmingDeletion.value = false;
    deleteForm.reset();
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-xl space-y-6">
            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Profile</h2>

                <p v-if="status === 'profile-updated'" class="mt-2 text-sm text-green-700">
                    Profile updated.
                </p>

                <div
                    v-if="mustVerifyEmail && user.email_verified_at === null"
                    class="mt-3 rounded-md bg-yellow-50 p-3 text-sm text-yellow-800"
                >
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" class="underline">
                        Resend the verification email.
                    </Link>
                </div>

                <form class="mt-4 space-y-4" @submit.prevent="updateProfile">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            v-model="profileForm.name"
                            autocomplete="name"
                            class="mt-1 block"
                            required
                        />
                        <InputError class="mt-2" :message="profileForm.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="profileForm.email"
                            type="email"
                            autocomplete="email"
                            class="mt-1 block"
                            required
                        />
                        <InputError class="mt-2" :message="profileForm.errors.email" />
                    </div>

                    <div class="flex items-center gap-3">
                        <PrimaryButton :disabled="profileForm.processing">Save</PrimaryButton>

                        <p v-if="profileForm.recentlySuccessful" class="text-sm text-green-700">
                            Saved.
                        </p>
                    </div>
                </form>
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Update password</h2>

                <p v-if="status === 'password-updated'" class="mt-2 text-sm text-green-700">
                    Password updated.
                </p>

                <form class="mt-4 space-y-4" @submit.prevent="updatePassword">
                    <div>
                        <InputLabel for="current_password" value="Current password" />
                        <TextInput
                            id="current_password"
                            v-model="passwordForm.current_password"
                            type="password"
                            autocomplete="current-password"
                            class="mt-1 block"
                            required
                        />
                        <InputError class="mt-2" :message="passwordForm.errors.current_password" />
                    </div>

                    <div>
                        <InputLabel for="new_password" value="New password" />
                        <TextInput
                            id="new_password"
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            class="mt-1 block"
                            required
                        />
                        <InputError class="mt-2" :message="passwordForm.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="new_password_confirmation" value="Confirm new password" />
                        <TextInput
                            id="new_password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="mt-1 block"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="passwordForm.errors.password_confirmation"
                        />
                    </div>

                    <div class="flex items-center gap-3">
                        <PrimaryButton :disabled="passwordForm.processing">Update</PrimaryButton>

                        <p v-if="passwordForm.recentlySuccessful" class="text-sm text-green-700">
                            Updated.
                        </p>
                    </div>
                </form>
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Delete account</h2>

                <p class="mt-2 text-sm text-gray-600">
                    Once your account is deleted, all of its data is gone forever. Download anything
                    you want to keep first.
                </p>

                <SecondaryButton
                    v-if="!confirmingDeletion"
                    class="mt-4"
                    @click="confirmingDeletion = true"
                >
                    Delete account
                </SecondaryButton>

                <form v-else class="mt-4 space-y-4" @submit.prevent="deleteAccount">
                    <div>
                        <InputLabel for="delete_password" value="Password" />
                        <TextInput
                            id="delete_password"
                            v-model="deleteForm.password"
                            type="password"
                            autocomplete="current-password"
                            class="mt-1 block"
                            required
                        />
                        <InputError class="mt-2" :message="deleteForm.errors.password" />
                    </div>

                    <div class="flex items-center gap-3">
                        <PrimaryButton :disabled="deleteForm.processing"
                            >Delete account</PrimaryButton
                        >

                        <SecondaryButton @click="closeDeletion">Cancel</SecondaryButton>
                    </div>
                </form>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
