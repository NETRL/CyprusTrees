import { ref, computed, watch, nextTick } from "vue"
import { MAP_MODES, useMapUiState } from "@/Lib/Map/useMapUiState"

export function useEventMode(mapRef, { initialModeRef, initialEventIdRef, getTreeLayerApi, } = {}) {
    const { ui, closeSidebar, setActiveMode } = useMapUiState()

    const mode = ref(initialModeRef.value)
    const eventId = ref(initialEventIdRef.value)
    const activeEvent = ref(null)
    const eventLoading = ref(false)
    const eventError = ref(null)

    const isEventMode = computed(() => (mode?.value !== "default" && !!eventId?.value))
    const activePlantingEventId = computed(() => (isPlantingMode.value ? eventId?.value : null))

    const isPlantingMode = computed(() => (mode?.value === "planting" && !!eventId?.value))

    if (!getTreeLayerApi) {
        throw new Error("useEventFunctions: getTreeLayerApi() is required")
    }

    if (isEventMode.value) {
        setActiveMode(MAP_MODES.EVENTS)
    }

    watch(isPlantingMode, v => {
        if (v) {
            setActiveMode(MAP_MODES.PLANTING)
        }
    }, { immediate: true })


    const onEventSelected = (ev) => {

        const treeLayerApi = getTreeLayerApi?.()

        if (ev.type === 'maintenance') {
            // setActiveMode(MAP_MODES.MAINTENANCE)
            treeLayerApi.selectTreeById(ev.meta.tree_id)
        }
    }

    const onEventFocused = (ev) => {
        if (ev.type === 'planting') {
            console.log('focused: ', ev)
            mode.value = ev.type
            eventId.value = ev.meta?.planting_id
        }

    }

    const onEventStart = (ev) => {
        console.log('start: ', ev)
    }

    const onEventComplete = (ev) => {
        console.log('complete: ', ev)
    }

    const terminateEventMode = () => {
        mode.value = null
        eventId.value = null
        activeEvent.value = null
        eventLoading.value = false
        eventError.value = null
    }

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
        () => [mode?.value, eventId?.value],
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
        activePlantingEventId,

        // state
        activeEvent,
        eventLoading,
        eventError,

        // actions
        recenterToEventIfPossible,
        onEventSelected,
        onEventStart,
        onEventComplete,
        onEventFocused,
        terminateEventMode,

    }
}
