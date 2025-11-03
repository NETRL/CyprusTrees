<template>
    <ComponentCard :title="'Two-Factor Authentication'" :desc="titleDesc" :transparent="true">
        <div>
            <Link :href="route('two-factor.show')" type="button" as="button"
                class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:dark:text-gray-200 dark:hover:bg-white/3 sm:w-auto">
                Manage
            </Link>
        </div>
    </ComponentCard>
</template>
<script setup>
import ComponentCard from '@/Components/Common/ComponentCard.vue';
import { Link, usePage } from '@inertiajs/vue3';


const page = usePage();

const titleDesc = 'Each time you log in, in addition to your password, you\'ll use an authenticator app to generate a one-time code.';
console.log(page.props.is2fa)

</script>