
export async function fetchTreeDetails(treeId, { onDataLoaded } = {}) {
  const res = await fetch(`/api/trees/${treeId}`)

  if (!res.ok) {
    throw new Error(`Failed to load tree ${treeId}: ${res.status}`)
  }

  const data = await res.json()
  onDataLoaded?.(data)
  return data
}

async function loadSvgAsSdfImage(map, name, url, rasterPx = 256) {
  if (map.hasImage(name)) map.removeImage(name);

  const res = await fetch(`${url}?v=${Date.now()}`);
  if (!res.ok) throw new Error(`Failed to load ${url}: ${res.status}`);

  const svgText = await res.text();
  const blob = new Blob([svgText], { type: 'image/svg+xml' });
  const blobUrl = URL.createObjectURL(blob);

  const img = await new Promise((resolve, reject) => {
    const im = new Image();
    im.onload = () => resolve(im);
    im.onerror = reject;
    im.src = blobUrl;
  });
  URL.revokeObjectURL(blobUrl);

  const canvas = document.createElement('canvas');
  canvas.width = rasterPx;
  canvas.height = rasterPx;

  const ctx = canvas.getContext('2d', { alpha: true });
  ctx.clearRect(0, 0, rasterPx, rasterPx);
  ctx.drawImage(img, 0, 0, rasterPx, rasterPx);

  const imageData = ctx.getImageData(0, 0, rasterPx, rasterPx);

  map.addImage(
    name,
    { width: rasterPx, height: rasterPx, data: imageData.data },
    { sdf: true }
  );
}



function preprocessTreesGeojson(data) {
  if (!data || !Array.isArray(data.features)) return data;

  for (const f of data.features) {
    const p = (f.properties ||= {});

    // ensure promoteId works with properties.id
    if (p.id == null && f.id != null) p.id = f.id;

    // parse nested JSON once (avoid JSON.parse in mousemove)
    if (typeof p.species === 'string') {
      try { p.species = JSON.parse(p.species); } catch { }
    }
    if (typeof p.neighborhood === 'string') {
      try { p.neighborhood = JSON.parse(p.neighborhood); } catch { }
    }
  }
  return data;
}

