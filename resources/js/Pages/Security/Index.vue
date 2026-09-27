<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    enabled: { type: Boolean, required: true },
    confirming: { type: Boolean, required: true },
    secret: { type: String, default: null },
    qrSvg: { type: String, default: null },
    recoveryCodes: { type: Array, default: null },
});

const success = usePage().props.flash?.success;

const enableForm = useForm({});
const confirmForm = useForm({
    code: '',
});
const recoveryForm = useForm({});
const disableForm = useForm({});

function confirmSetup() {
    confirmForm.post(route('two-factor.confirm'), {
        preserveScroll: true,
        onSuccess: () => confirmForm.reset(),
    });
}

function disable() {
    disableForm.delete(route('two-factor.destroy'), {
        preserveScroll: true,
    });
}

function regenerate() {
    recoveryForm.post(route('two-factor.recovery-codes'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-xl space-y-6">
            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Two-factor authentication</h2>

                <p v-if="success" class="mt-2 text-sm text-green-700">{{ success }}</p>

                <p class="mt-2 text-sm text-gray-600">
                    Add a second step to your login. Authenticator apps like 1Password, Google
                    Authenticator or Authy generate the codes.
                </p>

                <template v-if="props.enabled && !props.confirming">
                    <p
                        class="mt-4 inline-flex rounded-md bg-green-50 px-3 py-1 text-sm text-green-800"
                    >
                        Two-factor authentication is enabled.
                    </p>

                    <div v-if="props.recoveryCodes" class="mt-4 rounded-md bg-gray-50 p-4">
                        <p class="text-sm font-medium text-gray-900">Recovery codes</p>
                        <p class="mt-1 text-sm text-gray-600">
                            Each code works once. Store them somewhere safe right now.
                        </p>
                        <ul class="mt-3 grid grid-cols-2 gap-1 font-mono text-sm text-gray-800">
                            <li v-for="(code, index) in props.recoveryCodes" :key="index">
                                {{ code }}
                            </li>
                        </ul>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <SecondaryButton :disabled="recoveryForm.processing" @click="regenerate">
                            Show new recovery codes
                        </SecondaryButton>

                        <SecondaryButton :disabled="disableForm.processing" @click="disable">
                            Disable
                        </SecondaryButton>
                    </div>
                </template>

                <template v-else-if="props.confirming">
                    <p class="mt-4 text-sm text-gray-600">
                        Scan the QR code with your authenticator app, then confirm with a code.
                    </p>

                    <div class="mt-4 max-w-[200px]" v-html="props.qrSvg" />

                    <p class="mt-2 font-mono text-sm break-all text-gray-800">
                        {{ props.secret }}
                    </p>

                    <form class="mt-4 space-y-4" @submit.prevent="confirmSetup">
                        <div>
                            <InputLabel for="code" value="Confirm with a code" />
                            <TextInput
                                id="code"
                                v-model="confirmForm.code"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                class="mt-1 block"
                                required
                            />
                            <InputError class="mt-2" :message="confirmForm.errors.code" />
                        </div>

                        <PrimaryButton :disabled="confirmForm.processing">Confirm</PrimaryButton>
                    </form>
                </template>

                <template v-else>
                    <PrimaryButton
                        :disabled="enableForm.processing"
                        @click="
                            enableForm.post(route('two-factor.store'), { preserveScroll: true })
                        "
                    >
                        Enable two-factor authentication
                    </PrimaryButton>
                </template>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
