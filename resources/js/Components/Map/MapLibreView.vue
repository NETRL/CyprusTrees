<template>
    <MapSidebar :treeData="treeData" :neighborhoodData="neighborhoodData" @toggleCategory="toggleCategory" />

    <!-- Event Mode Top Bar -->
    <EventModeTopBar :initialEventId="props.initialEventId" :activeEvent="activeEvent" :eventLoading="eventLoading"
        :eventError="eventError" />

    <div ref="mapContainer" class="map-container w-full h-full"></div>

    <LocateControl :mapRef="map" />

    <MapPanels :hovered="hoveredTree" :selected="selectedTree" :markerLatLng="markerLatLng"
        :selectedNeighborhood="selectedNeighborhood" :neighborhoodStats="neighborhoodStats" :pinClickFlag="pinClickFlag"
        @cancelCreate="onCancelCreate" @clearSelection="onClearSelection" />

    <MapLoadingOverlay :isLoading="isLoading" />

    <AuthPromptModal :open="showAuthPrompt" @close="showAuthPrompt = false" />

</template>

<script setup>
import { onMounted, ref, onBeforeUnmount, computed, provide, readonly, watch, inject } from 'vue'
import MapSidebar from '@/Components/Map/Partials/MapSidebar.vue'
import MapLoadingOverlay from '@/Components/Map/Partials/MapLoadingOverlay.vue'
import MapPanels from '@/Components/Map/Partials/MapPanels.vue'
import AuthPromptModal from '@/Components/Map/Partials/AuthPromptModal.vue'
import LocateControl from '@/Components/Map/Controls/LocateControl.vue'
import EventModeTopBar from '@/Components/Map/Controls/EventModeTopBar.vue'

import { initMap } from '@/Lib/Map/core/initMap'
import { useMapFilter } from '@/Composables/useMapFilter'
import { usePermissions } from '@/Composables/usePermissions'

import mitt from 'mitt'
import { useTreeVisualization } from '@/Lib/Map/useTreeVisualization'
import { useMapLayers } from '@/Lib/Map/useMapLayers'
import { useNeighborhoodSelection } from '@/Lib/Map/useNeighborhoodSelection'
import { useTreeCreateMarker } from '@/Lib/Map/useTreeCreateMarker'
import { useEventMode } from '@/Lib/Map/useEventMode'
import { useTreeMutatorHandler } from '@/Lib/Map/useTreeMutatorHandler'
import { useMapUiState, MAP_PANELS, MAP_MODES } from '@/Lib/Map/useMapUiState'
import { MapOptionsManager } from '@/Lib/Map/layers/managers/MapOptionsManager'

const props = defineProps({
    initialTreeId: { type: Number, default: null },
    initialLocation: { type: Object, default: null, },
    initialMode: { type: String, default: 'default' },
    initialEventId: { type: Number, default: null },
})

const { can } = usePermissions()
provide('can', can)

const mapBus = mitt()
provide('mapBus', mapBus)

const userEvents = inject("userEvents", ref([]));

const mapContainer = ref(null)
const map = ref(null)

const isLoading = ref(true)

// Selection state
const treeData = ref([])
const neighborhoodData = ref([])
const selectedNeighborhoodId = ref(null)

const selectedTree = ref(null)
const hoveredTree = ref(null)

// Api refs
let treeLayerApi = null
let neighLayerApi = null
let gisLayerApi = null

let mapOptionsMgr = null

// Marker (long click) Api
const treeCreate = useTreeCreateMarker(map, {
    onClearSelection: clearMapSelection,
})
const { markerLatLng, showAuthPrompt, pinClickFlag, isInteractionEnabled } = treeCreate

// Neighborhood Select Api
const { selectedNeighborhood, neighborhoodStats } = useNeighborhoodSelection({
    neighborhoodData,
    selectedNeighborhoodId,
})

