<template>
    <MapSidebar :selectedData="selectedData" />
    <div ref="mapContainer" class="map-container w-full h-full"></div>

</template>

<script setup>
import { onMounted, ref, onBeforeUnmount, watch } from 'vue'
import maplibregl from 'maplibre-gl'
import MapSidebar from '@/Components/Map/Partials/MapSidebar.vue'

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
const CUSTOM_STYLE_URL = `https://api.maptiler.com/maps/019abffb-04c2-7927-8c58-ff64512e9321/style.json?key=${MAPTILER_KEY}`


onMounted(async () => {

    const styleJson = await fetch(CUSTOM_STYLE_URL).then(res => res.json())

    map.value = new maplibregl.Map({
        container: mapContainer.value,
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


    map.value.addControl(
        new maplibregl.AttributionControl({
            compact: false,
            customAttribution: 'City of Nicosia',
        }),
        'bottom-right'
    )

    // Navigation control
    map.value.addControl(new maplibregl.NavigationControl())

    map.value.on('load', () => {

        // 1) OSM Standard
        map.value.addSource('osmStandard', {
            type: 'raster',
            tiles: ['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png'],
            tileSize: 256,
        })

        map.value.addLayer({
            id: 'osmStandardLayer',
            type: 'raster',
            source: 'osmStandard',
            layout: { visibility: 'visible' }, // default visible
        })

        // 2) MapTiler Dark
        map.value.addSource('maptilerDark', {
            type: 'raster',
            tiles: [
                `https://api.maptiler.com/maps/dataviz-dark/{z}/{x}/{y}@2x.png?key=${MAPTILER_KEY}`,
            ],
            tileSize: 256,
        })

        map.value.addLayer({
            id: 'maptilerDarkLayer',
            type: 'raster',
            source: 'maptilerDark',
            layout: { visibility: 'none' },
        })

        // 3) Carto Dark
        map.value.addSource('cartoDark', {
            type: 'raster',
            tiles: ['https://s.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'],
            tileSize: 256,
        })

        map.value.addLayer({
            id: 'cartoDarkLayer',
            type: 'raster',
            source: 'cartoDark',
            layout: { visibility: 'none' },
        })

        // We'll load it dynamically and merge it
        fetch(CUSTOM_STYLE_URL)
            .then(res => res.json())
            .then(styleJson => {
                // Set glyphs if the style has them (required for text layers)
                if (styleJson.glyphs && !map.value.getStyle().glyphs) {
                    const currentStyle = map.value.getStyle()
                    currentStyle.glyphs = styleJson.glyphs
                    // Also add sprite if available
                    if (styleJson.sprite) {
                        currentStyle.sprite = styleJson.sprite
                    }
                }
                // Add all sources from the custom style
                Object.keys(styleJson.sources).forEach(sourceId => {
                    if (!map.value.getSource(sourceId)) {
                        map.value.addSource(sourceId, styleJson.sources[sourceId])
                    }
                })

                // Add all layers from the custom style with visibility: none
                styleJson.layers.forEach(layer => {
                    if (!map.value.getLayer(layer.id)) {
                        map.value.addLayer({
                            ...layer,
                            layout: {
                                ...layer.layout,
                                visibility: 'none'
                            }
                        })
                    }
                })

                // Now add the basemap control with the custom style option
                const baseMapControl = new BaseMapControl({
                    defaultLayerId: 'osmStandardLayer',
                    layers: [
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
                        {
                            id: 'customMapTilerStyle',
                            name: 'Dark Green',
                            type: 'vector',
                            layerIds: styleJson.layers.map(l => l.id), // All layer IDs from the style
                            preview: '/storage/images/map-custom.png',
                        },
                    ],
                    map: map.value
                })

                map.value.addControl(baseMapControl, 'top-right')

                fetchNeighborhoods(map.value)
                fetchTrees(map.value)
            })
            .catch(err => {
                console.error('Failed to load custom style:', err)

                // Fallback: add control without custom style
                const baseMapControl = new BaseMapControl({
                    defaultLayerId: 'osmStandardLayer',
                    layers: [
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
                    ],
                    map: map.value
                })

                map.value.addControl(baseMapControl, 'top-right')

                fetchNeighborhoods(map.value)
                fetchTrees(map.value)
            })
    })
})

class BaseMapControl {
    constructor(options) {
        this._layers = options.layers;
        this._defaultLayerId = options.defaultLayerId || (options.layers[0] && options.layers[0].id);
        this._map = options.map;
        this._container = null;
        this._isExpanded = false;
        this._activeId = this._defaultLayerId;
        this._handleDocumentClick = this._handleDocumentClick.bind(this);
    }

    onAdd(map) {
        this._map = map;

        const container = document.createElement("div");
        container.className = "maplibregl-ctrl";
        container.style.position = "relative";

        // --- Preview Button (Collapsed State) ---
        const previewBox = document.createElement("button");
        previewBox.type = "button";
        previewBox.className =
            "basemap-preview relative w-13 h-13 lg:w-16 lg:h-16 rounded-xl overflow-hidden cursor-pointer " +
            "border-2 border-white dark:border-gray-400 " +
            "shadow-md hover:shadow-xl hover:scale-105 transition-all duration-100 ease-out " +
            "focus:outline-none focus:ring-2 focus:ring-gray-700 focus:ring-offset-2 dark:focus:ring-offset-gray-900";

        // Tooltip (optional, appears on hover)
        previewBox.setAttribute("aria-label", "Change map style");

        const previewImg = document.createElement("div");
        previewImg.className = "w-full h-full bg-center bg-no-repeat bg-cover";
        previewBox.appendChild(previewImg);

        // --- Expanded Panel ---
        const expandedPanel = document.createElement("div");
        expandedPanel.className =
            "absolute top-0 right-0 bg-white dark:bg-gray-900 " +
            "border border-gray-200 dark:border-gray-800 " +
            "rounded-xl shadow-2xl p-3 min-w-[260px] z-50 " +
            "transform origin-top-right transition-all duration-200";
        expandedPanel.style.display = "none";

        // Panel Header
        const header = document.createElement("div");
        header.className = "flex items-center justify-between mb-3 px-1";

        const panelTitle = document.createElement("h3");
        panelTitle.className = "text-sm font-bold text-gray-800 dark:text-gray-100";
        panelTitle.textContent = "Map Style";

        header.appendChild(panelTitle);
        expandedPanel.appendChild(header);

        // Grid Layout
        const layerGrid = document.createElement("div");
        layerGrid.className = "grid grid-cols-2 gap-3";

        this._layers.forEach(layer => {
            const optionBtn = document.createElement("button");
            optionBtn.type = "button";
            // Base classes
            optionBtn.className =
                "relative group flex flex-col items-center cursor-pointer text-left w-full " +
                "focus:outline-none rounded-lg";

            // Image Container
            const imgContainer = document.createElement("div");
            imgContainer.className =
                "w-full h-20 rounded-lg overflow-hidden mb-2 relative " +
                "border-2 transition-all duration-200 ease-in-out " +
                "group-hover:shadow-md";

            // The background image
            const imgDisplay = document.createElement("div");
            imgDisplay.className = "w-full h-full bg-center bg-no-repeat bg-cover transition-transform duration-300 group-hover:scale-110";
            imgDisplay.style.backgroundImage = `url('${layer.preview || ""}')`;

            // Checkmark Overlay (Hidden by default, shown via JS toggling)
            const checkOverlay = document.createElement("div");
            checkOverlay.className = "absolute inset-0 bg-brand-600/20 hidden flex items-center justify-center";
            checkOverlay.innerHTML = `<svg class="w-6 h-6 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>`;

            imgContainer.appendChild(imgDisplay);
            imgContainer.appendChild(checkOverlay);

            // Label
            const label = document.createElement("span");
            label.className = "text-xs font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors";
            label.textContent = layer.name;

            optionBtn.appendChild(imgContainer);
            optionBtn.appendChild(label);

            optionBtn.addEventListener("click", e => {
                e.stopPropagation();
                this._selectLayer(layer.id);
            });

            layerGrid.appendChild(optionBtn);

            // Store references for updates
            layer.elements = {
                btn: optionBtn,
                container: imgContainer,
                check: checkOverlay,
                label: label
            };
        });

        expandedPanel.appendChild(layerGrid);

        // Event Listeners
        previewBox.addEventListener("click", (e) => {
            e.stopPropagation();
            this._toggle();
        });

        document.addEventListener("click", this._handleDocumentClick);
        expandedPanel.addEventListener("click", e => e.stopPropagation());

        container.appendChild(previewBox);
        container.appendChild(expandedPanel);

        this._container = container;
        this._previewBox = previewBox;
        this._previewImg = previewImg;
        this._expandedPanel = expandedPanel;

        // Initialize state
        if (this._defaultLayerId) {
            this._setActiveLayer(this._defaultLayerId);
        }
        this._updateUI();

        return container;
    }

    _handleDocumentClick() {
        if (this._isExpanded) this._collapse();
    }

    _toggle() {
        this._isExpanded ? this._collapse() : this._expand();
    }

    _expand() {
        this._isExpanded = true;
        this._expandedPanel.style.display = "block";
        // Small timeout to allow browser to register display:block before animating opacity if you wanted to add fade-in
    }

    _collapse() {
        this._isExpanded = false;
        this._expandedPanel.style.display = "none";
    }

    // Helper to handle the logic of selecting a layer (UI + Map)
    _selectLayer(id) {
        this._setActiveLayer(id);
        this._updateUI();
        this._collapse();
    }

    _getActiveLayerId() {
        // Logic remains the same as your original code
        const active = this._layers.find(layer => {
            if (layer.type === "vector" && layer.layerIds) {
                return layer.layerIds.some(layerId => {
                    const l = this._map.getLayer(layerId);
                    return l && this._map.getLayoutProperty(layerId, "visibility") === "visible";
                });
            }
            const l = this._map.getLayer(layer.id);
            return l && this._map.getLayoutProperty(layer.id, "visibility") === "visible";
        });
        return active?.id || this._defaultLayerId;
    }

    _updateUI() {
        const activeId = this._getActiveLayerId();

        // 1. Update Preview Box Image
        const activeLayer = this._layers.find(l => l.id === activeId);
        if (activeLayer) {
            this._previewImg.style.backgroundImage = `url('${activeLayer.preview || ""}')`;
        }

        // 2. Update Grid Selection State
        this._layers.forEach(layer => {
            if (!layer.elements) return;

            const isActive = layer.id === activeId;
            const { container, check, label } = layer.elements;

            if (isActive) {
                // Active State Styling
                container.classList.remove("border-transparent", "border-gray-200", "dark:border-gray-700");
                container.classList.add("border-brand-600", "ring-2", "ring-brand-600", "ring-offset-1", "ring-offset-white", "dark:ring-offset-gray-900");

                check.classList.remove("hidden");

                label.classList.add("text-brand-600", "dark:text-brand-400", "font-bold");
                label.classList.remove("text-gray-600", "dark:text-gray-400");
            } else {
                // Inactive State Styling
                container.classList.add("border-gray-200", "dark:border-gray-700");
                container.classList.remove("border-brand-600", "ring-2", "ring-brand-600", "ring-offset-1", "ring-offset-white", "dark:ring-offset-gray-900");

                check.classList.add("hidden");

                label.classList.remove("text-brand-600", "dark:text-brand-400", "font-bold");
                label.classList.add("text-gray-600", "dark:text-gray-400");
            }
        });
    }

    _setActiveLayer(activeId) {
        if (!this._map) return;
        this._layers.forEach(layer => {
            const visibility = layer.id === activeId ? "visible" : "none";
            if (layer.type === "vector" && layer.layerIds) {
                layer.layerIds.forEach(layerId => {
                    if (this._map.getLayer(layerId)) {
                        this._map.setLayoutProperty(layerId, "visibility", visibility);
                    }
                });
            } else {
                if (this._map.getLayer(layer.id)) {
                    this._map.setLayoutProperty(layer.id, "visibility", visibility);
                }
            }
        });
    }

    onRemove() {
        document.removeEventListener("click", this._handleDocumentClick);
        if (this._container && this._container.parentNode) {
            this._container.parentNode.removeChild(this._container);
        }
        this._map = null;
        this._container = null;
    }
}





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
