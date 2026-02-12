import { onBeforeUnmount, ref, watch } from 'vue'

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
    recenterZoom: null,

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
  let styleLoadBound = false

  // Keep FC as the source of truth (no private src._data reads).
  const fcRef = ref(makeInitialFC())

  function getMap() {
    return mapRef?.value ?? mapRef
  }

  function isStyleReady(map) {
    // MapLibre has isStyleLoaded(); if not, be conservative.
    return !!map && (typeof map.isStyleLoaded !== 'function' || map.isStyleLoaded())
  }

  function safeEnsureSourceAndLayers() {
    const map = getMap()
    if (!map) return
    if (!isStyleReady(map)) return

    // Source
    if (!map.getSource(options.sourceId)) {
      map.addSource(options.sourceId, {
        type: 'geojson',
        data: fcRef.value,
      })
    } else {
      // Make sure the map's source has the latest FC after style swaps.
      map.getSource(options.sourceId).setData(fcRef.value)
    }

    if (!options.autoAddLayers) return

    // Accuracy circle
    if (!map.getLayer(options.accuracyLayerId)) {
      map.addLayer({
        id: options.accuracyLayerId,
        type: 'circle',
        source: options.sourceId,
        filter: ['==', ['get', 'kind'], options.posKind],
        paint: {
          'circle-opacity': options.accuracyOpacity,
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

    // Dot
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

    // Heading cone
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

  function syncSourceData() {
    const map = getMap()
    const src = map?.getSource?.(options.sourceId)
    if (!src) return
    src.setData(fcRef.value)
  }

  function makeInitialFC() {
    return {
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
    }
  }

  function updateUserPosition([lng, lat], accuracyM = 0) {
    // Update our FC
    const fc = fcRef.value

    const posFeature = fc.features.find((f) => f?.properties?.kind === options.posKind)
    if (posFeature) {
      posFeature.geometry.coordinates = [lng, lat]
      posFeature.properties.acc = accuracyM
    }

    const coneFeature = fc.features.find((f) => f?.properties?.kind === options.coneKind)
    if (coneFeature) {
      // Keep cone anchored at user position even before heading exists
      const ring = coneFeature.geometry.coordinates[0]
      for (const p of ring) {
        p[0] = lng
        p[1] = lat
      }
    }

    hasFix.value = true
    lastLngLat.value = [lng, lat]
    lastAccuracyM.value = accuracyM

    // Push to map source if present
    syncSourceData()

    if (options.recenterOnFirstFix && !recentered) {
      recenterOnce()
    }
  }

  function updateHeading(deg) {
    if (!lastLngLat.value) return // no fix -> nowhere to draw the cone

    if (options.smoothHeading) {
      if (currentHeadingSmoothed == null) currentHeadingSmoothed = deg
      else {
        const a = options.headingSmoothing
        currentHeadingSmoothed = currentHeadingSmoothed * (1 - a) + deg * a
      }
      deg = currentHeadingSmoothed
    }

    const fc = fcRef.value
    const pos = fc.features.find((f) => f?.properties?.kind === options.posKind)?.geometry?.coordinates
    if (!pos) return

    const cone = fc.features.find((f) => f?.properties?.kind === options.coneKind)
    if (!cone) return

    const polyRing = makeConePolygon(pos, deg, options.coneLengthM, options.coneHalfAngleDeg)
    cone.geometry.coordinates = [polyRing]

    lastHeading.value = deg
    syncSourceData()
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

  function bindStyleLoad() {
    const map = getMap()
    if (!map?.on || styleLoadBound) return

    // On style reload: re-add source/layers and re-push latest known data.
    map.on('style.load', onStyleLoad)
    styleLoadBound = true
  }

  function unbindStyleLoad() {
    const map = getMap()
    if (!map?.off || !styleLoadBound) return
    map.off('style.load', onStyleLoad)
    styleLoadBound = false
  }

  function onStyleLoad() {
    // If tracking is active, restore visuals immediately
    safeEnsureSourceAndLayers()
    syncSourceData()
  }

  /**
   * Call start() from user gesture.
   * Works even if map becomes available shortly after, because we also watch mapRef.
   */
  async function start() {
    error.value = null

    const map = getMap()
    if (!map) throw new Error('Map ref is not ready')

    // Ensure style exists before adding sources/layers.
    // If style isn't ready yet, the watcher below will handle it.
    safeEnsureSourceAndLayers()

    bindStyleLoad()

    startGeolocation()
    await startOrientation()

    isActive.value = true
  }

  function stop() {
    unbindStyleLoad()

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

  // If map instance changes (initial null -> real map, or re-init), rebind and rehydrate.
  watch(
    () => (mapRef?.value ?? mapRef),
    (m, prev) => {
      if (prev?.off && styleLoadBound) prev.off('style.load', onStyleLoad)
      styleLoadBound = false

      if (!m) return

      // If we are currently active, ensure we restore visuals on the new map.
      if (isActive.value) {
        safeEnsureSourceAndLayers()
        bindStyleLoad()
        syncSourceData()
      }
    },
    { immediate: true }
  )

  onBeforeUnmount(() => stop())

  return {
    isActive,
    hasFix,
    lastLngLat,
    lastAccuracyM,
    lastHeading,
    error,

    start,
    stop,
    recenterOnce,

    // optional
    ensureSourceAndLayers: safeEnsureSourceAndLayers,
    updateUserPosition,
    updateHeading,
  }
}
