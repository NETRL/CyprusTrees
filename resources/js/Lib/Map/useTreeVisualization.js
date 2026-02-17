import { useMapColors } from "@/Composables/useMapColors";
import { ref, watch } from "vue";
import { hasLayer, whenLayerReady } from "@/Lib/Map/core/useLayerReady";

export function useTreeVisualization(mapRef, { onHiddenCategories, onPredicateSet, selectedFilterRef } = {}) {

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

    function visualiseTreeData(mode = 'status') {
        const m = map.value
        if (!hasLayer(m, 'trees-circle')) return

        if (mode === lastPaintMode) return
        lastPaintMode = mode

        const expr = paintByMode[mode]?.()

        m.setPaintProperty('trees-circle', 'circle-color', expr ?? DEFAULT_COLOR)
    }


    function applyVisibility(mode = 'status') {
        const m = map.value;
        if (!hasLayer(m, 'trees-circle')) return

        const propName = modeToPropName[mode];

        // If mode has no prop mapping, show all points
        setTreePointFilter(m, null);
        if (!propName) {
            return;
        }

        const set = hiddenCategories.value[mode];
        const predicate = makePredicateFromHidden(mode, set, modeToPropName);
        onPredicateSet?.(predicate)
        const hidden = set ? Array.from(set) : [];

        // When nothing hidden, show all points
        if (!hidden.length) {
            setTreePointFilter(m, null);
            return;
        }

        hidden.sort();
        const cacheKey = `${mode}:${hidden.join('|')}`;

        let extra = filterCache.get(cacheKey);
        if (!extra) {
            // Keep features that do NOT have the property (don’t accidentally hide “unknowns”), OR
            // have the property and it is NOT in the hidden list
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

    function setTreePointFilter(m, extraFilter) {
        // only non-cluster points
        const base = ['!', ['has', 'point_count']];

        // combine base + optional extra
        const combined = extraFilter ? ['all', base, extraFilter] : base;

        for (const id of TREE_POINT_LAYERS) {
            if (m.getLayer(id)) m.setFilter(id, combined);
        }
    }

    return {
        applyVisibility,
        visualiseTreeData,
        onToggleCategory,
    }
}