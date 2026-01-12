import { safeJsonParse } from '@/Composables/safeJsonParser'
import maplibregl from 'maplibre-gl'

export async function fetchTreeDetails(treeId, { onDataLoaded } = {}) {
  const res = await fetch(`/api/trees/${treeId}`)

  if (!res.ok) {
    throw new Error(`Failed to load tree ${treeId}: ${res.status}`)
  }

  const data = await res.json()
  onDataLoaded?.(data)
  return data
}


export async function loadTreesLayer(mapInstance, { onDataLoaded, onTreeSelected, onTreeHovered, setInitialFilter, isInteractionEnabled }) {
  const res = await fetch('/api/trees')
  if (!res.ok) {
    throw new Error(`Failed to load trees: ${res.status}`)
  }
  const data = await res.json()

  const interactionsAllowed = () => (isInteractionEnabled ? !!isInteractionEnabled() : true)

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
          8, 1,   // zoom starts from 0 (full map) to 22(full zoomed)
          14, 1.25,
          20, 6,
        ],
        'circle-color': '#16a34a',
        'circle-stroke-width': 10,
        'circle-stroke-color': 'rgba(0,0,0,0)',
        'circle-opacity': 1

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

    mapInstance.on('click', 'trees-circle', (e) => {
      if (!interactionsAllowed()) return
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

      const feature = data.features?.find(
        f => f.properties?.id === numericId
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

      // this will set filters, start pulse, and pass parsed species/neighborhood to Vue
      handleTreeClick(feature);
    }

    setInitialFilter?.('status')



    return {
      selectTreeById,
      clearSelection,
    };

  }
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
        'line-width': 0.0,
      },
    })
  } else {
    mapInstance.getSource('neighborhoods').setData(data)
  }

}
