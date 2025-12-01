import maplibregl from 'maplibre-gl'

export async function loadTreesLayer(mapInstance, { onDataLoaded, onTreeSelected, setInitialFilter }) {
  const res = await fetch('/api/trees')
  if (!res.ok) {
    throw new Error(`Failed to load trees: ${res.status}`)
  }
  const data = await res.json()

  onDataLoaded?.(data)

  if (mapInstance.getSource('trees')) {
    mapInstance.getSource('trees').setData(data)
  } else {
    mapInstance.addSource('trees', {
      type: 'geojson',
      data,
      promoteId: 'id'
    })


    mapInstance.addLayer({
      id: 'trees-circle',
      type: 'circle',
      source: 'trees',
      paint: {
        'circle-radius': [
          'interpolate', ['linear'], ['zoom'],
          10, 2,   // at zoom 10
          14, 2,   // at zoom 14
          17, 4    // at zoom 17
        ],
        'circle-color': '#16a34a',
        'circle-stroke-width': 10,
        'circle-stroke-color': 'rgba(0,0,0,0)',
      },
    })



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

    let hoveredId = null;
    let selectedId = null;

    let pulseAnimationId = null;
    const baseSelectedRadius = 10;

    function startPulse(map) {
      if (pulseAnimationId != null) return; // already running

      const start = performance.now();

      const frame = (time) => {
        const t = (time - start) / 300; // speed factor
        const pulse = baseSelectedRadius + Math.sin(t) * 2; // between 4 and 8

        map.setPaintProperty('trees-circle-selected', 'circle-radius', pulse);
        pulseAnimationId = requestAnimationFrame(frame);
      };

      pulseAnimationId = requestAnimationFrame(frame);
    }

    function stopPulse(map) {
      if (pulseAnimationId != null) {
        cancelAnimationFrame(pulseAnimationId);
        pulseAnimationId = null;
      }
      map.setPaintProperty('trees-circle-selected', 'circle-radius', baseSelectedRadius);
    }

    function clearSelection(map) {
      selectedId = null;
      stopPulse(map);
      map.setFilter('trees-circle-selected', ['==', ['get', 'id'], -1]);
    }

    mapInstance.on('click', 'trees-circle', (e) => {
      const feature = e.features?.[0];
      if (!feature) return;

      const p = feature.properties;
      const id = p?.id ?? feature.id;
      if (!id) return;

      // --- selection logic ---
      selectedId = id;
      // selected ring: show only this id
      mapInstance.setFilter('trees-circle-selected', ['==', ['get', 'id'], selectedId]);
      // hover ring: hide when selected
      hoveredId = null;
      mapInstance.setFilter('trees-circle-hover', ['==', ['get', 'id'], -1]);
      // start pulsating
      startPulse(mapInstance);

      // --- your existing code ---
      onTreeSelected?.(p);

      let species = null;
      let neighborhood = null;
      try {
        species = p.species ? JSON.parse(p.species) : null;
        neighborhood = p.neighborhood ? JSON.parse(p.neighborhood) : null;
      } catch (err) {
        console.warn('Failed to parse nested props', err);
      }

      const html = `
    <div>
      <strong>Tree #${p.id}</strong><br/>
      ${species ? `<div>${species.common_name ?? ''}</div>` : ''}
      ${neighborhood ? `<div>${neighborhood.name ?? ''}</div>` : ''}
      ${p.address ? `<div>${p.address}</div>` : ''}
    </div>
  `;

      new maplibregl.Popup()
        .setLngLat(e.lngLat)
        .setHTML(html)
        .addTo(mapInstance);
    });


    mapInstance.on('mouseenter', 'trees-circle', () => {
      mapInstance.getCanvas().style.cursor = 'pointer'
    })

    mapInstance.on('mousemove', 'trees-circle', (e) => {
      const feature = e.features?.[0];
      if (!feature) return;

      const id = feature.properties?.id ?? feature.id;
      if (!id) return;

      // If this feature is selected, don't show hover ring on top – keep only selected ring
      if (id === selectedId) {
        hoveredId = null;
        mapInstance.setFilter('trees-circle-hover', ['==', ['get', 'id'], -1]);
      } else {
        hoveredId = id;
        mapInstance.setFilter('trees-circle-hover', ['==', ['get', 'id'], hoveredId]);
      }

      mapInstance.getCanvas().style.cursor = 'pointer';
    });

    mapInstance.on('mouseleave', 'trees-circle', () => {
      hoveredId = null;
      mapInstance.setFilter('trees-circle-hover', ['==', ['get', 'id'], -1]);
      mapInstance.getCanvas().style.cursor = '';
    });

    mapInstance.on('click', (e) => {
      // Skip clicks that hit a tree feature – those are handled above
      const features = mapInstance.queryRenderedFeatures(e.point, {
        layers: ['trees-circle'],
      });

      if (features.length === 0) {
        clearSelection(mapInstance);
      }
    });
  }

  setInitialFilter?.('status')
}

export async function loadNeighborhoodsLayer(mapInstance, { onDataLoaded, onNeighborhoodSelected }) {
  const res = await fetch('/api/neighborhoods')
  if (!res.ok) {
    throw new Error(`Failed to load neighborhoods: ${res.status}`)
  }
  const data = await res.json()

  onDataLoaded?.(data)

  if (!mapInstance.getSource('neighborhoods')) {
    mapInstance.addSource('neighborhoods', {
      type: 'geojson',
      data,
    })

    mapInstance.addLayer({
      id: 'neighborhoods-fill',
      type: 'fill',
      source: 'neighborhoods',
      paint: {
        'fill-color': '#1d4ed8',
        'fill-opacity': 0.0,
      },
    })

    mapInstance.addLayer({
      id: 'neighborhoods-outline',
      type: 'line',
      source: 'neighborhoods',
      paint: {
        'line-color': '#1d4ed8',
        'line-width': 0,
      },
    })

    mapInstance.on('mouseenter', 'neighborhoods-fill', () => {
      mapInstance.getCanvas().style.cursor = 'pointer'
    })
    mapInstance.on('mouseleave', 'neighborhoods-fill', () => {
      mapInstance.getCanvas().style.cursor = ''
    })
  } else {
    mapInstance.getSource('neighborhoods').setData(data)
  }
}
