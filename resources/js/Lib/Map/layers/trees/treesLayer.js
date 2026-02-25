import { hasLayer, getSourceSafe, hasSource } from "@/Lib/Map/core/useMapStyleUtils"

export async function fetchTreeDetails(treeId, { onDataLoaded } = {}) {
    const res = await fetch(`/api/trees/${treeId}`)

    if (!res.ok) {
        throw new Error(`Failed to load tree ${treeId}: ${res.status}`)
    }

    const data = await res.json()
    onDataLoaded?.(data)
    return data
}

// async function loadSvgAsSdfImage(map, name, url, rasterPx = 256) {
//     if (map.hasImage(name)) map.removeImage(name);

//     const res = await fetch(`${url}?v=${Date.now()}`);
//     if (!res.ok) throw new Error(`Failed to load ${url}: ${res.status}`);

//     const svgText = await res.text();
//     const blob = new Blob([svgText], { type: 'image/svg+xml' });
//     const blobUrl = URL.createObjectURL(blob);

//     const img = await new Promise((resolve, reject) => {
//         const im = new Image();
//         im.onload = () => resolve(im);
//         im.onerror = reject;
//         im.src = blobUrl;
//     });
//     URL.revokeObjectURL(blobUrl);

//     const canvas = document.createElement('canvas');
//     canvas.width = rasterPx;
//     canvas.height = rasterPx;

//     const ctx = canvas.getContext('2d', { alpha: true });
//     ctx.clearRect(0, 0, rasterPx, rasterPx);
//     ctx.drawImage(img, 0, 0, rasterPx, rasterPx);

//     const imageData = ctx.getImageData(0, 0, rasterPx, rasterPx);

//     map.addImage(
//         name,
//         { width: rasterPx, height: rasterPx, data: imageData.data },
//         { sdf: true }
//     );
// }


function preprocessTreesGeojson(data) {
    if (!data || !Array.isArray(data.features)) return data;

    for (const f of data.features) {
        const p = (f.properties ||= {});

        // ensure promoteId works with properties.id
        if (p.id == null && f.id != null) p.id = f.id;

        // parse nested JSON once
        if (typeof p.species === 'string') {
            try { p.species = JSON.parse(p.species); } catch { }
        }
        if (typeof p.neighborhood === 'string') {
            try { p.neighborhood = JSON.parse(p.neighborhood); } catch { }
        }
    }
    return data;
}


