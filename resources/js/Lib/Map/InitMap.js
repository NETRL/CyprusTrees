import maplibregl from 'maplibre-gl'

export async function initMap(container, { center, zoom, styleUrl }) {
  const styleJson = await fetch(styleUrl).then(res => res.json())

  const map = new maplibregl.Map({
    container,
    style: {
      version: 8,
      glyphs: styleJson.glyphs,
      sprite: styleJson.sprite,
      sources: {},
      layers: [],
    },
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
    visualizePitch: true,
    visualizeRoll: true,
    showZoom: false,
    showCompass: true

  }))

  // Wait for load once, return a promise
  await new Promise(resolve => map.once('load', resolve))

  return { map, styleJson }
}
