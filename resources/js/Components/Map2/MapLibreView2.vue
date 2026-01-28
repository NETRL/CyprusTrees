<template>
    <MapSidebar :treeData="treeData" :neighborhoodData="neighborhoodData" :selectedData="selectedData"
        :hiddenCategories="hiddenCategories" :currentMode="selectedFilter" @toggleCategory="onToggleCategory" />

    <div ref="mapContainer" class="map-container w-full h-full"></div>

    <button v-if="shouldShowButton" type="button"
        class="absolute right-4 bottom-7 z-30 grid h-11 w-11 place-items-center rounded-xl bg-white/90 shadow ring-1 ring-slate-200 backdrop-blur dark:bg-slate-900/90 dark:ring-slate-700 transition-colors"
        :disabled="position.isActive && !position.hasFix" @click="onFloatingButtonClick" aria-label="Locate / Recenter"
        title="Locate / Recenter">
        <component :is="iconComponent" class="h-5 w-5" :class="iconClass" />
    </button>

    <TreeCard :hovered="hoveredData" :selected="selectedData" :markerLatLng="markerLatLng"
        :selectedNeighborhood="selectedNeighborhood" :neighborhoodStats="neighborhoodStats"
        @update:selected="selectedData = $event" @cancelCreate="onCancelCreate" :pinClickFlag="pinClickFlag" />
    <MapLoadingOverlay :isLoading="isLoading" />

    <Modal v-if="showAuthPrompt" @close="showAuthPrompt = false">
        <template #body>
            <div
                class="no-scrollbar relative w-auto h-auto overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
                <!-- close btn -->
                <button @click="showAuthPrompt = false"
                    class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                            fill="" />
                    </svg>
                </button>
                <div class="px-2 pr-14">
                    <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                        Login is Required
                    </h4>
                    <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
                        To access this feature you need to be logged in!
                    </p>
                </div>

                <div class="flex items-center gap-3 px-2 mt-6 lg:justify-end">
                    <button @click="showAuthPrompt = false" type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/3 sm:w-auto">
                        Close
                    </button>
                    <Link :href="route('login')"
                        class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                    Click to Login
                    </Link>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount, watch, computed, nextTick, onUnmounted, inject, provide } from 'vue'
import MapSidebar from '@/Components/Map/Partials/MapSidebar.vue'
import MapLoadingOverlay from '@/Components/Map/Partials/MapLoadingOverlay.vue'
import TreeCard from '@/Components/Map/Partials/TreeCard.vue'

import { initMap } from '@/Lib/Map/InitMap'
import { setupBaseLayers } from '@/Lib/Map/SetupBaseLayers'
import { loadTreesLayer, loadNeighborhoodsLayer, fetchTreeDetails, loadNeighborhoodStats } from '@/Lib/Map/DataLayers2'
import { useMapFilter } from '@/Composables/useMapFilter'
import { useMapColors } from '@/Composables/useMapColors'

import { useRealTimePosition } from '@/Lib/Map/useRealTimePosition'
import { LocateFixed, Crosshair, Loader2, Users } from 'lucide-vue-next'
import { storeNewTree } from '@/Lib/Map/LongClickFunctions'
import Modal from '@/Components/Modal.vue'
import { usePermissions } from '@/Composables/usePermissions'
import mitt from 'mitt'