// Store the raster size as a constant so offsets can reference it
const PIN_RASTER_SIZE = 56;
const PIN_COLOR = '#101828'
const PIN_HOVER_COLOR = '#009966'
const PIN_SELECT_COLOR = '#009966'

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
  const data = preprocessTreesGeojson(dataRaw);
  let currentData = data;

  await loadSvgAsSdfImage(mapInstance, 'pin-bg', '/icons/pin-bg.svg', PIN_RASTER_SIZE);
  await loadSvgAsSdfImage(mapInstance, 'tree-foliage', '/icons/tree-foliage.svg', PIN_RASTER_SIZE);

  const interactionsAllowed = () => (isInteractionEnabled ? !!isInteractionEnabled() : true);

  onDataLoaded?.(data);

  function setTreesData(newData) {
    currentData = preprocessTreesGeojson(newData);
    const src = mapInstance.getSource('trees');
    if (src) src.setData(currentData);
    clearSelection(); // feature-state ids may become invalid after refresh
  }

  // If source exists, just update and return controls
  if (mapInstance.getSource('trees')) {
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

    mapInstance.on('zoom', () => {
      console.log('Zoom:', mapInstance.getZoom().toFixed(2));
    });

  // Icon size configuration
  const ICON_SIZE_CONFIG = [
    'interpolate', ['linear'], ['zoom'],
    8, 0.8,
    12, 0.8,
    14, 0.8,
    18, 0.8,
    20, 0.8,
  ];

  // Calculate pixel offset based on icon size
  const createPixelOffset = (baseOffset) => [
    'interpolate', ['linear'], ['zoom'],
    10, ['literal', [0, baseOffset * 0.9]],
    14, ['literal', [0, baseOffset * 0.9]],
    16, ['literal', [0, baseOffset * 0.9]],
    20, ['literal', [0, baseOffset * 0.9]]
  ];

  // Calculate text offset in em units
  const createTextOffset = (baseOffsetEm) => [
    'interpolate', ['linear'], ['zoom'],
    10, ['literal', [0, baseOffsetEm * 1.4]],
    14, ['literal', [0, baseOffsetEm * 1.4]],
    18, ['literal', [0, baseOffsetEm * 1.4]],
    20, ['literal', [0, baseOffsetEm * 1.4]]
  ];

  // PIN layer (for both clustered and non-clustered)
  mapInstance.addLayer({
    id: 'trees-pin-bg',
    type: 'symbol',
    source: 'trees',
    layout: {
      'icon-image': 'pin-bg',
      'icon-anchor': 'bottom',
      'icon-size': ICON_SIZE_CONFIG,
      'icon-allow-overlap': true,
      'icon-ignore-placement': true,
    },
    paint: {
      'icon-color': [
        'case',
        ['boolean', ['feature-state', 'selected'], false], PIN_SELECT_COLOR, // selected: green
        ['boolean', ['feature-state', 'hover'], false], PIN_HOVER_COLOR,    // hover: blue
        PIN_COLOR  // default: dark
      ]
    }
  });

  // CIRCLE for individual trees (not clusters)
  mapInstance.addLayer({
    id: 'trees-circle',
    type: 'circle',
    source: 'trees',
    filter: ['!', ['has', 'point_count']],
    paint: {
      'circle-radius': [
        'interpolate', ['linear'], ['zoom'],
        10, 5,
        14, 5,
        18, 5,
        20, 5
      ],
      'circle-stroke-width': 0,
      'circle-color': PIN_COLOR,
      'circle-translate': createPixelOffset(-32),
      'circle-translate-anchor': 'map',
    }
  });

  // CLUSTER COUNT for clustered trees
  mapInstance.addLayer({
    id: 'trees-cluster-count',
    type: 'symbol',
    source: 'trees',
    filter: ['has', 'point_count'],
    layout: {
      'text-field': ['get', 'point_count_abbreviated'],
      'text-size': [
        'interpolate', ['linear'], ['zoom'],
        10, 12,
        14, 12,
        18, 12,
        20, 12
      ],
      'text-anchor': 'center',
      'text-offset': createTextOffset(-1.7),
      'text-allow-overlap': true,
      'text-ignore-placement': true,
    },
    paint: {
      'text-color': '#fff',
      'text-halo-color': '#fff',
      'text-halo-width': 0.4
    }
  });

  let hoveredId = null;
  let selectedId = null;
  let pulseAnimationId = null;

  // --- INTERACTION HANDLERS ---

  mapInstance.on('click', 'trees-pin-bg', (e) => {
    if (!interactionsAllowed()) return;
    e.originalEvent?.stopPropagation();
    e.originalEvent?.stopImmediatePropagation();

    const feature = e.features?.[0];

    // Don't handle clicks on clusters
    if (feature?.properties?.cluster) {
      return;
    }

    handleTreeClick(feature);
  });

  mapInstance.on('mouseenter', 'trees-pin-bg', (e) => {
    if (!interactionsAllowed()) return;

    const feature = e.features?.[0];
    // Don't show pointer for clusters
    if (feature?.properties?.cluster) {
      return;
    }

    mapInstance.getCanvas().style.cursor = 'pointer';
  });

  mapInstance.on('mousemove', 'trees-pin-bg', (e) => {
    if (!interactionsAllowed()) return;

    const feature = e.features?.[0];
    if (!feature) return;

    // Don't hover clusters
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

    // Don't hover if this tree is selected
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

    // If hovering a different tree, clear previous hover
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

    // Parse nested properties for callback
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

  // --- ANIMATION FUNCTIONS ---

  function startPulse() {
    if (pulseAnimationId != null) return;
    if (selectedId === null) return;

    const base = hexToRgb(PIN_SELECT_COLOR);
    const bright = brighten(base, 30); // intensity control here
    const start = performance.now();

    const frame = (time) => {
      if (selectedId === null) {
        stopPulse();
        return;
      }

      const t = (time - start) / 2000;
      const eased = Math.sin(t * Math.PI * 2) * 0.5 + 0.5;

      const color = `rgb(
      ${lerp(base.r, bright.r, eased)},
      ${lerp(base.g, bright.g, eased)},
      ${lerp(base.b, bright.b, eased)}
    )`;

      if (mapInstance.getLayer('trees-pin-bg')) {
        mapInstance.setPaintProperty('trees-pin-bg', 'icon-color', [
          'case',
          ['boolean', ['feature-state', 'selected'], false], color,
          ['boolean', ['feature-state', 'hover'], false], PIN_HOVER_COLOR,
          PIN_COLOR
        ]);
      }

      pulseAnimationId = requestAnimationFrame(frame);
    };

    pulseAnimationId = requestAnimationFrame(frame);
  }


  function hexToRgb(hex) {
    const h = hex.replace('#', '');
    const bigint = parseInt(h.length === 3
      ? h.split('').map(c => c + c).join('')
      : h, 16);

    return {
      r: (bigint >> 16) & 255,
      g: (bigint >> 8) & 255,
      b: bigint & 255
    };
  }

  function lerp(a, b, t) {
    return Math.round(a + (b - a) * t);
  }

  function brighten({ r, g, b }, amount = 30) {
    return {
      r: Math.min(255, r + amount),
      g: Math.min(255, g + amount),
      b: Math.min(255, b + amount),
    };
  }

  function stopPulse() {
    if (pulseAnimationId != null) {
      cancelAnimationFrame(pulseAnimationId);
      pulseAnimationId = null;
    }

    // Reset to static selected color
    if (mapInstance.getLayer('trees-pin-bg')) {
      mapInstance.setPaintProperty('trees-pin-bg', 'icon-color', [
        'case',
        ['boolean', ['feature-state', 'selected'], false], PIN_SELECT_COLOR,
        ['boolean', ['feature-state', 'hover'], false], PIN_HOVER_COLOR,
        PIN_COLOR
      ]);
    }
  }

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
  };
}


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
}

export async function loadNeighborhoodStats(id) {
  const res = await fetch(`/api/neighborhoods/${id}/stats`);
  if (!res.ok) throw new Error(`Failed to load neighborhood ${id} stats: ${res.status}`)

  const data = await res.json()
  console.log(data)
  return data
}
