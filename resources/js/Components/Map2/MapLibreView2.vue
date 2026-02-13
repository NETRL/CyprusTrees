<template>
    <MapSidebar :treeData="treeData" :neighborhoodData="neighborhoodData" :selectedData="selectedData"
        :currentMode="selectedFilter" @toggleCategory="toggleCategory" />


    <!-- Event Mode Top Bar -->
    <EventModeTopBar :isPlantingMode="isPlantingMode" :eventId="props.eventId" :activeEvent="activeEvent"
        :eventLoading="eventLoading" :eventError="eventError" />

    <div ref="mapContainer" class="map-container w-full h-full"></div>

    <LocateControl :mapRef="map" />

    <TreeCard :hovered="hoveredData" :selected="selectedData" :markerLatLng="markerLatLng"
        :selectedNeighborhood="selectedNeighborhood" :neighborhoodStats="neighborhoodStats" :pinClickFlag="pinClickFlag"
        @update:selected="selectedData = $event" @cancelCreate="onCancelCreate" @clearSelection="onClearSelection" />
    <MapLoadingOverlay :isLoading="isLoading" />

    <AuthPromptModal :open="showAuthPrompt" @close="showAuthPrompt = false" />


</template>

<script setup>
import { onMounted, ref, onBeforeUnmount, watch, computed, nextTick, provide, readonly } from 'vue'
import MapSidebar from '@/Components/Map/Partials/MapSidebar.vue'
import MapLoadingOverlay from '@/Components/Map/Partials/MapLoadingOverlay.vue'
import TreeCard from '@/Components/Map/Partials/TreeCard.vue'
import AuthPromptModal from '@/Components/Map/Partials/AuthPromptModal.vue'
import LocateControl from '@/Components/Map/Controls/LocateControl.vue'
import EventModeTopBar from '@/Components/Map/Controls/EventModeTopBar.vue'

import { initMap } from '@/Lib/Map/InitMap'
import { setupBaseLayers } from '@/Lib/Map/SetupBaseLayers'
import { fetchTreeDetails, loadNeighborhoodStats } from '@/Lib/Map/DataLayers2'
import { useMapFilter } from '@/Composables/useMapFilter'
import { storeNewTree } from '@/Lib/Map/LongClickFunctions'
import { usePermissions } from '@/Composables/usePermissions'

import mitt from 'mitt'
import { router } from '@inertiajs/vue3'
import { useSidebar } from '@/Composables/useSidebar'
import { useTreeVisualization } from '@/Lib/Map/useTreeVisualization'
import { useMapLayers } from '@/Lib/Map/useMapLayers'
import { whenLayerReady } from '@/Lib/Map/useWhenLayerReady'
import { useNeighborhoodSelection } from '@/Lib/Map/useNeighborhoodSelection'

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
// const selectedNeighborhood = ref(null)

const neighborhoodData = ref([])
const selectedData = ref(null)
const hoveredData = ref(null)

// const neighborhoodStats = ref({});
const selectedNeighborhoodId = ref(null)

const markerLatLng = ref(null)
const showAuthPrompt = ref(false)
const pinClickFlag = ref(0)

let longPressCtl = null

// --- event state ---
const activeEvent = ref(null)
const eventLoading = ref(false)
const eventError = ref(null)

let treeLayerApi = null
let neighLayerApi = null
let gisLayerApi = null

const { selectedNeighborhood, neighborhoodStats } = useNeighborhoodSelection({
  neighborhoodData,
  selectedNeighborhoodId,
})

const { selectedFilter } = useMapFilter()

const isEventMode = computed(() => props.mode !== 'default' && !!props.eventId)
const isPlantingMode = computed(() => props.mode === 'planting' && !!props.eventId)
provide('isPlantingMode', isPlantingMode)

const activePlantingEventId = computed(() => props.mode === 'planting' ? props.eventId : null)
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
// --- bus ---
mapBus.on('tree:saved', onTreeSaved)

