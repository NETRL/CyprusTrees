<template>
    <MapSidebar :treeData="treeData" :neighborhoodData="neighborhoodData" :selectedData="selectedData"
        :hiddenCategories="hiddenCategories" :currentMode="selectedFilter" @toggleCategory="onToggleCategory" />

    <!-- Event Mode Top Bar -->
    <div v-if="isPlantingMode" class="absolute top-3 left-3 right-3 z-40">
        <div class="rounded-2xl border border-gray-200 bg-white/90 backdrop-blur px-4 py-3 shadow
              dark:border-gray-800 dark:bg-slate-900/85">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Planting Event #{{ props.eventId }}
                        </span>

                        <span v-if="activeEvent?.status" class="rounded-md px-2 py-0.5 text-xs font-medium"
                            :class="statusPill(activeEvent.status)">
                            {{ activeEvent.status }}
                        </span>

                        <span v-if="eventLoading" class="text-xs text-gray-500 dark:text-gray-400">
                            Loading…
                        </span>

                        <span v-if="eventError" class="text-xs text-red-600 dark:text-red-300">
                            {{ eventError }}
                        </span>
                    </div>

                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                        <span v-if="activeEvent">
                            Trees: {{ activeEvent.event_trees_count ?? 0 }}
                            <template v-if="activeEvent.target_tree_count">/ {{ activeEvent.target_tree_count
                                }}</template>
                        </span>
                        <span v-if="activeEvent?.neighborhood?.name"> • {{ activeEvent.neighborhood.name }}</span>
                        <span v-if="activeEvent?.campaign?.name"> • {{ activeEvent.campaign.name }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Exit event mode -->
                    <button type="button" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium
                 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-white/5" @click="exitEventMode">
                        Exit
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div ref="mapContainer" class="map-container w-full h-full"></div>

    <button v-if="shouldShowButton" type="button"
        class="absolute right-4 bottom-7 z-30 grid h-11 w-11 place-items-center rounded-xl bg-white/90 shadow ring-1 ring-slate-200 backdrop-blur dark:bg-slate-900/90 dark:ring-slate-700 transition-colors"
        :disabled="position.isActive && !position.hasFix" @click="onFloatingButtonClick" aria-label="Locate / Recenter"
        title="Locate / Recenter">
        <component :is="iconComponent" class="h-5 w-5" :class="iconClass" />
    </button>

    <TreeCard :hovered="hoveredData" :selected="selectedData" :markerLatLng="markerLatLng"
        :selectedNeighborhood="selectedNeighborhood" :neighborhoodStats="neighborhoodStats" :pinClickFlag="pinClickFlag"
        @update:selected="selectedData = $event" @cancelCreate="onCancelCreate" @clearSelection="onClearSelection" />
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
import { onMounted, ref, onBeforeUnmount, watch, computed, nextTick, provide } from 'vue'
import MapSidebar from '@/Components/Map/Partials/MapSidebar.vue'
import MapLoadingOverlay from '@/Components/Map/Partials/MapLoadingOverlay.vue'
import TreeCard from '@/Components/Map/Partials/TreeCard.vue'
import Modal from '@/Components/Modal.vue'

import { initMap } from '@/Lib/Map/InitMap'
import { setupBaseLayers } from '@/Lib/Map/SetupBaseLayers'
import { loadTreesLayer, loadNeighborhoodsLayer, fetchTreeDetails, loadNeighborhoodStats } from '@/Lib/Map/DataLayers2'
import { useMapFilter } from '@/Composables/useMapFilter'
import { useMapColors } from '@/Composables/useMapColors'
import { useRealTimePosition } from '@/Lib/Map/useRealTimePosition'
import { LocateFixed, Crosshair, Loader2 } from 'lucide-vue-next'
import { storeNewTree } from '@/Lib/Map/LongClickFunctions'
import { usePermissions } from '@/Composables/usePermissions'
import { GisDataLayerManager } from '@/Lib/Map/GisDataLayerManager'

import mitt from 'mitt'
import { router } from '@inertiajs/vue3'
import { useSidebar } from '@/Composables/useSidebar'

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
const selectedData = ref(null)
const hoveredData = ref(null)

const neighborhoodStats = ref({});
const selectedNeighborhoodId = ref(null)
const selectedNeighborhood = ref(null)

const markerLatLng = ref(null)
const showAuthPrompt = ref(false)
const pinClickFlag = ref(0)

let longPressCtl = null
let treeLayerApi = null
let neighLayerApi = null
let gisMgr = null

const { selectedFilter } = useMapFilter()
const { STATUS_COLORS, WATER_USE_COLORS, SHADE_COLORS, ORIGIN_COLORS, POLLEN_RISK_COLORS } = useMapColors()

const isEventMode = computed(() => props.mode !== 'default' && !!props.eventId)
const isPlantingMode = computed(() => props.mode === 'planting' && !!props.eventId)
provide('isPlantingMode', isPlantingMode)

const activePlantingEventId = computed(() => props.mode === 'planting' ? props.eventId : null)
provide('activePlantingEventId', activePlantingEventId)

// --- event state ---
const activeEvent = ref(null)
const eventLoading = ref(false)
const eventError = ref(null)

const lastCreatedTree = ref(null)
provide('lastCreatedTree', lastCreatedTree)

// --- visibility state ---
const hiddenCategories = ref({
    status: new Set(),
    origin: new Set(),
    water_use: new Set(),
    shade: new Set(),
    pollen_risk: new Set(),
})

const CATEGORY_KEYS = {
    status: STATUS_COLORS.filter((_, i) => i % 2 === 0),
    origin: ORIGIN_COLORS.filter((_, i) => i % 2 === 0),
    water_use: WATER_USE_COLORS.filter((_, i) => i % 2 === 0),
    shade: SHADE_COLORS.filter((_, i) => i % 2 === 0),
}

const modeToPropName = {
    status: 'status',
    origin: 'species_origin',
    pollen_risk: 'calculated_pollen_risk',
    water_use: 'species_drought_tolerance',
    shade: 'species_canopy_class',
}

// --- helpers ---
function whenLayerReady(layerId, fn) {
    const m = map.value
    if (!m) return

    // Fast path: layer already exists
    if (m.getLayer(layerId)) {
        fn(m)
        return
    }

    // Otherwise wait until style updates include the layer
    const onStyleData = () => {
        if (!m.getLayer(layerId)) return
        m.off('styledata', onStyleData)
        fn(m)
    }

    m.on('styledata', onStyleData)
}


function hasTreesLayer(m) {
    return !!m?.getLayer?.('trees-circle')
}

// --- init ---
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
    // {
    //     id: 'landcapeDark',
    //     name: 'Landscape Dark',
    //     styleUrl: `https://api.maptiler.com/maps/019ac206-7965-7795-8035-d9b24b5c8815/style.json?key=${MAPTILER_KEY}`,
    //     preview: '/storage/images/map-custom.png',
    // },
    {
        id: 'satellite',
        name: 'Satellite View',
        styleUrl: `https://api.maptiler.com/maps/hybrid/style.json?key=${MAPTILER_KEY}`,
        preview: '/storage/images/map-default.png',
    },
    {
        id: 'street',
        name: 'Street View',
        styleUrl: `https://api.maptiler.com/maps/019c27fc-f979-75a2-8a6b-bbe7e6ce558b/style.json?key=${MAPTILER_KEY}`,
        preview: '/storage/images/map-default.png',
    },
    {
        id: 'pastel',
        name: 'Pastel View',
        styleUrl: `https://api.maptiler.com/maps/019ac201-8ff9-76ea-b752-4b2b1e4ed570/style.json?key=${MAPTILER_KEY}`,
        preview: '/storage/images/map-default.png',
    },
]

