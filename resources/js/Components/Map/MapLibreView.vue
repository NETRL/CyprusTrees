<template>
    <div ref="mapContainer" class="w-full h-full"></div>
</template>


<script setup>

import { onMounted, ref } from 'vue'
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
            layers: [{ id: 'osm', type: 'raster', source: 'osm' }],
        },
        attribution:'© <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
        center,
        zoom,
    })

    // optional: navigation zoom buttons
    map.value.addControl(new maplibregl.NavigationControl())

    map.value.on('load', () => {
        console.log('MapLibre loaded')
        // Example: add a dummy GeoJSON layer
        map.value.addSource('trees', {
            type: 'geojson',
            data: {
                type: 'FeatureCollection',
                features: [{
                    type: 'Feature',
                    geometry: { type: 'Point', coordinates: center },
                    properties: { name: 'Test Tree' }
                }]
            }
        })

        map.value.addLayer({
            id: 'trees-points',
            type: 'circle',
            source: 'trees',
            paint: { 'circle-radius': 10, 'circle-color': 'green' }
        })

        map.value.on('click', 'trees-points', e => {
            new maplibregl.Popup()
                .setLngLat(e.lngLat)
                .setHTML(`<strong>${e.features[0].properties.name}</strong>`)
                .addTo(map.value)
        })

    })


})
</script>

<style scoped>
@import 'maplibre-gl/dist/maplibre-gl.css';
</style>