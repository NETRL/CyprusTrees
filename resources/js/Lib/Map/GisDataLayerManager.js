import { GisLayersControl } from "@/Lib/Map/Controls/GisLayerControl" 

export class GisDataLayerManager {
  constructor(map, {
    baseUrl = "/api/gis-map",
    controlPosition = "top-right",
    defaultVisibleKeys = [],
    fetchBbox = true,       // fetch only viewport bbox
    reloadOnMoveEnd = false // if true, refetch visible layers when viewport changes
  } = {}) {
    this.map = map
    this.baseUrl = baseUrl
    this.controlPosition = controlPosition
    this.defaultVisibleKeys = new Set(defaultVisibleKeys)
    this.fetchBbox = fetchBbox
    this.reloadOnMoveEnd = reloadOnMoveEnd

    this.layers = new Map() // key -> {meta, visible, sourceId, mapLayerIds[], loadedOnce}
    this.control = null

    this._onStyleData = this._onStyleData.bind(this)
    this._onMoveEnd = this._onMoveEnd.bind(this)
  }

  async init() {
    await this.refreshCatalog()

    this.control = new GisLayersControl(this, { title: "GIS Layers" })
    this.map.addControl(this.control, this.controlPosition)

    // ensure persistence through style switches
    this.map.on("styledata", this._onStyleData)

    if (this.reloadOnMoveEnd) {
      this.map.on("moveend", this._onMoveEnd)
      this.map.on("zoomend", this._onMoveEnd)
    }

    // apply defaults
    for (const [key, rec] of this.layers.entries()) {
      if (this.defaultVisibleKeys.has(key)) {
        await this.setVisible(key, true)
      }
    }
  }

  destroy() {
    this.map.off("styledata", this._onStyleData)
    this.map.off("moveend", this._onMoveEnd)
    this.map.off("zoomend", this._onMoveEnd)
    if (this.control) this.map.removeControl(this.control)
  }

  getLayersForUI() {
    return Array.from(this.layers.values()).map(r => ({
      key: r.key,
      display_name: r.display_name,
      category: r.category,
      color: r.color,
      visible: r.visible
    }))
  }

  // fetch the layers
  async refreshCatalog() {
    const res = await fetch(`${this.baseUrl}/layers`, { headers: { "Accept": "application/json" } })
    if (!res.ok) throw new Error(`Failed to load GIS layers: ${res.status}`)
    const json = await res.json()

    const next = new Map()
    for (const l of (json.layers || [])) {
      const prev = this.layers.get(l.key)
      next.set(l.key, {
        ...prev,
        ...l,
        visible: prev?.visible ?? false,
        loadedOnce: prev?.loadedOnce ?? false,
        sourceId: prev?.sourceId ?? this._sourceId(l.key),
        mapLayerIds: prev?.mapLayerIds ?? this._layerIds(l.key),
      })
    }
    this.layers = next

    // re-render control if present
    this.control?.render?.()
  }

  async setVisible(key, visible) {
    const rec = this.layers.get(key)
    if (!rec) return

    rec.visible = !!visible
    this.layers.set(key, rec)

    if (rec.visible) {
      await this._ensureAddedToMap(rec)
      await this._ensureData(rec)
      this._setMapLayerVisibility(rec, true)
    } else {
      this._setMapLayerVisibility(rec, false)
    }

    this.control?.render?.()
  }

  async _ensureAddedToMap(rec) {
    const m = this.map
    const sourceId = rec.sourceId
    const [fillId, lineId, circleId] = rec.mapLayerIds

    if (!m.getSource(sourceId)) {
      m.addSource(sourceId, {
        type: "geojson",
        data: { type: "FeatureCollection", features: [] },
      })
    }

    // add style layers if missing (style changes remove them)
    if (!m.getLayer(fillId)) {
      m.addLayer({
        id: fillId,
        type: "fill",
        source: sourceId,
        filter: ["==", ["geometry-type"], "Polygon"],
        paint: {
          "fill-color": rec.color || "#3b82f6",
          "fill-opacity": 0.25,
          "fill-outline-color": rec.color || "#3b82f6",
        },
        layout: { visibility: "none" },
      })
    }

    if (!m.getLayer(lineId)) {
      m.addLayer({
        id: lineId,
        type: "line",
        source: sourceId,
        filter: ["==", ["geometry-type"], "LineString"],
        paint: {
          "line-color": rec.color || "#3b82f6",
          "line-width": 2,
        },
        layout: { visibility: "none" },
      })
    }

    if (!m.getLayer(circleId)) {
      m.addLayer({
        id: circleId,
        type: "circle",
        source: sourceId,
        filter: ["==", ["geometry-type"], "Point"],
        paint: {
          "circle-color": rec.color || "#3b82f6",
          "circle-radius": 5,
          "circle-stroke-color": "#111827",
          "circle-stroke-width": 1,
        },
        layout: { visibility: "none" },
      })
    }
  }

  async _ensureData(rec) {
    // load only once unless you want reload behavior
    if (rec.loadedOnce && !this.reloadOnMoveEnd) return
    const m = this.map
    const source = m.getSource(rec.sourceId)
    if (!source) return

    const url = new URL(`${this.baseUrl}/layers/${encodeURIComponent(rec.key)}/features`, window.location.origin)

    if (this.fetchBbox) {
      const b = m.getBounds()
      // MapLibre LngLatBounds: getWest/South/East/North
      const bbox = [b.getWest(), b.getSouth(), b.getEast(), b.getNorth()].join(",")
      url.searchParams.set("bbox", bbox)
    }

    // optional cap (backend caps anyway)
    url.searchParams.set("limit", "50000")

    const res = await fetch(url.toString(), { headers: { "Accept": "application/json" } })
    if (!res.ok) throw new Error(`Failed to load features for ${rec.key}: ${res.status}`)
    const geojson = await res.json()

    console.log(geojson)

    source.setData(geojson)
    rec.loadedOnce = true
    this.layers.set(rec.key, rec)
  }

  _setMapLayerVisibility(rec, on) {
    const m = this.map
    const vis = on ? "visible" : "none"
    for (const id of rec.mapLayerIds) {
      if (m.getLayer(id)) m.setLayoutProperty(id, "visibility", vis)
    }
  }

  _onStyleData() {
    // style reload wipes sources/layers; re-add for visible layers
    for (const rec of this.layers.values()) {
      if (rec.visible) {
        this._ensureAddedToMap(rec).then(() => {
          this._setMapLayerVisibility(rec, true)
          // re-apply data
          this._ensureData(rec).catch(console.error)
        }).catch(console.error)
      }
    }
  }

  _onMoveEnd() {
    // if reloadOnMoveEnd enabled, refetch visible layers for new bbox
    for (const rec of this.layers.values()) {
      if (rec.visible) {
        this._ensureData(rec).catch(console.error)
      }
    }
  }

  _sourceId(key) {
    return `gis-src:${key}`
  }

  _layerIds(key) {
    return [`gis-fill:${key}`, `gis-line:${key}`, `gis-circle:${key}`]
  }
}
