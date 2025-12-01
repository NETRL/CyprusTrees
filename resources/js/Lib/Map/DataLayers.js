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
    })

    mapInstance.addLayer({
      id: 'trees-circle',
      type: 'circle',
      source: 'trees',
      paint: {
        'circle-radius': 3,
        'circle-stroke-width': 0,
        'circle-stroke-color': '#064e3b',
      },
    })

    mapInstance.on('click', 'trees-circle', (e) => {
      const feature = e.features?.[0]
      if (!feature) return

      const p = feature.properties
      onTreeSelected?.(p)

      let species = null
      let neighborhood = null
      try {
        species = p.species ? JSON.parse(p.species) : null
        neighborhood = p.neighborhood ? JSON.parse(p.neighborhood) : null
      } catch (err) {
        console.warn('Failed to parse nested props', err)
      }

      const html = `
        <div>
          <strong>Tree #${p.id}</strong><br/>
          ${species ? `<div>${species.common_name ?? ''}</div>` : ''}
          ${neighborhood ? `<div>${neighborhood.name ?? ''}</div>` : ''}
          ${p.address ? `<div>${p.address}</div>` : ''}
        </div>
      `

      new maplibregl.Popup().setLngLat(e.lngLat).setHTML(html).addTo(mapInstance)
    })

    mapInstance.on('mouseenter', 'trees-circle', () => {
      mapInstance.getCanvas().style.cursor = 'pointer'
    })
    mapInstance.on('mouseleave', 'trees-circle', () => {
      mapInstance.getCanvas().style.cursor = ''
    })
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

    mapInstance.on('click', (e) => {
      const features = mapInstance.queryRenderedFeatures(e.point, {
        layers: ['neighborhoods-fill'],
      })
      if (!features.length) return

      const feature = features[0]
      onNeighborhoodSelected?.(feature.properties)

      const { name, geom_ref } = feature.properties

      new maplibregl.Popup()
        .setLngLat(e.lngLat)
        .setHTML(
          `<div>
            <strong>${name}</strong><br/>
            <small>${geom_ref}</small>
          </div>`
        )
        .addTo(mapInstance)
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
