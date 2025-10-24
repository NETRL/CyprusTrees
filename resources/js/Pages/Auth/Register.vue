<template>
    <FullScreenLayout>

        <Head title="Register" />
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
                    <!-- Form -->
                    <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                        <div class="mb-5 sm:mb-8">
                            <h1
                                class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                                Sign Up
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Enter your email and password to sign up!
                            </p>
                        </div>
                        <div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-5">
                                <button
                                    class="disabled inline-flex items-center justify-center gap-3 py-3 text-sm font-normal text-gray-700 transition-colors bg-gray-100 rounded-lg px-7 hover:bg-gray-200 hover:text-gray-800 dark:bg-white/5 dark:text-white/90 dark:hover:bg-white/10">
                                    <GoogleIcon/>
                                    Sign up with Google
                                </button>
                            </div>
                            <div class="relative py-3 sm:py-5">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-200 dark:border-gray-800"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="p-2 text-gray-400 bg-white dark:bg-gray-900 sm:px-5 sm:py-2">Or</span>
                                </div>
                            </div>
                            <form @submit.prevent="handleSubmit">
                                <div class="space-y-5">
                                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                        <!-- First Name -->
                                        <div class="sm:col-span-1">
                                            <FormField v-model="form.first_name" name="fname" label="Full Name"
                                                :required="true" component="InputText" type="text" :displayErrors="true"
                                                placeholder="Enter your first name" />
                                        </div>
                                        <!-- Last Name -->
                                        <div class="sm:col-span-1">
                                            <FormField v-model="form.last_name" name="lname" label="Last Name"
                                                :required="true" component="InputText" type="text" :displayErrors="true"
                                                placeholder="Enter your last name" />
                                        </div>
                                    </div>
                                    <!-- Email -->
                                    <div>
                                        <FormField v-model="form.email" name="email" label="Email" :required="true"
                                            component="InputText" type="email" :displayErrors="true"
                                            placeholder="info@gmail.com" />
                                    </div>
                                    <!-- Password -->
                                    <div>
                                        <FormField v-model="form.password" name="password" label="Password"
                                            :required="true" component="Password" type="password" :displayErrors="true"
                                            placeholder="Enter your password" toggleMask />
                                    </div>
                                    <!-- Confirm Password -->
                                    <div>
                                         <FormField v-model="form.password_confirmation" name="password_confirmation" label="Confirm Password"
                                            :required="true" component="Password" type="password" :displayErrors="true"
                                            placeholder="Confirm your password" :feedback="false"/>
                                    </div>
                                    <!-- Checkbox -->
                                    <div>
                                        <CustomCheckbox name="agreeToTerms" id="agreeToTerms"
                                            v-model:checked="form.agreeToTerms">
                                            <p class="inline-block font-normal text-gray-500 dark:text-gray-400">
                                                By creating an account you agree to the
                                                <a href="https://cyens.org.cy" target="_blank"><span
                                                        class="text-gray-800 dark:text-white/90">Terms and
                                                        Conditions</span></a>
                                                and our
                                                <a href="https://cyens.org.cy" target="_blank"><span
                                                        class="text-gray-800 dark:text-white">Privacy Policy</span>.</a>
                                            </p>
                                        </CustomCheckbox>
                                    </div>
                                    <!-- Button -->
                                    <div>
                                        <button type="submit" :class="{ 'opacity-25': form.processing }"
                                            :disabled="form.processing"
                                            class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                                            Sign Up
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-5">
                                <p
                                    class="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
                                    Already have an account?
                                    <Link :href="route('login')"
                                        class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Sign In
                                    </Link>
                                </p>
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

<script setup lang="ts">
import FullScreenLayout from '@/Layouts/FullScreenLayout.vue'
import CommonGridShape from '@/Components/Common/CommonGridShape.vue'
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3';
import CustomCheckbox from '@/Components/CustomCheckbox.vue';
import FormField from '@/Components/Primitives/FormField.vue';
import { GoogleIcon } from '@/icons';

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    agreeToTerms: false,
});

const showPassword = ref(false)

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value
}

const handleSubmit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation', 'agreeToTerms'),
    });
}
</script>
