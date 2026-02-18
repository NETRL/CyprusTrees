import { ref } from "vue";
import { MAP_MODES, useMapUiState } from "./useMapUiState";

export function useEventFunctions(mapRef, {
    getTreeLayerApi, // () => api | null
} = {}) {

    if (!getTreeLayerApi) {
        throw new Error("useEventFunctions: getTreeLayerApi() is required")
    }

    const map = ref(mapRef)

    const { setMapMode } = useMapUiState()


    const onEventSelected = (ev) => {

        const treeLayerApi = getTreeLayerApi?.()

        if (ev.type === 'maintenance') {
            // setMapMode(MAP_MODES.MAINTENANCE)
            treeLayerApi.selectTreeById(ev.meta.tree_id)
        }
    }


    return {
        onEventSelected
    }

}