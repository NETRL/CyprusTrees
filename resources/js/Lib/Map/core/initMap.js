import maplibregl from 'maplibre-gl'

export async function initMap(container, { center, zoom, maptilerKey }) {
  const styleJson = await fetch(`https://api.maptiler.com/maps/streets-v2/style.json?key=${maptilerKey}`).then(res => res.json())

  const map = new maplibregl.Map({
    container,
    style: styleJson,
    attributionControl: false,
    center,
    zoom,
  })

  // Add standard controls here if you like
  map.addControl(
    new maplibregl.AttributionControl({
      compact: false,
      customAttribution: 'City of Nicosia',
    }),
    'bottom-left'
  )
  map.addControl(new maplibregl.NavigationControl({
    // visualizePitch: true,
    // visualizeRoll: true,
    showZoom: false,
    showCompass: true

  }))

  // Wait for load once, return a promise
  await new Promise(resolve => map.once('load', resolve))

  return { map, styleJson }
}
