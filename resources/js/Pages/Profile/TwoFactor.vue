<template>
    <ComponentCard :title="'Two-Factor Authentication'"
        :desc="'Add an extra layer of security to your account by requiring a code from an authenticator app when you sign in.'">


        <ComponentCard :transparent="true">
            <!-- Flash status -->
            <div v-if="status"
                class="rounded-md border border-emerald-500/40 bg-emerald-50 px-4 py-2 text-sm text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-100">
                <span v-if="status === 'two-factor-enabled'">Two-factor authentication has been enabled.</span>
                <span v-else-if="status === 'two-factor-disabled'">Two-factor authentication has been disabled.</span>
                <span v-else-if="status === 'two-factor-recovery-codes-regenerated'">New recovery codes have been
                    generated.</span>
                <span v-else>{{ status }}</span>
            </div>

            <!-- When 2FA is enabled -->
            <section v-if="enabled">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-50">
                    Status
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Two-factor authentication is <span
                        class="font-semibold text-emerald-600 dark:text-emerald-400">enabled</span>
                    for your account.
                </p>

                <!-- Recovery codes -->
                <div class="mt-5">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-50">
                        Recovery Codes
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Store these recovery codes in a safe place. They can be used to access your account if you lose
                        access to your authenticator app.
                    </p>

                    <div v-if="recoveryCodes && recoveryCodes.length"
                        class="mt-3 grid gap-2 rounded-md bg-gray-50 p-3 text-xs font-mono text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                        <span v-for="code in recoveryCodes" :key="code"
                            class="inline-flex items-center justify-between rounded bg-white px-3 py-1 dark:bg-gray-900">
                            {{ code }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="button"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:dark:text-gray-200 dark:hover:bg-white/3 sm:w-auto"
                            @click="regenerateRecoveryCodes">
                            Regenerate Recovery Codes
                        </button>
                    </div>
                </div>

                <!-- Disable button -->
                <div class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-800">
                    <button type="button"
                        class="rounded-md border border-red-500 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 dark:border-red-500/70 dark:bg-red-900/20 dark:text-red-200 dark:hover:bg-red-900/40"
                        @click="disableTwoFactor">
                        Disable Two-Factor Authentication
                    </button>
                </div>
            </section>

            <!-- When 2FA is NOT enabled -->
            <section v-else>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-50">
                    Enable Two-Factor Authentication
                </h2>

                <ol class="mt-3 space-y-4 text-sm text-gray-700 dark:text-gray-200">
                    <li class="flex gap-3">
                        <span
                            class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white">1</span>
                        <div>
                            <p class="font-medium">Scan the QR code</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Use your authenticator app (Google Authenticator, Authy, etc.) to scan this QR code.
                            </p>

                            <div class="mt-3 flex items-center justify-center">
                                <div v-if="qrCodeDataUrl"
                                    class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                                    <img :src="qrCodeDataUrl" alt="Two factor QR code"
                                        class="h-40 w-40 object-contain" />
                                </div>
                                <p v-else class="text-xs text-amber-600 dark:text-amber-400">
                                    The QR code couldn’t be displayed. You can still manually enter the key below into
                                    your authenticator app.
                                </p>
                            </div>

                            <div v-if="secret" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Or enter this key manually: <span class="font-mono font-semibold">{{ secret }}</span>
                            </div>
                        </div>
                    </li>

                    <li class="flex gap-3">
                        <span
                            class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white">2</span>
                        <div class="flex-1">
                            <p class="font-medium">Enter the 6-digit code</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                After scanning, your app will show a 6-digit code. Enter it below to confirm and enable
                                two-factor authentication.
                            </p>

                            <form class="mt-3 space-y-3" @submit.prevent="submitEnable">
                                <div>
                                    <input v-model="enableForm.code" type="text" inputmode="numeric"
                                        autocomplete="one-time-code"
                                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                                        placeholder="123456" />
                                    <p v-if="enableForm.errors.code" class="mt-1 text-xs text-red-500">
                                        {{ enableForm.errors.code }}
                                    </p>
                                </div>

                                <button type="submit"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                                    :disabled="enabling">
                                    <span v-if="!enabling">Enable Two-Factor Authentication</span>
                                    <span v-else>Enabling…</span>
                                </button>
                            </form>
                        </div>
                    </li>
                </ol>
            </section>
        </ComponentCard>
    </ComponentCard>

</template>


<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ComponentCard from '@/Components/Common/ComponentCard.vue';
import QRCode from 'qrcode';


defineOptions({
    layout: AuthenticatedLayout
});

const props = defineProps({
    enabled: {
        type: Boolean,
        default: false,
    },
    // URL or data-URI for the QR image (e.g. PNG/SVG)
    otpauthUrl: {
        type: String,
        default: null,
    },
    // Raw secret string (optional, to show under QR)
    secret: {
        type: String,
        default: null,
    },
    // Array of recovery codes (only shown when backend sends them)
    recoveryCodes: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()

const status = computed(() => page.props.flash?.status ?? null)

const qrCodeDataUrl = ref(null)

const enableForm = useForm({
    code: '',
})

const enabling = computed(() => enableForm.processing)

function submitEnable() {
    enableForm.post(route('two-factor.enable'), {
        preserveScroll: true,
    })
}

function disableTwoFactor() {
    if (!confirm('Are you sure you want to disable two-factor authentication?')) {
        return
    }

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
    })
}

function regenerateRecoveryCodes() {
    if (!confirm('Generate new recovery codes? Old ones will stop working.')) {
        return
    }

    router.post(
        route('two-factor.recovery-codes'),
        {},
        {
            preserveScroll: true,
        },
    )
}

async function generateQr() {
    if (!props.otpauthUrl) {
        qrCodeDataUrl.value = null
        return
    }

    try {
        qrCodeDataUrl.value = await QRCode.toDataURL(props.otpauthUrl)
    } catch (e) {
        console.error('Failed to generate QR code', e)
        qrCodeDataUrl.value = null
    }
}

onMounted(generateQr)
watch(() => props.otpauthUrl, generateQr)
</script>
