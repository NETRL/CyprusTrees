<template>
    <MapSidebar :treeData="treeData" :neighborhoodData="neighborhoodData"
        @toggleCategory="toggleCategory" />

    <!-- Event Mode Top Bar -->
    <EventModeTopBar :isPlantingMode="isPlantingMode" :eventId="props.eventId" :activeEvent="activeEvent"
        :eventLoading="eventLoading" :eventError="eventError" />

    <div ref="mapContainer" class="map-container w-full h-full"></div>

    <LocateControl :mapRef="map" />

    <MapPanels :hovered="hoveredTree" :selected="selectedTree" :markerLatLng="markerLatLng"
        :selectedNeighborhood="selectedNeighborhood" :neighborhoodStats="neighborhoodStats" :pinClickFlag="pinClickFlag"
        @update:selected="selectedTree = $event" @cancelCreate="onCancelCreate" @clearSelection="onClearSelection" />

    <MapLoadingOverlay :isLoading="isLoading" />

    <AuthPromptModal :open="showAuthPrompt" @close="showAuthPrompt = false" />

    <div class="absolute top-13 left-2 z-50 flex flex-col gap-2">
        <button class="px-3 py-2 rounded-md border bg-white dark:bg-gray-900 shadow" @click="togglePanel">
            Show Events
        </button>
    </div>


</template>

<script setup>
import { onMounted, ref, onBeforeUnmount, computed, provide, readonly, watch } from 'vue'
import MapSidebar from '@/Components/Map/Partials/MapSidebar.vue'
import MapLoadingOverlay from '@/Components/Map/Partials/MapLoadingOverlay.vue'
import MapPanels from '@/Components/Map/Partials/MapPanels.vue'
import AuthPromptModal from '@/Components/Map/Partials/AuthPromptModal.vue'
import LocateControl from '@/Components/Map/Controls/LocateControl.vue'
import EventModeTopBar from '@/Components/Map/Controls/EventModeTopBar.vue'

import { initMap } from '@/Lib/Map/core/initMap'
import { setupBaseLayers } from '@/Lib/Map/core/setupBaseLayers'
import { useMapFilter } from '@/Composables/useMapFilter'
import { usePermissions } from '@/Composables/usePermissions'

import mitt from 'mitt'
import { useTreeVisualization } from '@/Lib/Map/useTreeVisualization'
import { useMapLayers } from '@/Lib/Map/useMapLayers'
import { useNeighborhoodSelection } from '@/Lib/Map/useNeighborhoodSelection'
import { useTreeCreateMarker } from '@/Lib/Map/useTreeCreateMarker'
import { useEventMode } from '@/Lib/Map/useEventMode'
import { useTreeMutatorHandler } from '@/Lib/Map/useTreeMutatorHandler'
import { useMapUiState, MAP_PANELS } from '@/Lib/Map/useMapUiState'
import { useEventFunctions } from '@/Lib/Map/useEventFunctions'

const props = defineProps({
    initialTreeId: { type: Number, default: null },
    initialLocation: { type: Object, default: null, },
    mode: { type: String, default: 'default' },
    eventId: { type: Number, default: null },
})

const { can } = usePermissions()
provide('can', can)

const mapBus = mitt()
provide('mapBus', mapBus)

const mapContainer = ref(null)
const map = ref(null)

const isLoading = ref(true)

const treeData = ref([])
const neighborhoodData = ref([])
const selectedNeighborhoodId = ref(null)

const selectedTree = ref(null)
const hoveredTree = ref(null)

let treeLayerApi = null
let neighLayerApi = null
let gisLayerApi = null

const treeCreate = useTreeCreateMarker(map, {
    onClearSelection: clearMapSelection,
})
const { markerLatLng, showAuthPrompt, pinClickFlag, isInteractionEnabled } = treeCreate

const { selectedNeighborhood, neighborhoodStats } = useNeighborhoodSelection({
    neighborhoodData,
    selectedNeighborhoodId,
})
const { selectedFilter } = useMapFilter()