// --- bus ---
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

        // move/zoom/rotate tracking
        m.on('moveend', onMapMoveEnd)
        m.on('zoomend', onMapMoveEnd)
        m.on('rotateend', onMapMoveEnd)

        longPressCtl = storeNewTree(m, {
            onLatLng: (latLng) => { markerLatLng.value = latLng },
            requiresAuth: (v) => { showAuthPrompt.value = v },
            onPinClick: () => { pinClickFlag.value++ },
        })

        gisMgr = new GisDataLayerManager(m, {
            baseUrl: "/api/gis-map",
            controlPosition: "top-right",
            defaultVisibleKeys: [],     // optionally: ["irrigation_lines"]
            fetchBbox: true,
            reloadOnMoveEnd: false,     // turn on if you want dynamic refetch
        })

        await gisMgr.init()

        setupBaseLayers(m, {
            maptilerKey: MAPTILER_KEY,
            vectorStyles: CUSTOM_VECTOR_STYLES,
        })



        const [neighApi, treesApi] = await Promise.all([
            loadNeighborhoodsLayer(m, {
                onDataLoaded: (data) => { neighborhoodData.value = data },
                onNeighborhoodSelected: (id) => { selectedNeighborhoodId.value = id },
                isInteractionEnabled: () => markerLatLng.value == null,
            }),
            loadTreesLayer(m, {
                onDataLoaded: (data) => { treeData.value = data },
                onTreeSelected: (props) => { selectedData.value = props },
                onTreeHovered: (props) => { hoveredData.value = props },
                setInitialFilter: (val) => { selectedFilter.value = val },
                isInteractionEnabled: () => markerLatLng.value == null,
            }),
        ])

        treeLayerApi = treesApi
        neighLayerApi = neighApi

        // initial styling/filtering once layer is ready
        whenLayerReady('trees-circle', (m) => {
            visualiseTreeData(selectedFilter.value ?? 'status')
            applyVisibility(selectedFilter.value ?? 'status')
        })
        if (props.initialTreeId) {
            treesApi.selectTreeById(props.initialTreeId)
        }


        // centered state initial
        isCentered.value = computeCentered()
    } catch (e) {
        console.error(e)
    } finally {
        isLoading.value = false
    }
})

