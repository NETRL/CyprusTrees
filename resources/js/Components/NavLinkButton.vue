<template>
    <div>
        <Link :href="href" type="button" as="button" :title="title"
            :aria-label="title" class="relative inline-flex items-center justify-center p-2 rounded-lg border bg-white text-gray-500 transition-colors
           hover:bg-brand-100 hover:text-brand-700 border-gray-200
           dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-brand-800 dark:hover:text-white">
        <slot></slot>
        </Link>
    </div>
</template>


<script setup>

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: '',
    },
});

</script>