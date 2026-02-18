
export async function loadNeighborhoodsLayer(mapInstance, { onDataLoaded, onNeighborhoodSelected, isInteractionEnabled } = {}) {
    const res = await fetch('/api/neighborhoods')
    if (!res.ok) throw new Error(`Failed to load neighborhoods: ${res.status}`)

    const interactionsAllowed = () => (isInteractionEnabled ? !!isInteractionEnabled() : true)

    const data = await res.json()
    onDataLoaded?.(data)

    // Ensure every feature has a stable top-level `id` (required for feature-state).
    if (data?.type === 'FeatureCollection' && Array.isArray(data.features)) {
        for (let i = 0; i < data.features.length; i++) {
            const f = data.features[i]
            if (f && f.id == null) {
                // Try common property keys first
                f.id =
                    f?.properties?.id ??
                    f?.properties?.neighborhood_id ??
                    f?.properties?.gid ??
                    `${i}` // last-resort fallback (stable only if feature order is stable)
            }
        }
    }

    const SOURCE_ID = 'neighborhoods'
    const FILL_ID = 'neighborhoods-fill'
    const OUTLINE_ID = 'neighborhoods-outline'

    const HOVER_OPACITY = 0.0
    const BASE_OPACITY = 0.0
    const HOVER_LINE = 2.5
    const BASE_LINE = 0.5

    // Keep hover state on the map instance so repeated calls don't add duplicates.
    if (!mapInstance.__neighborhoodHoverState) {
        mapInstance.__neighborhoodHoverState = { hoveredId: null }
    }

    if (!mapInstance.getSource(SOURCE_ID)) {
        mapInstance.addSource(SOURCE_ID, { type: 'geojson', data })

        mapInstance.addLayer({
            id: FILL_ID,
            type: 'fill',
            source: SOURCE_ID,
            paint: {
                'fill-color': '#1d4ed8',
                'fill-opacity': [
                    'case',
                    ['boolean', ['feature-state', 'hover'], false],
                    HOVER_OPACITY,
                    BASE_OPACITY,
                ],
            },
        })

        mapInstance.addLayer({
            id: OUTLINE_ID,
            type: 'line',
            source: SOURCE_ID,
            paint: {
                'line-color': '#1d4ed8',
                'line-width': [
                    'case',
                    ['boolean', ['feature-state', 'hover'], false],
                    HOVER_LINE,
                    BASE_LINE,
                ],
            },
        })

        // ---------- Hover handlers ----------
        mapInstance.on('mousemove', FILL_ID, (e) => {
            mapInstance.getCanvas().style.cursor = 'pointer'
            if (!e.features?.length) return

            const id = e.features[0].id
            if (id == null) return

            const state = mapInstance.__neighborhoodHoverState
            if (state.hoveredId !== null && state.hoveredId !== id) {
                mapInstance.setFeatureState({ source: SOURCE_ID, id: state.hoveredId }, { hover: false })
            }

            state.hoveredId = id
            mapInstance.setFeatureState({ source: SOURCE_ID, id }, { hover: true })
        })

        mapInstance.on('mouseleave', FILL_ID, () => {
            mapInstance.getCanvas().style.cursor = ''
            const state = mapInstance.__neighborhoodHoverState

            if (state.hoveredId !== null) {
                mapInstance.setFeatureState({ source: SOURCE_ID, id: state.hoveredId }, { hover: false })
            }
            state.hoveredId = null
        })

        // ---------- Click / selection ----------

        let selectedId = null
        if (onNeighborhoodSelected) {
            mapInstance.on('click', (e) => {
                if (!interactionsAllowed()) return
                const features = mapInstance.queryRenderedFeatures(e.point, {
                    layers: [FILL_ID],
                });
                if (features.length === 0 && selectedId) {
                    selectedId = null
                    onNeighborhoodSelected(null)
                }
            })

            mapInstance.on('click', FILL_ID, (e) => {
                if (!interactionsAllowed()) return
                if (clickHitsTree(e)) return

                e.originalEvent?.stopPropagation()
                e.originalEvent?.stopImmediatePropagation()

                if (!e.features?.length) return
                const feature = e.features[0]
                selectedId = feature.id
                onNeighborhoodSelected(feature.id)
            })
        }
    } else {
        mapInstance.getSource(SOURCE_ID).setData(data)
    }

    // helper
    function clickHitsTree(e) {
        const hits = mapInstance.queryRenderedFeatures(e.point, {
            layers: ['trees-circle', 'trees-pin-bg'].filter(id => mapInstance.getLayer(id)),
        })
        return hits.length > 0
    }

    function clearSelection() {
        onNeighborhoodSelected(null)
    }

    return {
        clearSelection
    }
}

export async function loadNeighborhoodStats(id) {
    const res = await fetch(`/api/neighborhoods/${id}/stats`);
    if (!res.ok) throw new Error(`Failed to load neighborhood ${id} stats: ${res.status}`)

    const data = await res.json()
    return data
}