const props = defineProps({
    initialTreeId: { type: Number, default: null },
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
const selectedData = ref(null)
const selectedNeighborhoodId = ref(null)
const selectedNeighborhood = ref(null)
const neighborhoodStats = ref({});
const hoveredData = ref(null)
const toggleTreeCard = ref(null)
const markerLatLng = ref(null)
let longPressCtl = null
const showAuthPrompt = ref(false)
const pinClickFlag = ref(0)

const center = [33.37, 35.17]
const zoom = 12

let dataLayerApi = null

const MAPTILER_KEY = import.meta.env.VITE_MAPTILER_KEY
const CUSTOM_VECTOR_STYLES = [
    {
        id: 'darkGreen',
        name: 'Dark Green',
        styleUrl: `https://api.maptiler.com/maps/019abffb-04c2-7927-8c58-ff64512e9321/style.json?key=${MAPTILER_KEY}`,
        preview: '/storage/images/map-custom.png',
    },
    {
        id: 'landcapeDark',
        name: 'Landscape Dark',
        styleUrl: `https://api.maptiler.com/maps/019ac206-7965-7795-8035-d9b24b5c8815/style.json?key=${MAPTILER_KEY}`,
        preview: '/storage/images/map-custom.png',
    },
    {
        id: 'satellite',
        name: 'Satellite View',
        styleUrl: `https://api.maptiler.com/maps/hybrid/style.json?key=${MAPTILER_KEY}`,
        preview: '/storage/images/map-custom.png',
    },
]

const { selectedFilter } = useMapFilter()
const { STATUS_COLORS, WATER_USE_COLORS, SHADE_COLORS, ORIGIN_COLORS, POLLEN_RISK_COLORS } = useMapColors()

const CATEGORY_KEYS = {
    status: STATUS_COLORS.filter((_, i) => i % 2 === 0),
    origin: ORIGIN_COLORS.filter((_, i) => i % 2 === 0),
    water_use: WATER_USE_COLORS.filter((_, i) => i % 2 === 0),
    shade: SHADE_COLORS.filter((_, i) => i % 2 === 0),
}

mapBus.on('tree:saved', onTreeSaved)

// -------- MAP INIT ----------
onMounted(async () => {
    try {
        const { map: m } = await initMap(mapContainer.value, {
            center,
            zoom,
            styleUrl: CUSTOM_VECTOR_STYLES[0].styleUrl,
        })

        map.value = m

        // track when user pans/zooms away so we can re-show recenter
        map.value.on('moveend', onMapMoveEnd)
        map.value.on('zoomend', onMapMoveEnd)
        map.value.on('rotateend', onMapMoveEnd)

        longPressCtl = storeNewTree(map.value, {
            onLatLng: (latLng) => {
                markerLatLng.value = latLng
            },
            requiresAuth: (v) => (showAuthPrompt.value = v),
            onPinClick: (v) => { pinClickFlag.value++; }
        })

        setupBaseLayers(m, {
            maptilerKey: MAPTILER_KEY,
            vectorStyles: CUSTOM_VECTOR_STYLES,
        })

        const [_, treesApi] = await Promise.all([
            loadNeighborhoodsLayer(m, {
                onDataLoaded: (data) => (neighborhoodData.value = data),
                onNeighborhoodSelected: (props) => { selectedNeighborhoodId.value = props; },
                isInteractionEnabled: () => markerLatLng.value == null,
            }),
            loadTreesLayer(m, {
                onDataLoaded: (data) => { treeData.value = data; allTreeData.value = data },
                onTreeSelected: (props) => (selectedData.value = props),
                onTreeHovered: (props) => (hoveredData.value = props),
                setInitialFilter: (val) => (selectedFilter.value = val),
                isInteractionEnabled: () => markerLatLng.value == null,
            }),
        ])

        dataLayerApi = treesApi

        if (map.value && map.value.getLayer('trees-circle')) {
            visualiseTreeData(selectedFilter.value ?? 'status')
        }

        if (props.initialTreeId) {
            treesApi.selectTreeById(props.initialTreeId)
        }

        // ensure centered state is correct on load (in case location already known)
        isCentered.value = computeCentered()
    } catch (e) {
        console.error(e)
    } finally {
        isLoading.value = false
    }
})

let statsReq = 0
watch(
    selectedNeighborhoodId,
    async (v) => {
        const reqId = ++statsReq
        if (!v) {
            selectedNeighborhood.value = null
            neighborhoodStats.value = null
            return
        }

        const fc = neighborhoodData.value
        if (!fc?.features?.length) return

        const feature = fc.features.find(f => Number(f.id) === Number(v))
        if (!feature) return

        selectedNeighborhood.value = feature.properties

        try {
            neighborhoodStats.value = null // το show loading state
            const stats = await loadNeighborhoodStats(v)
            if (reqId !== statsReq) return // stale response
            neighborhoodStats.value = stats
        } catch (err) {
            console.error(err)
            neighborhoodStats.value = null
        }
    },
    { immediate: true }
)

watch(selectedData, v => {
    console.log('selectedData', v)
})

const closeModal = () => {
    showAuthPrompt.value = false
}

// -------- WATCHERS ----------
watch(
    selectedFilter,
    (mode) => {
        if (!map.value || !map.value.getLayer('trees-circle')) return
        visualiseTreeData(mode)
    },
    { immediate: true }
)

watch(hoveredData, (data) => {
    if (!data) {
        toggleTreeCard.value = false
        return
    }
    toggleTreeCard.value = true
})

watch(markerLatLng, (v) => {
    if (v != null) {
        dataLayerApi?.clearSelection?.()
        hoveredData.value = null
        selectedData.value = null
        map.value?.getCanvas() && (map.value.getCanvas().style.cursor = '')
    }
})

// -------- REAL TIME POSITION ----------
const position = useRealTimePosition(map, {
    recenterOnFirstFix: true,
})

// Hide recenter button when we're already centered
const isCentered = ref(false)

const shouldShowButton = computed(() => {
    // show when inactive (so user can start)
    if (!position.isActive.value) return true

    // while waiting for first fix: show (but disabled)
    if (!position.hasFix.value) return true

    // active + has fix: only show if NOT centered
    return !isCentered.value
})

const iconComponent = computed(() => {
    if (!position.isActive.value) return LocateFixed
    if (!position.hasFix.value) return Loader2
    return Crosshair
})

const iconClass = computed(() => {
    if (position.isActive.value && !position.hasFix.value) return 'animate-spin'
    return ''
})

function computeCentered() {
    if (!map.value || !position.lastLngLat.value) return false

    const c = map.value.getCenter()
    const [uLng, uLat] = position.lastLngLat.value

    const thresholdM = 35
    return distanceMeters([c.lng, c.lat], [uLng, uLat]) <= thresholdM
}

function onMapMoveEnd() {
    // when user pans/zooms, reevaluate whether we’re centered
    isCentered.value = computeCentered()
}

const onFloatingButtonClick = async () => {
    // First click -> request permissions + start tracking
    if (!position.isActive.value) {
        try {
            await position.start()
            // If your composable does NOT auto-recenter on first fix, leave recenterOnFirstFix false and do it here.
            // Since recenterOnFirstFix is true, we just wait for the first fix.
        } catch (e) {
            console.warn(e)
        }
        return
    }

    // While active but no fix yet, ignore
    if (!position.hasFix.value) return

    // Recenter
    position.recenterOnce()

    // After animation ends, 'moveend' will run and update isCentered
    // But to be extra safe (some cases no moveend fires if already centered),
    // schedule a recompute after the next tick + small delay.
    await nextTick()
    window.setTimeout(() => {
        isCentered.value = computeCentered()
    }, 250)
}

// Update centered when we receive the first GPS fix or when user moves
watch(
    () => position.lastLngLat.value,
    () => {
        // if the map exists, compute centered as soon as we have a fix
        if (!map.value) return
        isCentered.value = computeCentered()
    }
)

function distanceMeters([lng1, lat1], [lng2, lat2]) {
    const R = 6378137
    const toRad = (d) => (d * Math.PI) / 180
    const dLat = toRad(lat2 - lat1)
    const dLng = toRad(lng2 - lng1)

    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
        Math.sin(dLng / 2) * Math.sin(dLng / 2)

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
    return R * c
}

// -------- VISUALIZATION / FILTERING ----------
const visualiseTreeData = (mode) => {
    if (!map.value || !map.value.getLayer('trees-circle')) return

    let layerId = 'circle-color'
    // if (window.location.pathname.startsWith('/map2')) {
    //     layerId = 'icon-color'
    // }

    let propertyKey
    let colorExpression
    const DEFAULT_COLOR = '#16a34a'

    switch (mode) {
        case 'status':
            propertyKey = ['get', 'status']
            colorExpression = ['match', propertyKey, ...STATUS_COLORS]
            break
        case 'origin':
            propertyKey = ['get', 'species_origin']
            colorExpression = ['match', propertyKey, ...ORIGIN_COLORS]
            break
        case 'pollen_risk':
            propertyKey = ['get', 'calculated_pollen_risk']
            colorExpression = ['step', propertyKey, ...POLLEN_RISK_COLORS]
            break
        case 'water_use':
            propertyKey = ['get', 'species_drought_tolerance']
            colorExpression = ['match', propertyKey, ...WATER_USE_COLORS]
            break
        case 'shade':
            propertyKey = ['get', 'species_canopy_class']
            colorExpression = ['match', propertyKey, ...SHADE_COLORS]
            break
        default:
            map.value.setPaintProperty('trees-circle', layerId, DEFAULT_COLOR)
            return
    }

    if (colorExpression) {
        map.value.setPaintProperty('trees-circle', layerId, colorExpression)
    }
}

const hiddenCategories = ref({
    status: new Set(),
    origin: new Set(),
    water_use: new Set(),
    shade: new Set(),
    pollen_risk: new Set(),
})

const modeToPropName = {
    status: 'status',
    origin: 'species_origin',
    pollen_risk: 'calculated_pollen_risk',
    water_use: 'species_drought_tolerance',
    shade: 'species_canopy_class',
}

const onToggleCategory = ({ mode, key }) => {
    if (!hiddenCategories.value[mode]) hiddenCategories.value[mode] = new Set()
    const currentSet = hiddenCategories.value[mode]

    if (key === 'all') {
        const allKeys = CATEGORY_KEYS[mode] || []
        const anyHidden = currentSet.size > 0
        const next = new Set()
        if (!anyHidden) allKeys.forEach((k) => next.add(k))

        hiddenCategories.value = { ...hiddenCategories.value, [mode]: next }
        applyVisibility(mode)
        return
    }

    const next = new Set(currentSet)
    next.has(key) ? next.delete(key) : next.add(key)

    hiddenCategories.value = { ...hiddenCategories.value, [mode]: next }
    applyVisibility(mode)
}

const NOT_CLUSTER = ['!', ['has', 'point_count']]
const allTreeData = ref(null)      // master FeatureCollection 
const visibleTreeData = ref(null)  // what we feed to the source (optional)

function buildFilteredTrees(mode) {
    const fc = allTreeData.value
    if (!fc?.features) return fc

    const propName = modeToPropName[mode]
    const hidden = Array.from(hiddenCategories.value[mode] || [])

    // no mode/prop or nothing hidden => show all
    if (!propName || hidden.length === 0) return fc

    const hiddenSet = new Set(hidden)

    return {
        type: 'FeatureCollection',
        features: fc.features.filter(f => {
            const v = f?.properties?.[propName]
            return !hiddenSet.has(v)
        }),
    }
}


const applyVisibility = (mode = selectedFilter.value) => {
    if (!map.value) return
    if (!allTreeData.value) return

    const filtered = buildFilteredTrees(mode)
    visibleTreeData.value = filtered

    // IMPORTANT: this makes clusters + counts recompute
    dataLayerApi?.setTreesData?.(filtered)
}


watch(
    selectedFilter,
    (mode) => {
        if (!map.value) return
        visualiseTreeData(mode)
        applyVisibility(mode)
    },
    { immediate: true }
)

function onCancelCreate() {
    dataLayerApi?.clearSelection?.()
    markerLatLng.value = null
    longPressCtl?.hide()    // or remove()
}

function onCreateSuccess() {
    markerLatLng.value = null
    longPressCtl?.remove()
}

async function onTreeSaved(payload) {
    const props = await fetchTreeDetails(payload.id) // returns properties object

    const existed = treeExistsInCollection(treeData.value, payload.id)

    // upsert into FeatureCollection
    const next = upsertTreeFeature(treeData.value, props)
    treeData.value = next

    // push into map source 
    dataLayerApi?.setTreesData?.(next)

    // if this was a brand new feature, auto-select it
    if (!existed) {
        // ensure map has applied the new source data before querying/selecting
        requestAnimationFrame(() => {
            dataLayerApi?.clearSelection?.()
            dataLayerApi?.selectTreeById?.(payload.id)
        })
    }

    // keep selection synced
    if (selectedData.value?.id === props.id) {
        selectedData.value = props
    }
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
        id, // good for promoteId/feature-state
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
    if (map.value) {
        map.value.off('moveend', onMapMoveEnd)
        map.value.off('zoomend', onMapMoveEnd)
        map.value.off('rotateend', onMapMoveEnd)
        map.value.remove()
        map.value = null
    }
    position.stop?.()
    longPressCtl?.cleanup()
    longPressCtl?.remove()
    dataLayerApi = null
    mapBus.off('tree.saved', onTreeSaved)
})
</script>
