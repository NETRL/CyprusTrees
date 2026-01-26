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
                <MapLibreView2 v-if="isMap2" :initialTreeId="initialTreeId" />
                <MapLibreView v-else :initialTreeId="initialTreeId" />
            </MapFilterProvider>
        </main>
    </div>
</template>

<script setup>
import AppHeader from '@/Layouts/AppHeader.vue';
import MapLibreView from '@/Components/Map/MapLibreView.vue';
import MapLibreView2 from '@/Components/Map2/MapLibreView2.vue';
import HeaderLogo from '@/Components/Layout/Header/HeaderLogo.vue';
import MapFilterProvider from '@/Components/Map/Partials/MapFilterProvider.vue';
import { provide } from 'vue';
import { useCustomToast } from '@/Composables/useCustomToast';

const props = defineProps({
    reportTypes: {
        type: Array,
        default: () => []
    },
    speciesData: {
        type: Array,
        default: () => []
    },
    neighborhoodData: {
        type: Array,
        default: () => []
    },
    tagData: {
        type: Array,
        default: () => []
    },
    treeSex: {
        type: Array,
        default: () => []
    },
    healthStatus: {
        type: Array,
        default: () => []
    },
    treeStatus: {
        type: Array,
        default: () => []
    },
    ownerType: {
        type: Array,
        default: () => []
    },
    initialTreeId: {
        type: Number,
        default: null,
    }
})

const isMap2 = window.location.pathname.startsWith('/map2')

useCustomToast();

provide('reportTypes', props.reportTypes)
provide('speciesData', props.speciesData)
provide('neighborhoodData', props.neighborhoodData)
provide('tagData', props.tagData)
provide('treeSex', props.treeSex)
provide('healthStatus', props.healthStatus)
provide('treeStatus', props.treeStatus)
provide('ownerType', props.ownerType)



</script>