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

    <TreeCard :hovered="hoveredData" :selected="selectedData" @update:selected="selectedData = $event" />
    <MapLoadingOverlay :isLoading="isLoading" />
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount, watch, computed, nextTick } from 'vue'
import MapSidebar from '@/Components/Map/Partials/MapSidebar.vue'
import MapLoadingOverlay from '@/Components/Map/Partials/MapLoadingOverlay.vue'
import TreeCard from '@/Components/Map/Partials/TreeCard.vue'

import { initMap } from '@/Lib/Map/InitMap'
import { setupBaseLayers } from '@/Lib/Map/SetupBaseLayers'
import { loadTreesLayer, loadNeighborhoodsLayer } from '@/Lib/Map/DataLayers'
import { useMapFilter } from '@/Composables/useMapFilter'
import { useMapColors } from '@/Composables/useMapColors'

import { useRealTimePosition } from '@/Lib/Map/useRealTimePosition'
import { LocateFixed, Crosshair, Loader2 } from 'lucide-vue-next'

const props = defineProps({
    initialTreeId: { type: Number, default: null },
})

const mapContainer = ref(null)
const map = ref(null)

const isLoading = ref(true)

const treeData = ref([])
const neighborhoodData = ref([])
const selectedData = ref(null)
const hoveredData = ref(null)
const toggleTreeCard = ref(null)

const center = [33.37, 35.17]
const zoom = 12

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
]

const { selectedFilter } = useMapFilter()
const { STATUS_COLORS, WATER_USE_COLORS, SHADE_COLORS, ORIGIN_COLORS, POLLEN_RISK_COLORS } = useMapColors()

const CATEGORY_KEYS = {
    status: STATUS_COLORS.filter((_, i) => i % 2 === 0),
    origin: ORIGIN_COLORS.filter((_, i) => i % 2 === 0),
    water_use: WATER_USE_COLORS.filter((_, i) => i % 2 === 0),
    shade: SHADE_COLORS.filter((_, i) => i % 2 === 0),
}

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

// IMPORTANT: update centered when we receive the first GPS fix or when user moves
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

        setupBaseLayers(m, {
            maptilerKey: MAPTILER_KEY,
            vectorStyles: CUSTOM_VECTOR_STYLES,
        })

        const [_, treesApi] = await Promise.all([
            loadNeighborhoodsLayer(m, {
                onDataLoaded: (data) => (neighborhoodData.value = data),
                onNeighborhoodSelected: (props) => (selectedData.value = props),
            }),
            loadTreesLayer(m, {
                onDataLoaded: (data) => (treeData.value = data),
                onTreeSelected: (props) => (selectedData.value = props),
                onTreeHovered: (props) => (hoveredData.value = props),
                setInitialFilter: (val) => (selectedFilter.value = val),
            }),
        ])

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

// -------- VISUALIZATION / FILTERING ----------
const visualiseTreeData = (mode) => {
    if (!map.value || !map.value.getLayer('trees-circle')) return

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
            map.value.setPaintProperty('trees-circle', 'circle-color', DEFAULT_COLOR)
            return
    }

    if (colorExpression) {
        map.value.setPaintProperty('trees-circle', 'circle-color', colorExpression)
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

const applyVisibility = (mode = selectedFilter.value) => {
    if (!map.value || !map.value.getLayer('trees-circle')) return

    const propName = modeToPropName[mode]
    if (!propName) {
        map.value.setFilter('trees-circle', null)
        return
    }

    const hidden = Array.from(hiddenCategories.value[mode] || [])
    if (!hidden.length) {
        map.value.setFilter('trees-circle', null)
        return
    }

    const filter = ['!', ['in', ['get', propName], ['literal', hidden]]]
    map.value.setFilter('trees-circle', filter)
}

watch(
    selectedFilter,
    (mode) => {
        if (!map.value || !map.value.getLayer('trees-circle')) return
        visualiseTreeData(mode)
        applyVisibility(mode)
    },
    { immediate: true }
)

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
})
</script>
