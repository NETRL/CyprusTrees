<template>
    <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" position="bottom-right" />
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
import { useCustomToast } from '@/Composables/useCustomToast';

const props = defineProps({
    reportTypes: {
        type: Array,
        default: () => []
    },
})

useCustomToast();

provide('reportTypes', props.reportTypes)


</script>