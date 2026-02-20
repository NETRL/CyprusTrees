import { loadNeighborhoodStats as defaultLoadNeighborhoodStats } from "@/Lib/Map/layers/neighborhoods/neighborhoodsLayer"
import { ref, watch } from "vue";

export function useNeighborhoodSelection({
    neighborhoodData,
    selectedNeighborhoodId,
    loadNeighborhoodStats = defaultLoadNeighborhoodStats,
} = {}) {
    const selectedNeighborhood = ref(null)
    const neighborhoodStats = ref(null)

    // stale-safe request guard
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
            const id = v.id
            const fc = neighborhoodData.value
            if (!fc?.features?.length) return
            
            const feature = fc.features.find(f => Number(f.id) === Number(id))
            if (!feature) return
            
            // on every click assign a new reference to maintain reactivity.
            selectedNeighborhood.value = feature ? { ...feature.properties } : null

            try {
                neighborhoodStats.value = null
                const stats = await loadNeighborhoodStats(id)
                if (reqId !== statsReq) return
                neighborhoodStats.value = stats
            } catch (err) {
                console.error(err)
                if (reqId === statsReq) neighborhoodStats.value = null
            }
        },
        { immediate: true }
    )

    return {
        selectedNeighborhood,
        neighborhoodStats,
    }
}