export async function loadTreesLayer(mapInstance, {
    onDataLoaded,
    onTreeSelected,
    onTreeHovered,
    setInitialFilter,
    isInteractionEnabled
}) {
    const res = await fetch('/api/trees');
    if (!res.ok) {
        throw new Error(`Failed to load trees: ${res.status}`);
    }

    const dataRaw = await res.json();
    let data = preprocessTreesGeojson(dataRaw);
    let currentData = data;

    const interactionsAllowed = () => (isInteractionEnabled ? !!isInteractionEnabled() : true);

    onDataLoaded?.(data);

    function setTreesData(newData) {
        data = preprocessTreesGeojson(newData);
        currentData = data
        const src = getSourceSafe(mapInstance, 'trees')
        if (src) src.setData(currentData);
        clearSelection(); // feature-state ids may become invalid after refresh
    }

    function setTreesDataFiltered(predicateFn) {
        console.log(() => predicateFn)
        const filtered = {
            ...data,
            features: (data.features || []).filter(predicateFn),
        };

        currentData = filtered;

        const src = getSourceSafe(mapInstance, 'trees')
        if (src) src.setData(currentData);

        clearSelection();
    }

    // If source exists, just update and return controls
    if (hasSource(mapInstance, 'trees')) {
        setTreesData(data);
        setInitialFilter?.('status');
        return { selectTreeById, clearSelection, setTreesData };
    }

    // --- source ---
    mapInstance.addSource('trees', {
        type: 'geojson',
        data,
        promoteId: 'id',
        cluster: true,
        clusterRadius: 40,
        clusterMaxZoom: 15
    });

    // mapInstance.on('zoom', () => {
    //   console.log('Zoom:', mapInstance.getZoom().toFixed(2));
    // });

    mapInstance.addLayer({
        id: 'trees-cluster-shadow',
        type: 'circle',
        source: 'trees',
        filter: ['has', 'point_count'],
        paint: {
            'circle-color': '#000000',
            'circle-opacity': 0.15,
            'circle-blur': 0.8,
            'circle-radius': [
                'step',
                ['get', 'point_count'],
                22,
                10, 28,
                50, 36
            ],
            'circle-translate': [2, 2]
        }
    });

    // 2. CLUSTER BACKGROUND
    mapInstance.addLayer({
        id: 'trees-cluster-bg',
        type: 'circle',
        source: 'trees',
        filter: ['has', 'point_count'],
        paint: {
            'circle-color': '#2D3748',
            'circle-opacity': 0.85, //0.85
            'circle-radius': [
                'step',
                ['get', 'point_count'],
                20,
                10, 26,
                50, 34
            ],
            'circle-stroke-width': 2,
            'circle-stroke-color': '#ffffff',
        }
    });

    // 4. INDIVIDUAL TREE - INTERACTION BACKGROUND (invisible, for clicks)
    mapInstance.addLayer({
        id: 'trees-pin-bg',
        type: 'circle',
        source: 'trees',
        filter: ['!', ['has', 'point_count']],
        paint: {
            'circle-color': '#000',
            'circle-opacity': 0,           // Invisible
            'circle-radius': 12,            // Larger hit area
            'circle-stroke-width': 0,
        }
    });

    // 5. INDIVIDUAL TREE - SELECTION PULSE (animated outer ring)
    mapInstance.addLayer({
        id: 'trees-selection-pulse',
        type: 'circle',
        source: 'trees',
        filter: ['!', ['has', 'point_count']],
        paint: {
            'circle-radius': 14,
            'circle-color': 'transparent',
            'circle-stroke-width': [
                'case',
                ['boolean', ['feature-state', 'selected'], false], 2,
                0
            ],
            'circle-stroke-color': '#009966',
            'circle-stroke-opacity': [
                'case',
                ['boolean', ['feature-state', 'selected'], false], 0.6,
                0
            ],
            'circle-translate-anchor': 'map',
        }
    });

    // 6. INDIVIDUAL TREE - SELECTION RING (static)
    mapInstance.addLayer({
        id: 'trees-selection-ring',
        type: 'circle',
        source: 'trees',
        filter: ['!', ['has', 'point_count']],
        paint: {
            'circle-radius': 11,
            'circle-color': 'transparent',
            'circle-stroke-width': [
                'case',
                ['boolean', ['feature-state', 'selected'], false], 2.5,
                0
            ],
            'circle-stroke-color': '#009966',
            'circle-translate-anchor': 'map',
        }
    });

    // 7. INDIVIDUAL TREE - HOVER RING
    mapInstance.addLayer({
        id: 'trees-hover-ring',
        type: 'circle',
        source: 'trees',
        filter: ['!', ['has', 'point_count']],
        paint: {
            'circle-radius': 11,
            'circle-color': 'transparent',
            'circle-stroke-width': [
                'case',
                ['boolean', ['feature-state', 'hover'], false], 2,
                0
            ],
            'circle-stroke-color': '#1F2937',
            'circle-stroke-opacity': [
                'case',
                ['boolean', ['feature-state', 'hover'], false], 0.8,
                0
            ],
            'circle-translate-anchor': 'map',
        }
    });

    // 8. INDIVIDUAL TREE - WHITE HALO
    mapInstance.addLayer({
        id: 'trees-circle-halo',
        type: 'circle',
        source: 'trees',
        filter: ['!', ['has', 'point_count']],
        paint: {
            'circle-radius': 8.5,
            'circle-color': '#ffffff',
            'circle-opacity': 1,
            'circle-stroke-width': 1,
            'circle-stroke-color': '#D1D5DB',
            'circle-translate-anchor': 'map',
        }
    });

    // 9. INDIVIDUAL TREE - MAIN COLORED CIRCLE
    mapInstance.addLayer({
        id: 'trees-circle',
        type: 'circle',
        source: 'trees',
        filter: ['!', ['has', 'point_count']],
        paint: {
            'circle-radius': [
                'interpolate', ['linear'], ['zoom'],
                10, 5,
                14, 7,
                18, 8.5
            ],
            'circle-color': '#20df80',  // initial color
            'circle-stroke-width': 0,
            'circle-stroke-color': '#1F2937',
            'circle-translate-anchor': 'map',
        }
    });


    // 3. CLUSTER COUNT TEXT
    mapInstance.addLayer({
        id: 'trees-cluster-count',
        type: 'symbol',
        source: 'trees',
        filter: ['has', 'point_count'],
        layout: {
            'text-field': ['get', 'point_count_abbreviated'],
            'text-size': [
                'interpolate', ['linear'], ['zoom'],
                10, 14,
                14, 15,
                18, 16,
                20, 16
            ],
            'text-font': ['DIN Pro Bold', 'Arial Unicode MS Bold'],
            'text-anchor': 'center',
            'text-allow-overlap': true,
            'text-ignore-placement': true,
        },
        paint: {
            'text-color': '#ffffff',
            'text-halo-color': 'rgba(0,0,0,0.3)',
            'text-halo-width': 1.5,
            'text-halo-blur': 0.5
        }
    });



    // ============================================================================
    // ANIMATION FUNCTIONS
    // ============================================================================

    let hoveredId = null;
    let selectedId = null;
    let pulseAnimationId = null;

    // --- PULSE ANIMATION FOR SELECTION ---
    function startPulse() {
        if (pulseAnimationId != null) return;
        if (selectedId === null) return;

        const start = performance.now();

        const frame = (time) => {
            if (selectedId === null) {
                stopPulse();
                return;
            }

            const elapsed = time - start;
            const t = elapsed / 1500; // 1.5 second cycle

            // Smooth sine wave for pulsing
            const pulse = Math.sin(t * Math.PI * 2) * 0.5 + 0.5;

            // Pulse the outer ring opacity (0.3 to 0.8)
            const opacity = 0.3 + (pulse * 0.5);

            // Pulse the outer ring size (13 to 15)
            const radius = 13 + (pulse * 2);

            if (hasLayer(mapInstance, 'trees-selection-pulse')) {
                mapInstance.setPaintProperty('trees-selection-pulse', 'circle-radius', [
                    'case',
                    ['boolean', ['feature-state', 'selected'], false],
                    radius,
                    0
                ]);

                mapInstance.setPaintProperty('trees-selection-pulse', 'circle-stroke-opacity', [
                    'case',
                    ['boolean', ['feature-state', 'selected'], false],
                    opacity,
                    0
                ]);
            }

            pulseAnimationId = requestAnimationFrame(frame);
        };

        pulseAnimationId = requestAnimationFrame(frame);
    }

    function stopPulse() {
        if (pulseAnimationId != null) {
            cancelAnimationFrame(pulseAnimationId);
            pulseAnimationId = null;
        }

        // Reset pulse layer to default
        if (hasLayer(mapInstance, 'trees-selection-pulse')) {
            mapInstance.setPaintProperty('trees-selection-pulse', 'circle-radius', [
                'case',
                ['boolean', ['feature-state', 'selected'], false],
                14,
                0
            ]);

            mapInstance.setPaintProperty('trees-selection-pulse', 'circle-stroke-opacity', [
                'case',
                ['boolean', ['feature-state', 'selected'], false],
                0.6,
                0
            ]);
        }
    }

    // --- INTERACTION HANDLERS  ---

    mapInstance.on('click', 'trees-pin-bg', (e) => {
        if (!interactionsAllowed()) return;
        e.originalEvent?.stopPropagation();
        e.originalEvent?.stopImmediatePropagation();

        const feature = e.features?.[0];
        if (feature?.properties?.cluster) return;

        handleTreeClick(feature);
    });

    mapInstance.on('mouseenter', 'trees-pin-bg', (e) => {
        if (!interactionsAllowed()) return;

        const feature = e.features?.[0];
        if (feature?.properties?.cluster) return;

        mapInstance.getCanvas().style.cursor = 'pointer';
    });

    mapInstance.on('mousemove', 'trees-pin-bg', (e) => {
        if (!interactionsAllowed()) return;

        const feature = e.features?.[0];
        if (!feature) return;

        if (feature.properties?.cluster) {
            if (hoveredId !== null) {
                mapInstance.setFeatureState(
                    { source: 'trees', id: hoveredId },
                    { hover: false }
                );
                hoveredId = null;
                onTreeHovered?.(null);
            }
            return;
        }

        const p = feature.properties;
        const id = p?.id ?? feature.id;
        if (!id) return;

        // Don't show hover if this tree is selected
        if (id === selectedId) {
            if (hoveredId !== null) {
                mapInstance.setFeatureState(
                    { source: 'trees', id: hoveredId },
                    { hover: false }
                );
                hoveredId = null;
            }
            return;
        }

        // Clear previous hover
        if (hoveredId !== null && hoveredId !== id) {
            mapInstance.setFeatureState(
                { source: 'trees', id: hoveredId },
                { hover: false }
            );
        }

        // Set new hover
        hoveredId = id;
        mapInstance.setFeatureState(
            { source: 'trees', id: hoveredId },
            { hover: true }
        );

        // Parse and callback
        let species = null;
        let neighborhood = null;
        try {
            species = p.species ? JSON.parse(p.species) : null;
            neighborhood = p.neighborhood ? JSON.parse(p.neighborhood) : null;
        } catch (err) {
            console.warn('Failed to parse nested props', err);
        }

        const propsWithParsed = { ...p, species, neighborhood };
        onTreeHovered?.(propsWithParsed);
    });

    mapInstance.on('mouseleave', 'trees-pin-bg', () => {
        if (!interactionsAllowed()) return;
        mapInstance.getCanvas().style.cursor = '';

        if (hoveredId !== null) {
            mapInstance.setFeatureState(
                { source: 'trees', id: hoveredId },
                { hover: false }
            );
            hoveredId = null;
            onTreeHovered?.(null);
        }
    });

    mapInstance.on('click', (e) => {
        if (!interactionsAllowed()) return;
        const features = mapInstance.queryRenderedFeatures(e.point, {
            layers: ['trees-pin-bg'],
        });

        if (features.length === 0 || features[0]?.properties?.cluster) {
            clearSelection();
        }
    });

    mapInstance.on('dragstart', () => {
        if (!interactionsAllowed()) return;
        if (selectedId) return;
        clearSelection();
    });

    // --- CLUSTER CLICK TO ZOOM ---
    mapInstance.on('click', 'trees-cluster-bg', (e) => {
        if (!interactionsAllowed()) return;

        const features = mapInstance.queryRenderedFeatures(e.point, {
            layers: ['trees-cluster-bg']
        });

        if (!features.length) return;

        const clusterId = features[0].properties.cluster_id;
        const source = getSourceSafe(mapInstance, 'trees') 

        source.getClusterExpansionZoom(clusterId, (err, zoom) => {
            if (err) return;

            mapInstance.easeTo({
                center: features[0].geometry.coordinates,
                zoom: zoom + 0.5,
                duration: 500
            });
        });
    });

    // Show pointer on cluster hover
    mapInstance.on('mouseenter', 'trees-cluster-bg', () => {
        mapInstance.getCanvas().style.cursor = 'pointer';
    });

    mapInstance.on('mouseleave', 'trees-cluster-bg', () => {
        mapInstance.getCanvas().style.cursor = '';
    });

    // --- STATE MANAGEMENT ---

    function clearSelection() {
        if (selectedId !== null) {
            mapInstance.setFeatureState(
                { source: 'trees', id: selectedId },
                { selected: false }
            );
            selectedId = null;
        }

        if (hoveredId !== null) {
            mapInstance.setFeatureState(
                { source: 'trees', id: hoveredId },
                { hover: false }
            );
            hoveredId = null;
        }

        stopPulse();
        onTreeSelected?.(null);
        onTreeHovered?.(null);
    }

    function handleTreeClick(feature) {
        if (!feature) return;

        const p = feature.properties;
        const id = p?.id ?? feature.id;
        if (!id) return;

        // Clear previous selection
        if (selectedId !== null && selectedId !== id) {
            mapInstance.setFeatureState(
                { source: 'trees', id: selectedId },
                { selected: false }
            );
        }

        // Clear hover state
        if (hoveredId !== null) {
            mapInstance.setFeatureState(
                { source: 'trees', id: hoveredId },
                { hover: false }
            );
            hoveredId = null;
        }

        // Set new selection
        selectedId = id;
        mapInstance.setFeatureState(
            { source: 'trees', id: selectedId },
            { selected: true }
        );

        startPulse();
        onTreeSelected?.(p);
    }

    function selectTreeById(treeId) {
        if (!treeId) return;

        const numericId = Number(treeId);
        const feature = currentData.features?.find(f =>
            Number(f.properties?.id ?? f.id) === numericId
        );

        if (!feature) {
            console.warn('No tree feature found for id', treeId);
            return;
        }

        if (feature.geometry?.type === 'Point') {
            const [lng, lat] = feature.geometry.coordinates;
            mapInstance.easeTo({
                center: [lng, lat],
                duration: 800,
                zoom: Math.max(mapInstance.getZoom(), 16),
            });
        }

        handleTreeClick(feature);
    }


    setInitialFilter?.('status');

    return {
        selectTreeById,
        clearSelection,
        setTreesData,
        setTreesDataFiltered,
    };
}