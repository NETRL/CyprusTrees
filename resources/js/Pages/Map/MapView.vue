<template>
    <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" position="bottom-right" />
    <div class="min-h-dvh h-dvh flex flex-col">
        <header class="shrink-0 relative">
            <AppHeader :withSidebar="false" />
        </header>

        <main class="flex-1 relative overflow-hidden">
            <MapFilterProvider>
                <MapUiStateProvider>
                    <MapLibreView :initialTreeId="initialTreeId" :initialLocation="initialLocation" :initialMode="initialMode"
                        :initialEventId="initialEventId" />
                </MapUiStateProvider>
            </MapFilterProvider>
        </main>
    </div>
</template>

<script setup>
import AppHeader from '@/Layouts/AppHeader.vue';
import MapLibreView from '@/Components/Map/MapLibreView.vue';
import MapFilterProvider from '@/Components/Map/Providers/MapFilterProvider.vue';
import { provide } from 'vue';
import { useCustomToast } from '@/Composables/useCustomToast';
import MapUiStateProvider from '@/Components/Map/Providers/MapUiStateProvider.vue';

const props = defineProps({
    reportTypes: { type: Array, default: () => [] },
    speciesData: { type: Array, default: () => [] },
    neighborhoodData: { type: Array, default: () => [] },
    tagData: { type: Array, default: () => [] },
    treeSex: { type: Array, default: () => [] },
    healthStatus: { type: Array, default: () => [] },
    treeStatus: { type: Array, default: () => [] },
    ownerType: { type: Array, default: () => [] },
    initialTreeId: { type: Number, default: null, },
    initialLocation: { type: Object, default: null, },
    initialMode: { type: String, default: 'default' },
    initialEventId: { type: Number, default: null },
    userEvents: { type: Array, default: () => [] },
})

useCustomToast();

provide('reportTypes', props.reportTypes)
provide('speciesData', props.speciesData)
provide('neighborhoodData', props.neighborhoodData)
provide('tagData', props.tagData)
provide('treeSex', props.treeSex)
provide('healthStatus', props.healthStatus)
provide('treeStatus', props.treeStatus)
provide('ownerType', props.ownerType)
provide('userEvents', props.userEvents)



</script>