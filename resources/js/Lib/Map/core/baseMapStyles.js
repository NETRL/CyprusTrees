export function getVectorLayers(maptilerKey) {
  return [
    {
      id: 'darkGreen',
      name: 'Dark Green',
      styleUrl: `https://api.maptiler.com/maps/019abffb-04c2-7927-8c58-ff64512e9321/style.json?key=${maptilerKey}`,
      preview: '/storage/images/map-custom.png',
    },
    {
      id: 'satellite',
      name: 'Satellite View',
      styleUrl: `https://api.maptiler.com/maps/hybrid/style.json?key=${maptilerKey}`,
      preview: '/storage/images/map-default.png',
    },
    {
      id: 'street',
      name: 'Street View',
      styleUrl: `https://api.maptiler.com/maps/019c27fc-f979-75a2-8a6b-bbe7e6ce558b/style.json?key=${maptilerKey}`,
      preview: '/storage/images/map-default.png',
    },
    {
      id: 'pastel',
      name: 'Pastel View',
      styleUrl: `https://api.maptiler.com/maps/019ac201-8ff9-76ea-b752-4b2b1e4ed570/style.json?key=${maptilerKey}`,
      preview: '/storage/images/map-default.png',
    },
  ]
}

export function getRasterLayers(maptilerKey) {
  return [
    {
      source: 'osmStandard',
      id: 'osmStandardLayer',
      name: 'Standard',
      type: 'raster',
      preview: '/storage/images/map-default.png',
      tiles: ['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png'],
      tileSize: 256,
    },
    {
      source: 'maptilerDark',
      id: 'maptilerDarkLayer',
      name: 'Dark',
      type: 'raster',
      preview: '/storage/images/map-dark.png',
      tiles: [`https://api.maptiler.com/maps/dataviz-dark/{z}/{x}/{y}@2x.png?key=${maptilerKey}`],
      tileSize: 256,
    },
    {
      source: 'cartoLight',
      id: 'cartoLightLayer',
      name: 'Carto',
      type: 'raster',
      preview: '/storage/images/map-default.png',
      tiles: ['https://s.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'],
      tileSize: 256,

    },
    {
      source: 'satellite2',
      id: 'satellite2',
      name: 'Satellite View2',
      type: 'raster',
      preview: '/storage/images/map-default.png',
      tiles: [`https://api.maptiler.com/maps/hybrid/256/{z}/{x}/{y}.jpg?key=${maptilerKey}`],
      tileSize: 256,
    },
  ]
}
