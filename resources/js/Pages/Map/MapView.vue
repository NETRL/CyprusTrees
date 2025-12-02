<template>
    <div class="min-h-dvh h-dvh flex flex-col">
        <header class="shrink-0 relative">
            <AppHeader>
                <template #startIcon>
                    <HeaderLogo class="max-lg:hidden" />
                </template>
            </AppHeader>
        </header>

        <main class="flex-1 relative overflow-hidden">
            <MapFilterProvider>
                <MapLibreView />
            </MapFilterProvider>
        </main>
    </div>
</template>

<script setup>
import AppHeader from '@/Layouts/AppHeader.vue';
import MapLibreView from '@/Components/Map/MapLibreView.vue';
import HeaderLogo from '@/Components/Layout/Header/HeaderLogo.vue';
import MapFilterProvider from '@/Components/Map/Partials/MapFilterProvider.vue';
import { provide } from 'vue';

const props = defineProps({
    reportTypes: {
        type: Array,
        default: () => []
    },
})

provide('reportTypes', props.reportTypes)


</script>