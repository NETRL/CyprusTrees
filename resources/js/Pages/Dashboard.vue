<template>
    <Head title="Dashboard" />
    <slot/>

</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineOptions({
    layout: AuthenticatedLayout
});

</script>


