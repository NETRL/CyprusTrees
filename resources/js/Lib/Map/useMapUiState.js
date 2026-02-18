import { computed, inject, provide, reactive, readonly } from "vue"

const MapUiStateSymbol = Symbol('MapUiState')

export const MAP_PANELS = Object.freeze({
    NONE: null,
    TREE: "tree",           // selected/hovered tree
    TREE_FORM: "tree_form",
    NEIGHBORHOOD: "neighborhood",
    EVENTS: "events",       // "Show my events"
    SELECTION: "selection", // selected trees summary (phase 2+)
})

export const MAP_MODES = Object.freeze({
    NONE: null,                   // initial map (no mode)
    PLANTING: "planting",
    MAINTENANCE: "maintenance",
    SELECT_TREES: "selectTrees",   // phase 2
    DRAW_BOUNDARY: "drawBoundary", // phase 2
})

export function useMapUiStateProvider() {

    const state = reactive({
        sidebarState: true,
        activePanel: MAP_PANELS.NONE,
        mapMode: MAP_MODES.NONE,

        // lightweight “panel context”
        events: {
            query: "",
            status: "active", // active/upcoming/all etc
        },

        selection: {
            treeIds: [],
            geometry: null,
            stats: null,
        },
    })

    const isSidebarOpen = computed(() => !!state.sidebarState)

    const isPanelOpen = computed(() => !!state.activePanel)

    function openSidebar() {
        state.sidebarState = true
    }

    function closeSidebar() {
        state.sidebarState = false
    }

    function toggleSidebar() {
        state.sidebarState = !state.sidebarState
    }

    function openPanel(key) {
        state.activePanel = key
    }

    function closePanel() {
        state.activePanel = MAP_PANELS.NONE
    }

    function togglePanel(key) {
        state.activePanel = state.activePanel === key ? MAP_PANELS.NONE : key
    }

    function setMapMode(mode) {
        state.mapMode = mode
    }

    function resetSelection() {
        state.selection.treeIds = []
        state.selection.geometry = null
        state.selection.stats = null
    }

    const context = {
        ui: readonly(state),
        isSidebarOpen,
        isPanelOpen,
        // mutators
        openSidebar,
        closeSidebar,
        toggleSidebar,
        openPanel,
        closePanel,
        togglePanel,
        setMapMode,
        resetSelection,
    }

    provide(MapUiStateSymbol, context)

    return context
}


export function useMapUiState() {
    const context = inject(MapUiStateSymbol)
    if (!context) {
        throw new Error(
            'useMapUiState must be used within a component that has MapUiStateProvider as an ancestor'
        )
    }
    return context
}
