import { useMapColors } from "@/Composables/useMapColors";
import { ref, watch } from "vue";
import { hasLayer, whenLayerReady } from "@/Lib/Map/core/useMapStyleUtils";

export function useTreeVisualization(mapRef, { onHiddenCategories, onPredicateSet, selectedFilterRef, baseMapFilterRef, basePredicateRef } = {}) {

    const map = ref(mapRef)

    const hiddenCategories = ref({
        status: new Set(),
        origin: new Set(),
        water_use: new Set(),
        shade: new Set(),
        pollen_risk: new Set(),
    })

    const { STATUS_COLORS, WATER_USE_COLORS, SHADE_COLORS, ORIGIN_COLORS, POLLEN_RISK_COLORS } = useMapColors()

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

    const DEFAULT_COLOR = '#4A5568';
    const TREE_POINT_LAYERS = [
        'trees-pin-bg',
        'trees-selection-pulse',
        'trees-selection-ring',
        'trees-hover-ring',
        'trees-circle-halo',
        'trees-circle',
    ];
    const filterCache = new Map(); // small cache to avoid re-allocating identical filters for same hidden set
    let lastPaintMode = null

    const paintByMode = {
        status: () => ['match', ['get', 'status'], ...STATUS_COLORS],
        origin: () => ['match', ['get', 'species_origin'], ...ORIGIN_COLORS],
        pollen_risk: () => ['step', ['get', 'calculated_pollen_risk'], ...POLLEN_RISK_COLORS],
        water_use: () => ['match', ['get', 'species_drought_tolerance'], ...WATER_USE_COLORS],
        shade: () => ['match', ['get', 'species_canopy_class'], ...SHADE_COLORS],
    }

    watch(hiddenCategories, (data) => {
        onHiddenCategories?.(data)
    })

    watch(
        selectedFilterRef,
        (mode) => {
            whenLayerReady(map.value, 'trees-circle', () => {
                visualiseTreeData(mode ?? "status");
                applyVisibility(mode ?? "status");
            })
        },
        { immediate: true }
    )

    watch(
        () => [ baseMapFilterRef?.(), basePredicateRef?.() ],
        () => applyVisibility(selectedFilterRef.value ?? 'status'),
        // { immediate: true }
    )

    // watch(
    //     () => basePredicateRef?.(),
    //     () => applyVisibility(selectedFilterRef.value ?? 'status'),
    //     // { immediate: true }
    // )

    function visualiseTreeData(mode = 'status') {
        const m = map.value
        if (!hasLayer(m, 'trees-circle')) return

        if (mode === lastPaintMode) return
        lastPaintMode = mode

        const expr = paintByMode[mode]?.()

        m.setPaintProperty('trees-circle', 'circle-color', expr ?? DEFAULT_COLOR)
    }


    function applyVisibility(mode = 'status') {
        const m = map.value
        if (!hasLayer(m, 'trees-circle')) return

        const baseExtra = baseMapFilterRef?.() ?? null
        
        //  base predicate (event restriction)
        const basePredicate = basePredicateRef?.() ?? (() => true)

        // If mode isn't valid, fall back to status
        const effectiveMode = modeToPropName[mode] ? mode : 'status'
        const propName = modeToPropName[effectiveMode] // e.g. 'species_origin'

        // --- category predicate (hidden categories for current mode) ---
        const set = hiddenCategories.value?.[effectiveMode]
        const categoryPredicate = makePredicateFromHidden(effectiveMode, set, modeToPropName)

        // Always update the data source predicate (base AND category)
        onPredicateSet?.((f) => basePredicate(f) && categoryPredicate(f))

        // If for some reason we don't have a propName, only apply base map filter
        if (!propName) {
            setTreePointFilter(m, baseExtra, null)
            return
        }

        // --- category map filter (hidden categories -> MapLibre expression) ---
        const hidden = set ? Array.from(set) : []
        if (!hidden.length) {
            setTreePointFilter(m, baseExtra, null)
            return
        }

        hidden.sort()
        const cacheKey = `${effectiveMode}:${hidden.join('|')}`

        let categoryExtra = filterCache.get(cacheKey)
        if (!categoryExtra) {
            categoryExtra = ['any', ['!has', propName], ['!in', propName, ...hidden]]
            filterCache.set(cacheKey, categoryExtra)
        }


        // Apply both: base restriction AND category filter
        setTreePointFilter(m, baseExtra, categoryExtra)
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

    function setTreePointFilter(m, baseExtra, categoryExtra) {
        const base = ['!has', 'point_count'] // legacy filter syntax

        const parts = [base]
        if (baseExtra) parts.push(baseExtra)
        if (categoryExtra) parts.push(categoryExtra)

        const combined = parts.length === 1 ? base : ['all', ...parts]

        for (const id of TREE_POINT_LAYERS) {
            if (m.getLayer(id)) m.setFilter(id, combined)
        }
    }

    return {
        applyVisibility,
        visualiseTreeData,
        onToggleCategory,
    }
}