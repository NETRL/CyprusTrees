
export async function fetchTreeDetails(treeId, { onDataLoaded } = {}) {
  const res = await fetch(`/api/trees/${treeId}`)

  if (!res.ok) {
    throw new Error(`Failed to load tree ${treeId}: ${res.status}`)
  }

  const data = await res.json()
  onDataLoaded?.(data)
  return data
}


// async function ensureTreeIcon(map) {
//   if (map.hasImage('pin-bg')) return;

//   // Load Background (Static Black Pin)
//   const bgRes = await fetch(`/icons/pin-bg.svg?v=${Date.now()}`);
//   const bgImg = await createImageBitmap(await bgRes.blob());
//   map.addImage('pin-bg', bgImg);

//   // Load Foliage (The mask for coloring)
//   const foliageRes = await fetch(`/icons/tree-foliage.svg?v=${Date.now()}`);
//   const foliageImg = await createImageBitmap(await foliageRes.blob());
//   map.addImage('tree-foliage', foliageImg, { sdf: true });
// }

async function ensureTreeIcon(map) {
  if (map.hasImage('tree-marker')) return;

  const res = await fetch(`/icons/tree-marker.svg?v=${Date.now()}`)
  if (!res.ok) throw new Error('Failed to load tree-marker.svg')

  const svgText = await res.text()
  const svg = new Blob([svgText], { type: 'image/svg+xml' })
  const url = URL.createObjectURL(svg)

  await new Promise((resolve, reject) => {
    const img = new Image()
    img.onload = () => {
      // IMPORTANT: sdf:true enables icon-color tinting
      map.addImage('tree-marker', img, { sdf: true })
      URL.revokeObjectURL(url)
      resolve()
    }
    img.onerror = reject
    img.src = url
  })
}

async function ensurePinIcon(map) {
  if (map.hasImage('pin-bg')) return;

  const res = await fetch(`/icons/pin-bg.svg?v=${Date.now()}`)
  if (!res.ok) throw new Error('Failed to load pin-bg.svg')

  const svgText = await res.text()
  const svg = new Blob([svgText], { type: 'image/svg+xml' })
  const url = URL.createObjectURL(svg)

  await new Promise((resolve, reject) => {
    const img = new Image()
    img.onload = () => {
      // IMPORTANT: sdf:true enables icon-color tinting
      map.addImage('pin-bg', img, { sdf: true })
      URL.revokeObjectURL(url)
      resolve()
    }
    img.onerror = reject
    img.src = url
  })
}

async function ensureFoliageIcon(map) {
  if (map.hasImage('tree-foliage')) return;

  const res = await fetch(`/icons/tree-foliage.svg?v=${Date.now()}`)
  if (!res.ok) throw new Error('Failed to load tree-foliage.svg')

  const svgText = await res.text()
  const svg = new Blob([svgText], { type: 'image/svg+xml' })
  const url = URL.createObjectURL(svg)

  await new Promise((resolve, reject) => {
    const img = new Image()
    img.onload = () => {
      // IMPORTANT: sdf:true enables icon-color tinting
      map.addImage('tree-foliage', img, { sdf: true })
      URL.revokeObjectURL(url)
      resolve()
    }
    img.onerror = reject
    img.src = url
  })
}


