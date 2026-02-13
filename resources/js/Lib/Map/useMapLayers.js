import { isRef, ref } from "vue";
import { GisDataLayerManager } from "./GisDataLayerManager";
import { loadNeighborhoodsLayer, loadTreesLayer } from "./DataLayers2";

export function useMapLayers(map, options = {}) {

    const isLoading = ref(true)

    let treeLayerApi = null
    let neighLayerApi = null
    let gisLayerApi = null


    function makeSetter(target) {
        // target can be a ref or a function
        if (!target) return null
        if (typeof target === "function") return target
        if (isRef(target)) return (v) => { target.value = v }
        return null
    }

    function makeInteractionFn(src) {
        // src can be function | ref<boolean> | boolean | undefined
        if (typeof src === "function") return () => !!src()
        if (isRef(src)) return () => !!src.value
        if (typeof src === "boolean") return () => src
        return () => true
    }

    async function initializeLayers() {
        isLoading.value = true

        const isInteractionEnabled = makeInteractionFn(options.isInteractionEnabled)

        // Turn refs/functions into consistent setters
        const setNeighborhoodData = makeSetter(options.onNeighborhoodData)
        const setNeighborhoodSelected = makeSetter(options.onNeighborhoodSelected)

        const setTreeData = makeSetter(options.onTreeData)
        const setTreeSelected = makeSetter(options.onTreeSelected)
        const setTreeHovered = makeSetter(options.onTreeHovered)
        const setInitialFilter = makeSetter(options.onInitialFilter)

        // GIS data manager
        gisLayerApi = new GisDataLayerManager(map, {
            baseUrl: "/api/gis-map",
            controlPosition: "top-right",
            defaultVisibleKeys: [],     // optionally: ["irrigation_lines"]
            fetchBbox: true,
            reloadOnMoveEnd: false,     // turn on for dynamic refetch
        })
        await gisLayerApi.init()

            // Data layers
            ;[neighLayerApi, treeLayerApi] = await Promise.all([
                loadNeighborhoodsLayer(map, {
                    onDataLoaded: (data) => setNeighborhoodData?.(data),
                    onNeighborhoodSelected: (id) => setNeighborhoodSelected?.(id),
                    isInteractionEnabled,
                }),
                loadTreesLayer(map, {
                    onDataLoaded: (data) => setTreeData?.(data),
                    onTreeSelected: (props) => setTreeSelected?.(props),
                    onTreeHovered: (props) => setTreeHovered?.(props),
                    setInitialFilter: (val) => setInitialFilter?.(val),
                    isInteractionEnabled,
                }),
            ])

        isLoading.value = false
        return { treeLayerApi, neighLayerApi, gisLayerApi }
    }

    return {
        isLoading,
        initializeLayers,
        getTreeLayerApi: () => treeLayerApi,
        getNeighLayerApi: () => neighLayerApi,
        getGisLayerApi: () => gisLayerApi,
    }


}