<template>
    <button v-if="shouldShowButton" type="button"
        class="absolute right-2 bottom-7 z-30 grid h-8.5 w-8.5 place-items-center rounded-md bg-white/90 shadow ring-1 ring-slate-200 backdrop-blur dark:bg-slate-900/90 dark:ring-slate-700 transition-colors"
        :disabled="position.isActive && !position.hasFix" @click="onFloatingButtonClick" aria-label="Locate / Recenter"
        title="Locate / Recenter">
        <component :is="iconComponent" class="h-5 w-5" :class="iconClass" />
    </button>
</template>


<script setup>
import { useRealTimePosition } from '@/Lib/Map/useRealTimePosition';
import { Crosshair, Loader2, LocateFixed } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';


const props = defineProps({
    mapRef: { type: Object, default: null },
})

const map = computed(() => props.mapRef ?? null)

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

function onMapMoveEnd() {
    // when user pans/zooms, reevaluate whether we’re centered
    isCentered.value = computeCentered()
}

function computeCentered() {
    const m = map.value
    const last = position.lastLngLat.value
    if (!m || !last) return false

    const c = m.getCenter()
    const [uLng, uLat] = last
    const thresholdM = 35
    return distanceMeters([c.lng, c.lat], [uLng, uLat]) <= thresholdM
}

watch(
    () => position.lastLngLat.value,
    () => {
        // if the map exists, compute centered as soon as we have a fix
        if (!map.value) return
        isCentered.value = computeCentered()
    }
)

// Bind/unbind map listeners whenever the map instance appears/changes.
watch(
    () => map.value,
    (m, prev) => {
        if (prev) {
            prev.off('moveend', onMapMoveEnd)
            prev.off('zoomend', onMapMoveEnd)
            prev.off('rotateend', onMapMoveEnd)
        }

        if (m) {
            m.on('moveend', onMapMoveEnd)
            m.on('zoomend', onMapMoveEnd)
            m.on('rotateend', onMapMoveEnd)
            isCentered.value = computeCentered()
        }
    },
    { immediate: true }
)

async function onFloatingButtonClick() {
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


onBeforeUnmount(() => {
    position.stop?.()

    const m = map.value
    if (m) {
        m.off('moveend', onMapMoveEnd)
        m.off('zoomend', onMapMoveEnd)
        m.off('rotateend', onMapMoveEnd)
    }
})

</script>