// -------- Neighborhood stats (stale-safe) ----------
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
            neighborhoodStats.value = null
            const stats = await loadNeighborhoodStats(v)
            if (reqId !== statsReq) return
            neighborhoodStats.value = stats
        } catch (err) {
            console.error(err)
            if (reqId === statsReq) neighborhoodStats.value = null
        }
    },
    { immediate: true }
)

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
        whenLayerReady('trees-circle', () => {
            // if (!hasTreesLayer(map)) return
            visualiseTreeData(mode)
            applyVisibility(mode)
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


function statusPill(status) {
    switch (status) {
        case 'draft': return 'bg-stone-100 text-stone-800 dark:bg-white/10 dark:text-stone-200'
        case 'scheduled': return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-200'
        case 'in_progress': return 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200'
        case 'completed': return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200'
        case 'cancelled': return 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200'
        default: return 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-200'
    }
}

function exitEventMode() {
    // just navigate to the map's route.
    router.visit(route('/'), { preserveState: true, preserveScroll: true })
}

// -------- REAL TIME POSITION ----------
const position = useRealTimePosition(map, { recenterOnFirstFix: true, })
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
        } catch (e) {
            console.warn(e)
        }
        return
    }

    // While active but no fix yet, ignore
    if (!position.hasFix.value) return

    // Recenter
    position.recenterOnce()

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
let lastPaintMode = null
const DEFAULT_COLOR = '#4A5568';
const TREE_POINT_LAYERS = [
    'trees-pin-bg',
    'trees-selection-pulse',
    'trees-selection-ring',
    'trees-hover-ring',
    'trees-circle-halo',
    'trees-circle',
];