const { selectedFilter } = useMapFilter()
const { ui, openPanel, togglePanel, setActiveMode, closeSidebar } = useMapUiState()

// -------- EVENT MODE (fetch + recenter) ----------
const isEventMode = computed(() => ui.activeMode === MAP_MODES.EVENTS)
const initialMode = computed(() => props.initialMode)
const initialEventIdRef = computed(() => props.initialEventId)
const {
    activePlantingEventId,
    activeEvent,
    eventLoading,
    eventError,

    onEventSelected,
    onEventStart,
    onEventComplete,
    onEventFocused,
    terminateEventMode,
} = useEventMode(map, {
    initialModeRef: initialMode,
    initialEventIdRef: initialEventIdRef,
    getTreeLayerApi: () => treeLayerApi
})

provide('activePlantingEventId', activePlantingEventId)


const lastCreatedTree = ref(null)
provide('lastCreatedTree', lastCreatedTree)

// --- visibility state ---

const hiddenCategories = ref(null)
provide('hiddenCategories', readonly(hiddenCategories));
let treeVisualizationApi = null

const { onTreeSaved, onTreeUpdated } = useTreeMutatorHandler({
    treeData,
    selectedTree,

    getTreeLayerApi: () => treeLayerApi,  // since treeLayerApi is a plain let, expose it via a getter:

    initialEventId: initialEventIdRef, // computed from props
    activeEvent,
    lastCreatedTree,
})
// --- bus ---
mapBus.on('tree:saved', onTreeSaved)
mapBus.on('tree:updated', onTreeUpdated)
mapBus.on('event:selected', onEventSelected)
mapBus.on('event:start', onEventStart)
mapBus.on('event:complete', onEventComplete)
mapBus.on('event:focus', onEventFocused)

const assignedTreeIdSet = computed(() => {
    if (!isEventMode.value) return null // no restriction outside event mode

    const set = new Set()
    for (const ev of (userEvents ?? [])) {
        const id = ev?.meta?.tree_id
        if (id != null) set.add(Number(id))
    }
    return set
})

// compute a map filter expression that "include features where the feature property id is in the array"
const eventBaseMapFilter = computed(() => {
    const allow = assignedTreeIdSet.value
    if (allow == null) return null

    const ids = [...allow]
    if (!ids.length) return ['==', 'id', -1] // show none

    return ['in', 'id', ...ids] // legacy syntax
})


const eventBasePredicate = computed(() => {
    if (assignedTreeIdSet.value == null) return () => true
    const allow = new Set(assignedTreeIdSet.value)
    return (f) => allow.has(f?.properties?.id)
})

// -------- MAP INIT ----------
onMounted(async () => {
    try {
        map.value = await initMap(mapContainer.value)

        treeCreate.attach()

        mapOptionsMgr = new MapOptionsManager(map.value, {
            mapUi: { openPanel, setActiveMode, closeSidebar },
        }).init()

        const layersComposable = useMapLayers(map.value, {
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


        treeVisualizationApi = useTreeVisualization(map.value, {
            onHiddenCategories: (set) => { hiddenCategories.value = set },
            onPredicateSet: (p) => { treeLayerApi?.setTreesDataFiltered(p) },
            selectedFilterRef: selectedFilter,
            baseMapFilterRef: () => eventBaseMapFilter.value,
            basePredicateRef: () => eventBasePredicate.value,
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

// ---------- UI state watcher ----------

watch(() => ui.activeMode, (mode) => {
    if (mode === MAP_MODES.NONE) {
        terminateEventMode()
        clearMapSelection()
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

    mapOptionsMgr?.destroy()
    mapOptionsMgr = null

    mapBus.off('tree:saved', onTreeSaved)
    mapBus.off('tree:updated', onTreeUpdated)
    mapBus.off('event:selected', onEventSelected)
    mapBus.off('event:start', onEventStart)
    mapBus.off('event:complete', onEventComplete)
    mapBus.off('event:focus', onEventFocused)
})
</script>
