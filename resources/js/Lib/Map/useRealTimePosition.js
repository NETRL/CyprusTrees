import { onBeforeUnmount, ref } from 'vue'

export function useRealTimePosition(mapRef, opts = {}) {
  const options = {
    sourceId: 'user',
    posKind: 'pos',
    coneKind: 'cone',
    accuracyLayerId: 'user-accuracy',
    dotLayerId: 'user-dot',
    coneLayerId: 'user-cone',

    // visuals
    dotRadius: 6,
    dotStrokeWidth: 2,
    accuracyOpacity: 0.12,
    coneOpacity: 0.18,

    // heading cone geometry
    coneLengthM: 25,
    coneHalfAngleDeg: 18,

    // behavior
    enableHighAccuracy: true,
    maximumAge: 1000,
    timeout: 10000,
    smoothHeading: true,
    headingSmoothing: 0.15, // 0..1 (higher = more responsive)
    autoAddLayers: true,
    recenterOnFirstFix: true,
    recenterZoom: null, // keep current zoom if null

    ...opts,
  }

  const isActive = ref(false)
  const hasFix = ref(false)
  const lastLngLat = ref(null)
  const lastAccuracyM = ref(null)
  const lastHeading = ref(null)
  const error = ref(null)

  let watchId = null
  let orientationListening = false
  let recentered = false
  let currentHeadingSmoothed = null

  function getMap() {
    return mapRef?.value ?? mapRef
  }

  function ensureSourceAndLayers() {
    const map = getMap()
    if (!map) return

    // If style reloads, sources/layers are lost. This must be re-run on 'style.load'.
    if (!map.getSource(options.sourceId)) {
      map.addSource(options.sourceId, {
        type: 'geojson',
        data: {
          type: 'FeatureCollection',
          features: [
            {
              type: 'Feature',
              properties: { kind: options.posKind, acc: 0 },
              geometry: { type: 'Point', coordinates: [0, 0] },
            },
            {
              type: 'Feature',
              properties: { kind: options.coneKind },
              geometry: { type: 'Polygon', coordinates: [[[0, 0], [0, 0], [0, 0], [0, 0]]] },
            },
          ],
        },
      })
    }

    if (!options.autoAddLayers) return

    if (!map.getLayer(options.accuracyLayerId)) {
      map.addLayer({
        id: options.accuracyLayerId,
        type: 'circle',
        source: options.sourceId,
        filter: ['==', ['get', 'kind'], options.posKind],
        paint: {
          'circle-opacity': options.accuracyOpacity,
          // simple zoom-aware visualization of accuracy (not meter-perfect)
          'circle-radius': [
            'interpolate',
            ['linear'],
            ['zoom'],
            0,
            2,
            18,
            ['/', ['get', 'acc'], 1.5],
          ],
        },
      })
    }

    if (!map.getLayer(options.dotLayerId)) {
      map.addLayer({
        id: options.dotLayerId,
        type: 'circle',
        source: options.sourceId,
        filter: ['==', ['get', 'kind'], options.posKind],
        paint: {
          'circle-radius': options.dotRadius,
          'circle-stroke-width': options.dotStrokeWidth,
          'circle-stroke-color': '#ffffff',
        },
      })
    }

    if (!map.getLayer(options.coneLayerId)) {
      map.addLayer({
        id: options.coneLayerId,
        type: 'fill',
        source: options.sourceId,
        filter: ['==', ['get', 'kind'], options.coneKind],
        paint: { 'fill-opacity': options.coneOpacity },
      })
    }
  }

  function getSourceData() {
    const map = getMap()
    const src = map?.getSource(options.sourceId)
    if (!src) return null

    // MapLibre stores the geojson on a private field; different versions differ slightly.
    // We keep it robust by reading from known fields and falling back safely.
    return src._data ?? src._options?.data ?? null
  }

  function setSourceData(fc) {
    const map = getMap()
    const src = map?.getSource(options.sourceId)
    if (!src) return
    src.setData(fc)
  }

  function cloneGeoJSON(data) {
    // Use JSON parse/stringify instead of structuredClone to avoid proxy cloning issues
    return JSON.parse(JSON.stringify(data))
  }

  function updateUserPosition([lng, lat], accuracyM = 0) {
    const map = getMap()
    if (!map?.getSource(options.sourceId)) return

    const data = getSourceData()
    if (!data) return

    const fc = cloneGeoJSON(data)

    const posFeature = fc.features.find((f) => f?.properties?.kind === options.posKind)
    if (posFeature) {
      posFeature.geometry.coordinates = [lng, lat]
      posFeature.properties.acc = accuracyM
    }

    const coneFeature = fc.features.find((f) => f?.properties?.kind === options.coneKind)
    if (coneFeature) {
      // keep cone anchored even before heading exists
      const ring = coneFeature.geometry.coordinates[0]
      for (const p of ring) {
        p[0] = lng
        p[1] = lat
      }
    }

    setSourceData(fc)

    hasFix.value = true
    lastLngLat.value = [lng, lat]
    lastAccuracyM.value = accuracyM

    if (options.recenterOnFirstFix && !recentered) {
      recenterOnce()
    }
  }

  function updateHeading(deg) {
    const map = getMap()
    if (!map?.getSource(options.sourceId)) return

    if (options.smoothHeading) {
      if (currentHeadingSmoothed == null) currentHeadingSmoothed = deg
      else {
        const a = options.headingSmoothing
        currentHeadingSmoothed = currentHeadingSmoothed * (1 - a) + deg * a
      }
      deg = currentHeadingSmoothed
    }

    const data = getSourceData()
    if (!data) return

    const fc = cloneGeoJSON(data)
    const pos = fc.features.find((f) => f?.properties?.kind === options.posKind)?.geometry?.coordinates
    if (!pos) return

    const cone = fc.features.find((f) => f?.properties?.kind === options.coneKind)
    if (!cone) return

    const polyRing = makeConePolygon(pos, deg, options.coneLengthM, options.coneHalfAngleDeg)
    cone.geometry.coordinates = [polyRing]

    setSourceData(fc)

    lastHeading.value = deg
  }

  function makeConePolygon([lng, lat], headingDeg, lengthM, halfAngleDeg) {
    const left = destination([lng, lat], lengthM, headingDeg - halfAngleDeg)
    const right = destination([lng, lat], lengthM, headingDeg + halfAngleDeg)
    return [
      [lng, lat],
      left,
      right,
      [lng, lat],
    ]
  }

  function destination([lng, lat], distM, bearingDeg) {
    const R = 6378137
    const brng = (bearingDeg * Math.PI) / 180
    const lat1 = (lat * Math.PI) / 180
    const lon1 = (lng * Math.PI) / 180
    const d = distM / R

    const lat2 = Math.asin(
      Math.sin(lat1) * Math.cos(d) + Math.cos(lat1) * Math.sin(d) * Math.cos(brng)
    )
    const lon2 =
      lon1 +
      Math.atan2(
        Math.sin(brng) * Math.sin(d) * Math.cos(lat1),
        Math.cos(d) - Math.sin(lat1) * Math.sin(lat2)
      )

    return [(lon2 * 180) / Math.PI, (lat2 * 180) / Math.PI]
  }

  function handleOrientation(e) {
    // iOS: webkitCompassHeading is most reliable when present.
    const heading =
      typeof e.webkitCompassHeading === 'number'
        ? e.webkitCompassHeading
        : typeof e.alpha === 'number'
          ? (360 - e.alpha)
          : null

    if (heading == null || Number.isNaN(heading)) return
    updateHeading(heading)
  }

  async function startOrientation() {
    if (orientationListening) return

    // iOS permission gate
    if (
      typeof DeviceOrientationEvent !== 'undefined' &&
      typeof DeviceOrientationEvent.requestPermission === 'function'
    ) {
      const res = await DeviceOrientationEvent.requestPermission()
      if (res !== 'granted') throw new Error('Orientation permission denied')
    }

    window.addEventListener('deviceorientation', handleOrientation, true)
    orientationListening = true
  }

  function stopOrientation() {
    if (!orientationListening) return
    window.removeEventListener('deviceorientation', handleOrientation, true)
    orientationListening = false
  }

  function startGeolocation() {
    if (!navigator.geolocation) throw new Error('Geolocation not supported')
    if (watchId != null) return

    watchId = navigator.geolocation.watchPosition(
      (pos) => {
        const lng = pos.coords.longitude
        const lat = pos.coords.latitude
        const acc = pos.coords.accuracy ?? 0
        updateUserPosition([lng, lat], acc)

        // course heading when moving can be better than compass (when available)
        const course = pos.coords.heading
        if (typeof course === 'number' && !Number.isNaN(course)) {
          updateHeading(course)
        }
      },
      (err) => {
        error.value = err
        console.warn('geolocation error', err)
      },
      {
        enableHighAccuracy: options.enableHighAccuracy,
        maximumAge: options.maximumAge,
        timeout: options.timeout,
      }
    )
  }

  function stopGeolocation() {
    if (watchId == null) return
    navigator.geolocation.clearWatch(watchId)
    watchId = null
  }

  /**
   * Call start() from a user gesture (button tap).
   * call it after map is ready.
   */
  async function start() {
    error.value = null

    const map = getMap()
    if (!map) throw new Error('Map ref is not ready')

    ensureSourceAndLayers()

    // Re-add user layers after style changes (switching MapTiler style etc.)
    // Avoid stacking handlers by removing before adding.
    map.off?.('style.load', ensureSourceAndLayers)
    map.on?.('style.load', ensureSourceAndLayers)

    startGeolocation()
    await startOrientation()

    isActive.value = true
  }

  function stop() {
    const map = getMap()
    if (map?.off) map.off('style.load', ensureSourceAndLayers)

    stopGeolocation()
    stopOrientation()

    isActive.value = false
  }

  function recenterOnce() {
    const map = getMap()
    if (!map || !lastLngLat.value) return

    const zoom = options.recenterZoom ?? map.getZoom?.()
    map.easeTo?.({ center: lastLngLat.value, zoom })
    recentered = true
  }

  onBeforeUnmount(() => {
    stop()
  })

  return {
    isActive,
    hasFix,
    lastLngLat,
    lastAccuracyM,
    lastHeading,
    error,

    // controls
    start,
    stop,
    recenterOnce,

    // low-level (optional)
    ensureSourceAndLayers,
    updateUserPosition,
    updateHeading,
  }
}
