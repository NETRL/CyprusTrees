<template>
  <FullScreenLayout>

    <Head title="Login" />
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
      <div class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900">
        <div class="flex flex-col flex-1 w-full lg:w-1/2">
          <div class="w-full max-w-md pt-10 mx-auto">
            <NavLink :href="'/'">
              <ChevronLeftIcon />
              Back to dashboard
            </NavLink>
          </div>
          <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
            <div>
              <div class="mb-5 sm:mb-8">
                <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                  Sign In
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Enter your email and password to sign in!
                </p>
              </div>
              <div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-5">
                  <button
                    class="inline-flex items-center justify-center gap-3 py-3 text-sm font-normal text-gray-700 transition-colors bg-gray-100 rounded-lg px-7 hover:bg-gray-200 hover:text-gray-800 dark:bg-white/5 dark:text-white/90 dark:hover:bg-white/10">
                    <GoogleIcon />
                    Sign in with Google
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
                    <!-- Email -->
                    <div>
                      <FormField v-model="form.email" name="email" label="Email" :required="true" component="InputText"
                        type="email" :displayErrors="true" placeholder="info@gmail.com" />
                    </div>
                    <!-- Password -->
                    <div>
                      <FormField v-model="form.password" name="password" label="Password" :required="true"
                        component="Password" type="password" :displayErrors="true" placeholder="Enter your password"
                        toggleMask />
                    </div>
                    <!-- Checkbox -->
                    <div class="flex items-center justify-between">
                      <div>
                        <Checkbox name="keepLoggedIn" id="keepLoggedIn" v-model:checked="form.remember"
                          :dispayErrors="false">
                          Keep me logged in
                        </Checkbox>
                      </div>
                      <Link v-if="canResetPassword" :href="route('password.request')"
                        class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">Forgot password?
                      </Link>
                    </div>
                    <!-- Button -->
                    <div>
                      <button type="submit"
                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Sign In
                      </button>
                    </div>
                  </div>
                </form>
                <div class="mt-5">
                  <p class="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
                    Don't have an account?
                    <Link :href="route('register')" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Sign
                    Up</Link>
                  </p>
                </div>
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
import { ref } from 'vue'
import CommonGridShape from '@/Components/Common/CommonGridShape.vue'
import FullScreenLayout from '@/Layouts/FullScreenLayout.vue'
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { useForm } from '@inertiajs/vue3';
import { ChevronLeftIcon, GoogleIcon } from '@/icons';
import NavLink from '@/Components/NavLink.vue';
import FormField from '@/Components/Primitives/FormField.vue';


defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

// const email = ref('')
// const password = ref('')
// const keepLoggedIn = ref(false)
const showPassword = ref(false)

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const handleSubmit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
}
</script>