// -------- MAP INIT ----------
onMounted(async () => {
    try {
        const { map: m } = await initMap(mapContainer.value, {
            center,
            zoom,
            maptilerKey: MAPTILER_KEY,
        })

        map.value = m

        longPressCtl = storeNewTree(m, {
            onLatLng: (latLng) => { markerLatLng.value = latLng },
            requiresAuth: (v) => { showAuthPrompt.value = v },
            onPinClick: () => { pinClickFlag.value++ },
        })

        setupBaseLayers(m, MAPTILER_KEY)

        const layersComposable = useMapLayers(m, {
            isInteractionEnabled: () => markerLatLng.value == null,

            onNeighborhoodData: neighborhoodData,
            onNeighborhoodSelected: selectedNeighborhoodId,

            onTreeData: treeData,
            onTreeSelected: selectedData,
            onTreeHovered: hoveredData,

            onInitialFilter: selectedFilter,
        })

        const { treeLayerApi: tApi, neighLayerApi: nApi, gisLayerApi: gApi } = await layersComposable.initializeLayers()
        treeLayerApi = tApi
        neighLayerApi = nApi
        gisLayerApi = gApi


        treeVisualizationApi = useTreeVisualization(m, {
            onHiddenCategories: (set) => { hiddenCategories.value = set },
            onPredicateSet: (p) => { treeLayerApi?.setTreesDataFiltered(p) }
        })

        // initial styling/filtering once layer is ready
        whenLayerReady(map.value, 'trees-circle', (m) => {
            treeVisualizationApi.visualiseTreeData(selectedFilter.value ?? 'status')
            treeVisualizationApi.applyVisibility(selectedFilter.value ?? 'status')
        })
        if (props.initialTreeId) {
            treeLayerApi.selectTreeById(props.initialTreeId)
        }

    } catch (e) {
        console.error(e)
    } finally {
        isLoading.value = false
    }
})

// -------- Neighborhood stats (stale-safe) ----------
// let statsReq = 0
// watch(
//     selectedNeighborhoodId,
//     async (v) => {
//         const reqId = ++statsReq
//         if (!v) {
//             selectedNeighborhood.value = null
//             neighborhoodStats.value = null
//             return
//         }

//         const fc = neighborhoodData.value
//         if (!fc?.features?.length) return

//         const feature = fc.features.find(f => Number(f.id) === Number(v))
//         if (!feature) return

//         selectedNeighborhood.value = feature.properties

//         try {
//             neighborhoodStats.value = null
//             const stats = await loadNeighborhoodStats(v)
//             if (reqId !== statsReq) return
//             neighborhoodStats.value = stats
//         } catch (err) {
//             console.error(err)
//             if (reqId === statsReq) neighborhoodStats.value = null
//         }
//     },
//     { immediate: true }
// )

// -------- marker create state ----------
watch(
    markerLatLng,
    (v) => {
        if (v == null) return
        treeLayerApi?.clearSelection?.()
        hoveredData.value = null
        selectedData.value = null
        const c = map.value?.getCanvas?.()
        if (c) c.style.cursor = ''
    },
    { flush: 'post' }
)


// -------- FILTER + VISIBILITY (single watcher) ----------
watch(
    selectedFilter,
    (mode) => {
        whenLayerReady(map.value, 'trees-circle', () => {
            // if (!hasTreesLayer(map)) return
            treeVisualizationApi?.visualiseTreeData(mode)
            treeVisualizationApi?.applyVisibility(mode)
        })
    },
    { immediate: true }
)

// -------- EVENT MODE (fetch + recenter) ----------
const { hideSidebar } = useSidebar()

watch(
    () => [props.mode, props.eventId],
    async ([mode, eventId]) => {
        activeEvent.value = null
        eventError.value = null
        if (!(mode === 'planting' && eventId)) return

        hideSidebar()

        eventLoading.value = true
        try {
            const data = await fetchPlantingEvent(eventId)
            activeEvent.value = data
        } catch (e) {
            console.error(e)
            eventError.value = e?.message ?? 'Failed to load event'
        } finally {
            eventLoading.value = false
        }
    },
    { immediate: true }
)

watch(
    () => [map.value, activeEvent.value?.lat, activeEvent.value?.lon, isPlantingMode.value],
    async () => {
        if (!isPlantingMode.value) return
        await nextTick()
        if (!selectedData.value) {
            recenterToEventIfPossible()
        }
    },
    { immediate: true }
)