export async function loadTreesLayer(mapInstance, { onDataLoaded, onTreeSelected, onTreeHovered, setInitialFilter, isInteractionEnabled }) {
  const res = await fetch('/api/trees')
  if (!res.ok) {
    throw new Error(`Failed to load trees: ${res.status}`)
  }
  const data = await res.json()
  let currentData = data

  await ensureTreeIcon(mapInstance)
  await ensurePinIcon(mapInstance)
  await ensureFoliageIcon(mapInstance)

  const interactionsAllowed = () => (isInteractionEnabled ? !!isInteractionEnabled() : true)

  onDataLoaded?.(data)

  function setTreesData(newData) {
    currentData = newData
    mapInstance.getSource('trees')?.setData(newData)
  }

  if (mapInstance.getSource('trees')) {
    setTreesData(data)
  } else {
    mapInstance.addSource('trees', {
      type: 'geojson',
      data,
      promoteId: 'id'
    })

    const DOT_ZOOM_8 = 1.7
    const DOT_ZOOM_14 = 1.7
    const DOT_ZOOM_20 = 6

    if (!window.location.pathname.startsWith('/map2')) {

      mapInstance.addLayer({
        id: 'trees-circle',
        type: 'circle',
        source: 'trees',
        paint: {
          'circle-radius': [
            'interpolate', ['linear'], ['zoom'],
            8, DOT_ZOOM_8,   // zoom starts from 0 (full map) to 22(full zoomed)
            14, DOT_ZOOM_14,
            20, DOT_ZOOM_20,
          ],
          'circle-color': '#16a34a',
          'circle-stroke-width': 10,
          'circle-stroke-color': 'rgba(0,0,0,0)',
          'circle-opacity': 1

        },
      })
    }


    // mapInstance.addLayer({
    //   id: 'trees-circle',          // ID stays the same
    //   type: 'symbol',              // type changes
    //   source: 'trees',
    //   layout: {
    //     'icon-image': 'tree-marker',
    //     'icon-allow-overlap': true,
    //     'icon-ignore-placement': true,
    //     'icon-anchor': 'bottom',
    //     'icon-size': [
    //       'interpolate', ['linear'], ['zoom'],
    //       8, 0.35,
    //       14, 0.5,
    //       20, 1.0,
    //     ],
    //   },
    //   paint: {
    //     'icon-color': '#16a34a',
    //     'icon-opacity': 1,
    //   },
    // })





    // Hover ring (no pulse, just a halo while hovering)
    mapInstance.addLayer({
      id: 'trees-circle-hover',
      type: 'circle',
      source: 'trees',
      paint: {
        'circle-radius': 10,
        'circle-color': 'rgba(34, 197, 94, 0.25)', // transparent fill
        'circle-stroke-width': 2,
        'circle-stroke-color': '#22c55e',
      },
      // initially match nothing
      filter: ['==', ['get', 'id'], -1],
    });

    // Selected ring (this one will pulse)
    mapInstance.addLayer({
      id: 'trees-circle-selected',
      type: 'circle',
      source: 'trees',
      paint: {
        'circle-radius': 10,
        'circle-color': 'rgba(34, 197, 94, 0.25)',
        'circle-stroke-width': 2,
        'circle-stroke-color': '#22c55e',
      },
      filter: ['==', ['get', 'id'], -1],
    });

    if (window.location.pathname.startsWith('/map2')) {
      const ICON_SIZE_CONFIG = [
        'interpolate', ['linear'], ['zoom'],
        8, 0.6,   // Very small at low zoom
        14, 0.4,   // Medium size at city level
        20, 0.7    // Larger when zoomed in close
      ];


      const ICON_OFFSET = [0, 8];

      // Layer 1: The Black Pin
      mapInstance.addLayer({
        id: 'trees-pin-bg',
        type: 'symbol',
        source: 'trees',
        layout: {
          'icon-image': 'pin-bg',
          'icon-anchor': 'bottom',
          'icon-allow-overlap': true,
          'icon-offset': ICON_OFFSET,
          'icon-size': ICON_SIZE_CONFIG,
        },
        paint: {
          'icon-color': '#101828'
        }
      });

      // Layer 2: The Colored Foliage
      mapInstance.addLayer({
        id: 'trees-circle',
        type: 'symbol',
        source: 'trees',
        layout: {
          'icon-image': 'tree-foliage',
          'icon-anchor': 'bottom',
          'icon-allow-overlap': true,
          'icon-offset': ICON_OFFSET,
          'icon-size': ICON_SIZE_CONFIG,
        },
        paint: {
          'icon-color': '#16a34a'
        }
      });

    }

    let hoveredId = null;
    let selectedId = null;

    let pulseAnimationId = null;
    const baseSelectedRadius = 10;

    mapInstance.on('click', 'trees-circle', (e) => {
      if (!interactionsAllowed()) return

      e.originalEvent?.stopPropagation()
      e.originalEvent?.stopImmediatePropagation()
      const feature = e.features?.[0];
      handleTreeClick(feature)
    });


    mapInstance.on('mouseenter', 'trees-circle', () => {
      if (!interactionsAllowed()) return
      mapInstance.getCanvas().style.cursor = 'crosshair'
    })

    mapInstance.on('mousemove', 'trees-circle', (e) => {
      if (!interactionsAllowed()) return
      if (selectedId) return;
      const feature = e.features?.[0];
      if (!feature) return;

      const p = feature.properties;
      const id = p?.id ?? feature.id;
      if (!id) return;

      let species = null;
      let neighborhood = null;
      try {
        species = p.species ? JSON.parse(p.species) : null;
        neighborhood = p.neighborhood ? JSON.parse(p.neighborhood) : null;
      } catch (err) {
        console.warn('Failed to parse nested props', err);
      }

      const propsWithParsed = { ...p, species, neighborhood };

      if (id === selectedId) {
        hoveredId = null;
        mapInstance.setFilter('trees-circle-hover', ['==', ['get', 'id'], -1]);
      } else {
        hoveredId = id;
        mapInstance.setFilter('trees-circle-hover', ['==', ['get', 'id'], hoveredId]);
        onTreeHovered?.(propsWithParsed);
      }

      mapInstance.getCanvas().style.cursor = 'crosshair';
    });

    mapInstance.on('mouseleave', 'trees-circle', (e) => {
      if (!interactionsAllowed()) return
      // hoveredId = null;
      // mapInstance.setFilter('trees-circle-hover', ['==', ['get', 'id'], -1]);
      mapInstance.getCanvas().style.cursor = '';
      // handler(e)
    });


    mapInstance.on('click', (e) => {
      if (!interactionsAllowed()) return
      // Skip clicks that hit a tree feature – those are handled above
      const features = mapInstance.queryRenderedFeatures(e.point, {
        layers: ['trees-circle'],
      });

      if (features.length === 0) {
        clearSelection(mapInstance);
      }
    });
    mapInstance.on('dragstart', () => {
      if (!interactionsAllowed()) return
      if (selectedId) return
      clearSelection(mapInstance)
    });

    function startPulse() {
      if (pulseAnimationId != null) return; // already running

      const start = performance.now();

      const frame = (time) => {
        const t = (time - start) / 300; // speed factor
        const pulse = baseSelectedRadius + Math.sin(t) * 2; // between 4 and 8

        if (mapInstance.getLayer('trees-circle-selected')) {
          mapInstance.setPaintProperty('trees-circle-selected', 'circle-radius', pulse);
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
      if (mapInstance.getLayer('trees-circle-selected')) {
        mapInstance.setPaintProperty('trees-circle-selected', 'circle-radius', baseSelectedRadius);
      }
    }

    function clearSelection() {
      selectedId = null;
      hoveredId = null;
      stopPulse();
      mapInstance.setFilter('trees-circle-selected', ['==', ['get', 'id'], -1]);
      mapInstance.setFilter('trees-circle-hover', ['==', ['get', 'id'], -1]);
      onTreeSelected?.(null);
      onTreeHovered?.(null);
    }

    function handleTreeClick(feature) {
      if (!feature) return;

      const p = feature.properties;
      const id = p?.id ?? feature.id;
      if (!id) return;

      // --- selection logic ---
      selectedId = id;
      // selected ring: show only this id
      mapInstance.setFilter('trees-circle-selected', ['==', ['get', 'id'], id]);
      // hover ring: hide when selected
      hoveredId = null;
      mapInstance.setFilter('trees-circle-hover', ['==', ['get', 'id'], -1]);
      // start pulsating
      startPulse();

      onTreeSelected?.(p);
    }


    function selectTreeById(treeId) {
      if (!treeId) return;

      // treeId might be a string; normalize to number
      const numericId = Number(treeId);

      const feature = currentData.features?.find(f => Number(f.properties?.id ?? f.id) === numericId)

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

      // this will set filters, start pulse, and pass parsed species/neighborhood to Vue
      handleTreeClick(feature);
    }

    setInitialFilter?.('status')



    return {
      selectTreeById,
      clearSelection,
      setTreesData,
    };

  }
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
