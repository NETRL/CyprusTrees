<template>
    <MapSidebar :selectedData="selectedData" />
    <div ref="mapContainer" class="map-container w-full h-full"></div>

</template>

<script setup>
import { onMounted, ref, onBeforeUnmount, watch } from 'vue'
import maplibregl from 'maplibre-gl'
import MapSidebar from '@/Components/Map/Partials/MapSidebar.vue'
import { initMap } from '@/Lib/Map/InitMap'
import { setupBaseLayers } from '@/Lib/Map/SetupBaseLayers'
import { useMapFilter } from '@/Composables/useMapFilter'

const mapContainer = ref(null)
const map = ref(null)

const treesData = ref([])
const neighborhoodData = ref([])
const selectedData = ref(null)

// initial coordinates
const center = [33.37, 35.17]  // [lon, lat]
const zoom = 12

// Your MapTiler API key & style ID
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
        styleUrl: `https://api.maptiler.com/maps/streets-v2/style.json?key=${MAPTILER_KEY}`,
        preview: '/storage/images/map-custom.png',
    },
    {
        id: 'landcapeDark',
        name: 'Landscape Dark',
        styleUrl: `https://api.maptiler.com/maps/landscape-dark/style.json?key=${MAPTILER_KEY}`,
        preview: '/storage/images/map-custom.png',
    },
]

const { selectedFilter } = useMapFilter()

watch(selectedFilter, v => {
    console.log(v)
})

onMounted(async () => {
    const { map: m, styleJson } = await initMap(mapContainer.value, {
        center,
        zoom,
        styleUrl: CUSTOM_VECTOR_STYLES[0].styleUrl,
    })

    map.value = m

    setupBaseLayers(m, {
        maptilerKey: MAPTILER_KEY,
        vectorStyles: CUSTOM_VECTOR_STYLES,
    })

    fetchNeighborhoods(m)
    fetchTrees(m)
    //   await Promise.all([
    //   ])
})


const fetchTrees = (mapInstance) => {
    fetch('/api/trees')
        .then((res) => {
            if (!res.ok) {
                throw new Error(`Failed to load trees: ${res.status}`)
            }
            return res.json()
        })
        .then((data) => {
            if (!mapInstance) return

            treesData.value = data;
            // If source already exists (e.g. reloading data), just update it
            if (mapInstance.getSource('trees')) {
                mapInstance.getSource('trees').setData(data)
            } else {
                // 1) Add GeoJSON source
                mapInstance.addSource('trees', {
                    type: 'geojson',
                    data: data,
                })

                // 2) Add circle layer for tree points
                mapInstance.addLayer({
                    id: 'trees-circle',
                    type: 'circle',
                    source: 'trees',
                    paint: {
                        'circle-radius': 4,
                        'circle-color': '#16a34a', // green
                        'circle-stroke-width': 1,
                        'circle-stroke-color': '#064e3b',
                    },
                })

                // 3) click popup for trees
                mapInstance.on('click', 'trees-circle', (e) => {
                    const feature = e.features?.[0]
                    if (!feature) return

                    const p = feature.properties

                    selectedData.value = p;

                    console.log(p)

                    // species & neighborhood are JSON-encoded strings in props
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

                    new maplibregl.Popup()
                        .setLngLat(e.lngLat)
                        .setHTML(html)
                        .addTo(mapInstance)

                })

                mapInstance.on('mouseenter', 'trees-circle', () => {
                    mapInstance.getCanvas().style.cursor = 'pointer'
                })

                mapInstance.on('mouseleave', 'trees-circle', () => {
                    mapInstance.getCanvas().style.cursor = ''
                })
            }
        })
        .catch((err) => {
            console.error(err)
        })
}



const fetchNeighborhoods = (mapInstance) => {
    // Fetch GeoJSON from the API
    fetch('/api/neighborhoods')
        .then((res) => {
            if (!res.ok) {
                throw new Error(`Failed to load neighborhoods: ${res.status}`)
            }
            return res.json()
        })
        .then((data) => {
            if (!mapInstance) return
            neighborhoodData.value = data;
            // 2) Add source
            mapInstance.addSource('neighborhoods', {
                type: 'geojson',
                data,
            })

            // 3) Add fill layer
            mapInstance.addLayer({
                id: 'neighborhoods-fill',
                type: 'fill',
                source: 'neighborhoods',
                paint: {
                    'fill-color': '#1d4ed8',
                    'fill-opacity': 0.25,
                },
            })

            // 4) Add outline layer
            mapInstance.addLayer({
                id: 'neighborhoods-outline',
                type: 'line',
                source: 'neighborhoods',
                paint: {
                    'line-color': '#1d4ed8',
                    'line-width': 1,
                },
            })

            // 5) Click → popup
            mapInstance.on('click', (e) => {
                const features = mapInstance.queryRenderedFeatures(e.point, {
                    layers: ['neighborhoods-fill'],
                })

                if (!features.length) return

                const feature = features[0]

                const { name, geom_ref } = feature.properties
                selectedData.value = feature.properties

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

            // 6) Cursor change on hover
            mapInstance.on('mouseenter', 'neighborhoods-fill', () => {
                mapInstance.getCanvas().style.cursor = 'pointer'
            })

            mapInstance.on('mouseleave', 'neighborhoods-fill', () => {
                mapInstance.getCanvas().style.cursor = ''
            })
        })
        .catch((err) => {
            console.error(err)
        })
}

onBeforeUnmount(() => {
    if (map.value) {
        map.value.remove()
        map.value = null
    }
})
</script>
