import { ref, computed, watch, nextTick } from "vue"
import { useMapUiState } from "./useMapUiState"

export function useEventMode(mapRef, { modeRef, eventIdRef, fetchEvent } = {}) {
    const activeEvent = ref(null)
    const eventLoading = ref(false)
    const eventError = ref(null)

    const isEventMode = computed(() => modeRef?.value !== "default" && !!eventIdRef?.value)
    const isPlantingMode = computed(() => modeRef?.value === "planting" && !!eventIdRef?.value)
    const activePlantingEventId = computed(() => (isPlantingMode.value ? eventIdRef?.value : null))

    const { closeSidebar } = useMapUiState() 

    function recenterToEventIfPossible() {
        const m = mapRef?.value
        const ev = activeEvent.value
        if (!m || !ev?.lat || !ev?.lon) return

        m.easeTo({
            center: [Number(ev.lon), Number(ev.lat)],
            zoom: Math.max(m.getZoom(), 15),
        })
    }

    // fetch event when mode/id changes
    watch(
        () => [modeRef?.value, eventIdRef?.value],
        async ([mode, eventId]) => {
            activeEvent.value = null
            eventError.value = null

            if (!(mode === "planting" && eventId)) return

            closeSidebar()

            eventLoading.value = true
            try {
                const data = await fetchPlantingEvent(eventId)
                activeEvent.value = data
            } catch (e) {
                console.error(e)
                eventError.value = e?.message ?? "Failed to load event"
            } finally {
                eventLoading.value = false
            }
        },
        { immediate: true }
    )

    // recenter when we have a map + active event + planting mode
    watch(
        () => [mapRef?.value, activeEvent.value?.lat, activeEvent.value?.lon, isPlantingMode.value],
        async () => {
            if (!isPlantingMode.value) return
            await nextTick()
            recenterToEventIfPossible()
        },
        { immediate: true }
    )

    async function fetchPlantingEvent(id) {
        const res = await fetch(`/api/events/planting/${id}`)
        if (!res.ok) throw new Error(`Failed to load planting event ${id}: ${res.status}`)
        return await res.json()
    }

    return {
        // flags
        isEventMode,
        isPlantingMode,
        activePlantingEventId,

        // state
        activeEvent,
        eventLoading,
        eventError,

        // actions
        recenterToEventIfPossible,
    }
}