function recenterToEventIfPossible() {
    const m = map.value
    const ev = activeEvent.value
    if (!m || !ev?.lat || !ev?.lon) return

    m.easeTo({
        center: [Number(ev.lon), Number(ev.lat)],
        zoom: Math.max(m.getZoom(), 15),
    })
}

async function fetchPlantingEvent(id) {
    const res = await fetch(`/api/events/planting/${id}`)
    if (!res.ok) throw new Error(`Failed to load planting event ${id}: ${res.status}`)
    return await res.json()
}

// -------- VISUALIZATION / FILTERING ----------

function toggleCategory(payload) {
    treeVisualizationApi?.onToggleCategory(payload)
}

// -------- CRUD hooks ----------
function onClearSelection() {
    console.log('onClearSelection')
    treeLayerApi?.clearSelection?.()
    neighLayerApi?.clearSelection?.()
}

function onCancelCreate() {
    treeLayerApi?.clearSelection?.()
    markerLatLng.value = null
    longPressCtl?.hide?.()
}

function onCreateSuccess() {
    markerLatLng.value = null
    longPressCtl?.remove()
}

async function onTreeSaved(payload) {
    const propsObj = await fetchTreeDetails(payload.id)

    const existed = treeExistsInCollection(treeData.value, payload.id)
    const next = upsertTreeFeature(treeData.value, propsObj)

    treeData.value = next
    treeLayerApi?.setTreesData?.(next)

    if (!existed) {
        requestAnimationFrame(() => {
            treeLayerApi?.clearSelection?.()
            treeLayerApi?.selectTreeById?.(payload.id)
        })
    }

    if (selectedData.value?.id === propsObj.id) {
        selectedData.value = propsObj
    }

    if (isPlantingMode.value) {
        try {
            lastCreatedTree.value = propsObj
            await attachTreeToPlantingEvent(payload.id)
            if (activeEvent.value) {
                activeEvent.value = {
                    ...activeEvent.value,
                    event_trees_count: (Number(activeEvent.value.event_trees_count ?? 0) + 1),
                }
            }
        } catch (e) {
            console.error(e)
        }
    }
}

function attachTreeToPlantingEvent(treeId) {
    return new Promise((resolve, reject) => {
        router.post(
            route('plantingEventTrees.store', props.eventId),
            {
                tree_id: treeId,
                planted_at: new Date().toISOString(),
                planting_method: null,
                notes: null,
            },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onSuccess: (page) => {
                    resolve(page)
                },
                onError: (errors) => {
                    reject(errors)
                },
            }
        )
    })
}

function treeExistsInCollection(fc, id) {
    const numericId = Number(id)
    return !!fc?.features?.some(f => Number(f?.properties?.id ?? f?.id) === numericId)
}

function toPointFeature(treeProps) {
    const id = Number(treeProps.id)
    const lon = Number(treeProps.lon)
    const lat = Number(treeProps.lat)

    if (!Number.isFinite(id) || !Number.isFinite(lon) || !Number.isFinite(lat)) {
        throw new Error(`Invalid tree payload for feature: id=${treeProps.id} lon=${treeProps.lon} lat=${treeProps.lat}`)
    }

    return {
        type: 'Feature',
        id,
        geometry: { type: 'Point', coordinates: [lon, lat] },
        properties: treeProps,
    }
}

function upsertTreeFeature(featureCollection, treeProps) {
    const fc = featureCollection?.type === 'FeatureCollection'
        ? featureCollection
        : { type: 'FeatureCollection', features: [] }

    const next = {
        type: 'FeatureCollection',
        features: [...(fc.features ?? [])],
    }

    const nextFeature = toPointFeature(treeProps)
    const id = nextFeature.id
    const idx = next.features.findIndex(f => Number(f?.properties?.id ?? f?.id) === id)

    if (idx >= 0) next.features[idx] = nextFeature
    else next.features.push(nextFeature)

    return next
}


// -------- CLEANUP ----------
onBeforeUnmount(() => {
    const m = map.value
    if (m) {
        m.remove()
    }
    map.value = null

    longPressCtl?.cleanup?.()
    longPressCtl?.remove?.()
    longPressCtl = null

    treeLayerApi = null

    mapBus.off('tree:saved', onTreeSaved)
})
</script>
