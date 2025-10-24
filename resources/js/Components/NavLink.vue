<template>
    <Link :href="route(href)" :class="classes">
        <slot />
    </Link>
</template>
<script setup>
import { computed } from 'vue';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 leading-5 text-brand-500 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 leading-5 dark:text-white dark:hover:text-gray-300 hover:text-brand-500 transition duration-150 ease-in-out',
);
</script>