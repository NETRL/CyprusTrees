import { BaseMapControl } from '@/Lib/Map/BaseMapControl'

export async function setupBaseLayers(map, { maptilerKey, vectorStyles = [] }) {
  // Raster basemaps
  map.addSource('osmStandard', {
    type: 'raster',
    tiles: ['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png'],
    tileSize: 256,
  })
  map.addLayer({
    id: 'osmStandardLayer',
    type: 'raster',
    source: 'osmStandard',
    layout: { visibility: 'visible' },
  })

  map.addSource('maptilerDark', {
    type: 'raster',
    tiles: [
      `https://api.maptiler.com/maps/dataviz-dark/{z}/{x}/{y}@2x.png?key=${maptilerKey}`,
    ],
    tileSize: 256,
  })
  map.addLayer({
    id: 'maptilerDarkLayer',
    type: 'raster',
    source: 'maptilerDark',
    layout: { visibility: 'none' },
  })

  map.addSource('cartoDark', {
    type: 'raster',
    tiles: ['https://s.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'],
    tileSize: 256,
  })
  map.addLayer({
    id: 'cartoDarkLayer',
    type: 'raster',
    source: 'cartoDark',
    layout: { visibility: 'none' },
  })

  const layersConfig = [
    {
      id: 'osmStandardLayer',
      name: 'Standard',
      type: 'raster',
      preview: '/storage/images/map-default.png',
    },
    {
      id: 'maptilerDarkLayer',
      name: 'Dark',
      type: 'raster',
      preview: '/storage/images/map-dark.png',
    },
    {
      id: 'cartoDarkLayer',
      name: 'Carto',
      type: 'raster',
      preview: '/storage/images/map-carto.png',
    },
  ]

  // Multiple custom MapTiler vector styles
  for (const styleDef of vectorStyles) {
    const styleJson = await fetch(styleDef.styleUrl).then(res => res.json())

    const styleLayerIds = addVectorStyleAsNamespace(map, styleJson, styleDef.id)

    if (styleLayerIds.length) {
      layersConfig.push({
        id: styleDef.id,
        name: styleDef.name,
        type: 'vector',
        layerIds: styleLayerIds,
        preview: styleDef.preview,
      })
    }
  }

  const baseMapControl = new BaseMapControl({
    defaultLayerId: 'osmStandardLayer',
    layers: layersConfig,
    map,
  })

  map.addControl(baseMapControl, 'top-right')
}

function addVectorStyleAsNamespace(map, styleJson, namespace) {
  const layerIds = []

  // Ensure sources exist
  Object.entries(styleJson.sources || {}).forEach(([sourceId, sourceDef]) => {
    if (!map.getSource(sourceId)) {
      map.addSource(sourceId, sourceDef)
    }
  })

    // Add each layer with a namespaced ID, hidden by default
    ; (styleJson.layers || []).forEach(layer => {
      const namespacedId = `${namespace}__${layer.id}`

      if (map.getLayer(namespacedId)) {
        return
      }

      const clonedLayer = {
        ...layer,
        id: namespacedId,
        // keep the same `source` (e.g. "openmaptiles")
        layout: {
          ...(layer.layout || {}),
          visibility: 'none',
        },
      }

      map.addLayer(clonedLayer)
      layerIds.push(namespacedId)
    })

  return layerIds
}

