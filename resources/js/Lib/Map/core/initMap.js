import maplibregl from 'maplibre-gl'
import { setupBaseLayers } from '@/Lib/Map/core/setupBaseLayers'

export async function initMap(container) {

  const MAPTILER_KEY = import.meta.env.VITE_MAPTILER_KEY
  const styleJson = await fetch(`https://api.maptiler.com/maps/streets-v2/style.json?key=${MAPTILER_KEY}`).then(res => res.json())

  const center = [33.37, 35.17]
  const zoom = 12

  const map = new maplibregl.Map({
    container,
    style: styleJson,
    attributionControl: false,
    center,
    zoom,
  })

  // Add standard controls here
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

  setupBaseLayers(map, MAPTILER_KEY)

  return map
}