// -------- EVENT MODE (fetch + recenter) ----------
const modeRef = computed(() => props.mode)
const eventIdRef = computed(() => props.eventId)

const {
    isEventMode,
    isPlantingMode,
    activePlantingEventId,
    activeEvent,
    eventLoading,
    eventError,
} = useEventMode(map, {
    modeRef,
    eventIdRef,
})

const { onEventSelected } = useEventFunctions(map, { getTreeLayerApi: () => treeLayerApi })

provide('isPlantingMode', isPlantingMode)
provide('activePlantingEventId', activePlantingEventId)


const lastCreatedTree = ref(null)
provide('lastCreatedTree', lastCreatedTree)

// --- visibility state ---

const hiddenCategories = ref(null)
provide('hiddenCategories', readonly(hiddenCategories));
let treeVisualizationApi = null

// --- init ---
const center = [33.37, 35.17]
const zoom = 12

const MAPTILER_KEY = import.meta.env.VITE_MAPTILER_KEY

const { onTreeSaved, onTreeUpdated } = useTreeMutatorHandler({
    treeData,
    selectedTree,

    getTreeLayerApi: () => treeLayerApi,  // since treeLayerApi is a plain let, expose it via a getter:

    isPlantingMode,
    eventId: eventIdRef, // computed from props
    activeEvent,
    lastCreatedTree,
})
// --- bus ---
mapBus.on('tree:saved', onTreeSaved)
mapBus.on('tree:updated', onTreeUpdated)
mapBus.on('event:selected', onEventSelected)

const { openPanel } = useMapUiState()
function togglePanel() {
    openPanel(MAP_PANELS.EVENTS)
}
// -------- MAP INIT ----------
onMounted(async () => {
    try {
        const { map: m } = await initMap(mapContainer.value, {
            center,
            zoom,
            maptilerKey: MAPTILER_KEY,
        })

        map.value = m

        treeCreate.attach()

        setupBaseLayers(m, MAPTILER_KEY)

        const layersComposable = useMapLayers(m, {
            isInteractionEnabled: () => isInteractionEnabled.value,

            onNeighborhoodData: neighborhoodData,
            onNeighborhoodSelected: selectedNeighborhoodId,

            onTreeData: treeData,
            onTreeSelected: selectedTree,
            onTreeHovered: hoveredTree,

            onInitialFilter: selectedFilter,
        })

        const { treeLayerApi: tApi, neighLayerApi: nApi, gisLayerApi: gApi } = await layersComposable.initializeLayers()
        treeLayerApi = tApi
        neighLayerApi = nApi
        gisLayerApi = gApi


        treeVisualizationApi = useTreeVisualization(m, {
            onHiddenCategories: (set) => { hiddenCategories.value = set },
            onPredicateSet: (p) => { treeLayerApi?.setTreesDataFiltered(p) },
            selectedFilterRef: selectedFilter,
        })

        if (props.initialTreeId) {
            treeLayerApi?.selectTreeById(props.initialTreeId)
        }

    } catch (e) {
        console.error(e)
    } finally {
        isLoading.value = false
    }
})

function clearMapSelection() {
    treeLayerApi?.clearSelection?.()
    neighLayerApi?.clearSelection?.()
    hoveredTree.value = null
    selectedTree.value = null
}

// -------- VISUALIZATION / FILTERING ----------

function toggleCategory(payload) {
    treeVisualizationApi?.onToggleCategory(payload)
}

// -------- CRUD hooks ----------
function onClearSelection() {
    clearMapSelection()
}

function onCancelCreate() {
    treeCreate.cancelCreate()
}

function onCreateSuccess() {
    treeCreate.createSuccessCleanup()
}

// -------- CLEANUP ----------
onBeforeUnmount(() => {
    const m = map.value
    if (m) {
        m.remove()
    }
    map.value = null

    treeLayerApi = null
    neighLayerApi = null

    gisLayerApi?.destroy()
    gisLayerApi = null

    mapBus.off('tree:saved', onTreeSaved)
    mapBus.off('tree:updated', onTreeUpdated)
    mapBus.off('event:selected', onEventSelected)
})
</script>
