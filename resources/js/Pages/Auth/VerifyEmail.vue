<template>
    <FullScreenLayout>

        <Head title="Login" />
        <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
            <div class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900">
                <div class="flex flex-col flex-1 w-full lg:w-1/2">
                    <div class="w-full max-w-md pt-10 mx-auto">
                        <Link :href="route('logout')"
                            class="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg class="stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 20 20" fill="none">
                            <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Log Out
                        </Link>
                    </div>
                    <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                        <div>
                            <div class="mb-5 sm:mb-8">
                                <h1
                                    class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                                    Verify Your Email
                                </h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Thanks for signing up! Before getting started, could you verify your
                                    email address by clicking on the link we just emailed to you? If you
                                    didn't receive the email, we will gladly send you another.
                                </p>
                                <div class="mb-4 text-sm font-medium text-green-600" v-if="verificationLinkSent">
                                    A new verification link has been sent to the email address you
                                    provided during registration.
                                </div>
                            </div>
                            <div>
                                <form @submit.prevent="handleSubmit">
                                    <div class="space-y-5">
                                        <!-- Button -->
                                        <div>
                                            <button type="submit"
                                                class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"
                                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                                Resend Verification Email
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative items-center hidden w-full h-full lg:w-1/2 bg-brand-950 dark:bg-white/5 lg:grid">
                    <div class="flex items-center justify-center z-1">
                        <common-grid-shape />
                        <div class="flex flex-col items-center max-w-xs">
                            <Link :href="route('/')" class="block mb-4">
                            <img width="{231}" height="{48}" src="@assets/images/logo/auth-logo.svg" alt="Logo" />
                            </Link>
                            <p class="text-center text-gray-400 dark:text-white/60">
                                Enter a something about the brand here.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FullScreenLayout>
</template>

<script setup>
import CommonGridShape from '@/Components/Common/CommonGridShape.vue'
import FullScreenLayout from '@/Layouts/FullScreenLayout.vue'
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';



const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const handleSubmit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);

</script>
