<template>
    <FullScreenLayout>

        <Head title="Confirm Password" />
        <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
            <div class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900">
                <div class="flex flex-col flex-1 w-full lg:w-1/2">
                    <div class="w-full max-w-md pt-10 mx-auto">
                        <Link :href="route('/')"
                            class="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg class="stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 20 20" fill="none">
                            <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Back to dashboard
                        </Link>
                    </div>
                    <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                        <div>
                            <div class="mb-5 sm:mb-8">
                                <h1
                                    class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                                    Confirm Your Password
                                </h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    This is a secure area of the application. Please confirm your
                                    password before continuing.
                                </p>
                            </div>
                            <div>
                                <form @submit.prevent="handleSubmit">
                                    <div class="space-y-5">
                                        <!-- Email -->
                                        <div>
                                            <label
                                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                                 Password
                                            </label>
                                            <Password id="password" type="password" v-model="form.password"
                                                autocomplete="password" inputClass="w-full border-transparent! shadow-none! rounded-lg!"
                                                toggleMask
                                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent bg-none text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                            <InputError :message="form.errors.password" class="mt-2" />
                                        </div>
                                        <!-- Button -->
                                        <div>
                                            <button type="submit"
                                                class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"
                                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                                Confirm
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
import FullScreenLayout from '@/Layouts/FullScreenLayout.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const handleSubmit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>
