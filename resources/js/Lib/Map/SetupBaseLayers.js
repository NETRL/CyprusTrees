import { BaseMapControl } from '@/Lib/Map/Controls/BaseMapControl'
import { getRasterLayers, getVectorLayers } from '@/Lib/Map/BaseMapStyles'

export async function setupBaseLayers(map, maptilerKey) {

  const vectorLayers = getVectorLayers(maptilerKey)
  const rasterLayers = getRasterLayers(maptilerKey)

  rasterLayers.forEach(layer => {
    map.addSource(layer.source, {
      type: layer.type,
      tiles: layer.tiles,
      tileSize: layer.tileSize
    })

    map.addLayer({
      id: layer.id,
      type: layer.type,
      source: layer.source,
      layout: { visibility: 'none' },

    })
  })

  // Multiple custom MapTiler vector styles
  for (const styleDef of vectorLayers) {
    const styleJson = await fetch(styleDef.styleUrl).then(res => res.json())

    const styleLayerIds = addVectorStyleAsNamespace(map, styleJson, styleDef.id)

    if (styleLayerIds.length) {
      rasterLayers.push({
        id: styleDef.id,
        name: styleDef.name,
        type: 'vector',
        layerIds: styleLayerIds,
        preview: styleDef.preview,
      })
    }
  }

  let defaultMap = "cartoLightLayer"
  // if( window.location.pathname.startsWith('/map2')){
  //   defaultMap = "osmStandardLayer"
  // } 

  const baseMapControl = new BaseMapControl({
    // defaultLayerId: rasterLayers[0].id,
    defaultLayerId: defaultMap,
    layers: rasterLayers,
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

