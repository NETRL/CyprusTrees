<template>
  <MapSidebar :selectedData="selectedData" />
  <div ref="mapContainer" class="map-container w-full h-full"></div>

  <!-- Loading overlay controlled by v-model -->
  <MapLoadingOverlay :isLoading="isLoading" />
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount, watch } from 'vue'
import MapSidebar from '@/Components/Map/Partials/MapSidebar.vue'
import MapLoadingOverlay from '@/Components/Map/Partials/MapLoadingOverlay.vue'

import { initMap } from '@/Lib/Map/InitMap'
import { setupBaseLayers } from '@/Lib/Map/SetupBaseLayers'
import { loadTreesLayer, loadNeighborhoodsLayer } from '@/Lib/Map/DataLayers'
import { useMapFilter } from '@/Composables/useMapFilter'
import { useMapColors } from '@/Composables/useMapColors'

const mapContainer = ref(null)
const map = ref(null)

const isLoading = ref(true)

const treesData = ref([])
const neighborhoodData = ref([])
const selectedData = ref(null)

const center = [33.37, 35.17]
const zoom = 12

const MAPTILER_KEY = import.meta.env.VITE_MAPTILER_KEY
const CUSTOM_VECTOR_STYLES = [
  {
    id: 'darkGreen',
    name: 'Dark Green',
    styleUrl: `https://api.maptiler.com/maps/019abffb-04c2-7927-8c58-ff64512e9321/style.json?key=${MAPTILER_KEY}`,
    preview: '/storage/images/map-custom.png',
  },
  {
    id: 'lightStreets',
    name: 'Light Streets',
    styleUrl: `https://api.maptiler.com/maps/019ac206-e7bc-7796-8930-3a253cad08d9/style.json?key=${MAPTILER_KEY}`,
    preview: '/storage/images/map-default.png',
  },
  {
    id: 'landcapeDark',
    name: 'Landscape Dark',
    styleUrl: `https://api.maptiler.com/maps/019ac206-7965-7795-8035-d9b24b5c8815/style.json?key=${MAPTILER_KEY}`,
    preview: '/storage/images/map-custom.png',
  },
]

const { selectedFilter } = useMapFilter()

const { STATUS_COLORS, WATER_USE_COLORS, SHADE_COLORS, ORIGIN_COLORS, POLLEN_RISK_COLORS } =
  useMapColors()

onMounted(async () => {
  try {
    const { map: m } = await initMap(mapContainer.value, {
      center,
      zoom,
      styleUrl: CUSTOM_VECTOR_STYLES[0].styleUrl,
    })

    map.value = m

    setupBaseLayers(m, {
      maptilerKey: MAPTILER_KEY,
      vectorStyles: CUSTOM_VECTOR_STYLES,
    })

    await Promise.all([
      loadNeighborhoodsLayer(m, {
        onDataLoaded: (data) => (neighborhoodData.value = data),
        onNeighborhoodSelected: (props) => (selectedData.value = props),
      }),
      loadTreesLayer(m, {
        onDataLoaded: (data) => (treesData.value = data),
        onTreeSelected: (props) => (selectedData.value = props),
        setInitialFilter: (val) => (selectedFilter.value = val),
      }),
    ])
  } catch (e) {
    console.error(e)
    // could set a dedicated error state here
  } finally {
    isLoading.value = false
  }
})

watch(
  selectedFilter,
  (mode) => {
    if (!map.value || !map.value.getLayer('trees-circle')) return
    visualiseTreeData(mode)
  },
  { immediate: true }
)

const visualiseTreeData = (mode) => {
  if (!map.value || !map.value.getLayer('trees-circle')) return

  let propertyKey
  let colorExpression
  const DEFAULT_COLOR = '#16a34a'

  switch (mode) {
    case 'status':
      propertyKey = ['get', 'status']
      colorExpression = ['match', propertyKey, ...STATUS_COLORS]
      break
    case 'origin':
      propertyKey = ['get', 'species_origin']
      colorExpression = ['match', propertyKey, ...ORIGIN_COLORS]
      break
    case 'pollen_risk':
      propertyKey = ['get', 'calculated_pollen_risk']
      colorExpression = ['step', propertyKey, ...POLLEN_RISK_COLORS]
      break
    case 'water_use':
      propertyKey = ['get', 'species_drought_tolerance']
      colorExpression = ['match', propertyKey, ...WATER_USE_COLORS]
      break
    case 'shade':
      propertyKey = ['get', 'species_canopy_class']
      colorExpression = ['match', propertyKey, ...SHADE_COLORS]
      break
    default:
      map.value.setPaintProperty('trees-circle', 'circle-color', DEFAULT_COLOR)
      return
  }

  if (colorExpression) {
    map.value.setPaintProperty('trees-circle', 'circle-color', colorExpression)
  }
}

onBeforeUnmount(() => {
  if (map.value) {
    map.value.remove()
    map.value = null
  }
})
</script>