function setTreePointFilter(m, extraFilter /* can be null */) {
    // base condition: only non-cluster points
    const base = ['!', ['has', 'point_count']];

    // combine base + optional extra
    const combined = extraFilter ? ['all', base, extraFilter] : base;

    for (const id of TREE_POINT_LAYERS) {
        if (m.getLayer(id)) m.setFilter(id, combined);
    }
}


const paintByMode = {
    status: () => ['match', ['get', 'status'], ...STATUS_COLORS],
    origin: () => ['match', ['get', 'species_origin'], ...ORIGIN_COLORS],
    pollen_risk: () => ['step', ['get', 'calculated_pollen_risk'], ...POLLEN_RISK_COLORS],
    water_use: () => ['match', ['get', 'species_drought_tolerance'], ...WATER_USE_COLORS],
    shade: () => ['match', ['get', 'species_canopy_class'], ...SHADE_COLORS],
}

function visualiseTreeData(mode) {
    if (mode === lastPaintMode) return
    lastPaintMode = mode

    const expr = paintByMode[mode]?.()

    const m = map.value
    if (!m || !hasTreesLayer(m)) return
    m.setPaintProperty('trees-circle', 'circle-color', expr ?? DEFAULT_COLOR)
}

// small cache to avoid re-allocating identical filters for same hidden set
const filterCache = new Map();

function applyVisibility(mode = selectedFilter.value) {
    const m = map.value;
    if (!m || !hasTreesLayer(m)) return;

    const propName = modeToPropName[mode];

    // If mode has no prop mapping, show all points (still non-cluster)
    if (!propName) {
        setTreePointFilter(m, null);
        return;
    }

    const set = hiddenCategories.value[mode];
    const predicate = makePredicateFromHidden(mode, set, modeToPropName);
    treeLayerApi?.setTreesDataFiltered(predicate);
    const hidden = set ? Array.from(set) : [];

    // Nothing hidden => show all points
    if (!hidden.length) {
        setTreePointFilter(m, null);
        return;
    }

    hidden.sort();
    const cacheKey = `${mode}:${hidden.join('|')}`;

    let extra = filterCache.get(cacheKey);
    if (!extra) {
        // Keep features that:
        // - do NOT have the property (don’t accidentally hide “unknowns”), OR
        // - have the property and it is NOT in the hidden list
        extra = [
            'any',
            ['!', ['has', propName]],
            ['!', ['in', ['get', propName], ['literal', hidden]]],
        ];
        filterCache.set(cacheKey, extra);
    }

    setTreePointFilter(m, extra);
}


function onToggleCategory({ mode, key }) {
    const hc = hiddenCategories.value ?? {};
    const currentSet = hc[mode] instanceof Set ? hc[mode] : new Set();

    if (key === 'all') {
        const allKeys = CATEGORY_KEYS[mode] || [];
        const anyHidden = currentSet.size > 0;

        const next = new Set();
        if (!anyHidden) allKeys.forEach(k => next.add(k));

        hiddenCategories.value = { ...hc, [mode]: next };
        applyVisibility(mode);
        return;
    }

    const next = new Set(currentSet);
    next.has(key) ? next.delete(key) : next.add(key);

    hiddenCategories.value = { ...hc, [mode]: next };
    applyVisibility(mode);
}

function makePredicateFromHidden(mode, hiddenSet, modeToPropName) {
    const prop = modeToPropName[mode];
    if (!prop || !hiddenSet || hiddenSet.size === 0) return () => true;

    return (f) => {
        const v = f?.properties?.[prop];
        // keep “unknown/missing” unless you explicitly want to hide it
        if (v == null) return true;
        return !hiddenSet.has(v);
    };
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
        m.off('moveend', onMapMoveEnd)
        m.off('zoomend', onMapMoveEnd)
        m.off('rotateend', onMapMoveEnd)
        m.remove()
    }
    map.value = null

    position.stop?.()

    longPressCtl?.cleanup?.()
    longPressCtl?.remove?.()
    longPressCtl = null

    treeLayerApi = null

    mapBus.off('tree:saved', onTreeSaved)
})
</script>
