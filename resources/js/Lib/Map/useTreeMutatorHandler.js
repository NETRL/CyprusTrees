import { fetchTreeDetails } from '@/Lib/Map/layers/trees/treesLayer'
import { router } from '@inertiajs/vue3'
import { MAP_MODES, useMapUiState } from '@/Lib/Map/useMapUiState'

export function useTreeMutatorHandler({
    // state refs
    treeData,
    selectedTree,

    // layer access (choose one)
    getTreeLayerApi, // () => api | null

    // planting/event context
    initialEventId,            // ref/computed number | null
    activeEvent,        // ref
    lastCreatedTree,    // ref
} = {}) {
    if (!treeData || !selectedTree) {
        throw new Error("useTreeMutatorHandler: treeData and selectedTree refs are required")
    }
    if (!getTreeLayerApi) {
        throw new Error("useTreeMutatorHandler: getTreeLayerApi() is required")
    }

    const { ui } = useMapUiState()

    async function onTreeUpdated(payload) {
        const id = payload?.id
        if (!id) return

        const propsObj = await fetchTreeDetails(id)

        const next = upsertTreeFeature(treeData.value, propsObj)
        treeData.value = next

        requestAnimationFrame(() => {
            const api = getTreeLayerApi?.()
            api?.setTreesData?.(next)
            api?.selectTreeById?.(id)
        })
        selectedTree.value = propsObj
    }

    async function onTreeSaved(payload) {
        const id = payload?.id
        if (!id) return

        const propsObj = await fetchTreeDetails(id)

        const existed = treeExistsInCollection(treeData.value, id)
        const next = upsertTreeFeature(treeData.value, propsObj)

        treeData.value = next

        const treeLayerApi = getTreeLayerApi?.()
        treeLayerApi?.setTreesData?.(next)

        if (!existed) {
            requestAnimationFrame(() => {
                const api = getTreeLayerApi?.()
                api?.clearSelection?.()
                api?.selectTreeById?.(id)
            })
        }

        if (selectedTree.value?.id === propsObj.id) {
            selectedTree.value = propsObj
        }

        if (ui.activeMode === MAP_MODES.PLANTING) {
            try {
                if (lastCreatedTree) lastCreatedTree.value = propsObj

                if (initialEventId) {
                    await attachTreeToPlantingEvent({ treeId: id, initialEventId })

                    if (activeEvent?.value) {
                        activeEvent.value = {
                            ...activeEvent.value,
                            event_trees_count: Number(activeEvent.value.event_trees_count ?? 0) + 1,
                        }
                    }
                }
            } catch (e) {
                console.error(e)
            }
        }
    }
    return {
        onTreeSaved,
        onTreeUpdated,
    }
}

// ----------------- helpers -----------------

function attachTreeToPlantingEvent({ treeId, initialEventId }) {
    return new Promise((resolve, reject) => {
        router.post(
            route('plantingEventTrees.store', initialEventId),
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
                onSuccess: (page) => { resolve(page) },
                onError: (errors) => { reject(errors) },
            }
        )
    })
}


function treeExistsInCollection(fc, id) {
    const numericId = Number(id)
    return !!fc?.features?.some((f) => Number(f?.properties?.id ?? f?.id) === numericId)
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