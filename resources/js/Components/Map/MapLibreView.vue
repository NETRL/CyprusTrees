<template>
    <div ref="mapContainer" class="map-container w-full h-full"></div>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from 'vue'
import maplibregl from 'maplibre-gl'

const mapContainer = ref(null)
const map = ref(null)

// initial coordinates for your city
const center = [33.37, 35.17]  // [lon, lat]
const zoom = 12

onMounted(() => {
    map.value = new maplibregl.Map({
        container: mapContainer.value,
        style: {
            version: 8,
            sources: {
                osm: {
                    type: 'raster',
                    tiles: ['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png'],
                    tileSize: 256,
                },
            },
            layers: [
                {
                    id: 'osm',
                    type: 'raster',
                    source: 'osm',
                },
            ],
        },
        attribution:
            '© <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
        center,
        zoom,
    })

    map.value.addControl(new maplibregl.NavigationControl())

    map.value.on('load', () => {
        console.log('MapLibre loaded')

        // // 1) Fetch GeoJSON from your Laravel API
        fetch('/api/neighborhoods')
            .then((res) => {
                if (!res.ok) {
                    throw new Error(`Failed to load neighborhoods: ${res.status}`)
                }
                return res.json()
            })
            .then((data) => {
                if (!map.value) return

                // 2) Add source
                map.value.addSource('neighborhoods', {
                    type: 'geojson',
                    data,
                })

                // 3) Add fill layer
                map.value.addLayer({
                    id: 'neighborhoods-fill',
                    type: 'fill',
                    source: 'neighborhoods',
                    paint: {
                        'fill-color': '#1d4ed8',
                        'fill-opacity': 0.25,
                    },
                })

                // 4) Add outline layer
                map.value.addLayer({
                    id: 'neighborhoods-outline',
                    type: 'line',
                    source: 'neighborhoods',
                    paint: {
                        'line-color': '#1d4ed8',
                        'line-width': 1,
                    },
                })

                // 5) Click → popup
                map.value.on('click', (e) => {
                    const features = map.value.queryRenderedFeatures(e.point, {
                        layers: ['neighborhoods-fill'],
                    })

                    console.log('Clicked features:', features)

                    if (!features.length) return

                    const feature = features[0]
                    const { name, geom_ref } = feature.properties

                    new maplibregl.Popup()
                        .setLngLat(e.lngLat)
                        .setHTML(
                            `<div>
                                <strong>${name}</strong><br/>
                                <small>${geom_ref}</small>
                            </div>`
                        )
                        .addTo(map.value)
                })

                // 6) Cursor change on hover
                map.value.on('mouseenter', 'neighborhoods-fill', () => {
                    map.value.getCanvas().style.cursor = 'pointer'
                })

                map.value.on('mouseleave', 'neighborhoods-fill', () => {
                    map.value.getCanvas().style.cursor = ''
                })
            })
            .catch((err) => {
                console.error(err)
            })
    })
})

onBeforeUnmount(() => {
    if (map.value) {
        map.value.remove()
        map.value = null
    }
})
</script>

