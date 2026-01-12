import maplibregl from 'maplibre-gl'

export function storeNewTree(mapInstance, { onLatLng, delay = 500 } = {}) {
  let longPressTimeout = null
  let isLongPress = false

  let pinMarker = null

  function ensurePin(map) {
    if (pinMarker) return pinMarker
    pinMarker = new maplibregl.Marker({ color: '#e11d48' })
      .setLngLat([0, 0])
      .addTo(map)

    pinMarker.getElement().style.display = 'none'
    return pinMarker
  }

  function showPinAt(map, lngLat) {
    const marker = ensurePin(map)
    marker.setLngLat([lngLat.lng, lngLat.lat])
    marker.getElement().style.display = ''
  }

  function hidePin() {
    if (!pinMarker) return
    pinMarker.getElement().style.display = 'none'
  }

  function removePin() {
    if (!pinMarker) return
    pinMarker.remove()
    pinMarker = null
  }

  const start = (e) => {
    isLongPress = false
    clearTimeout(longPressTimeout)

    longPressTimeout = setTimeout(() => {
      isLongPress = true

      showPinAt(mapInstance, e.lngLat)

      // return the lngLat via callback
      onLatLng?.(e.lngLat)
    }, delay)
  }

  const cancel = () => clearTimeout(longPressTimeout)

  mapInstance.on('mousedown', start)
  mapInstance.on('mouseup', cancel)
  mapInstance.on('mousemove', cancel)

  mapInstance.on('touchstart', start)
  mapInstance.on('touchend', cancel)
  mapInstance.on('touchmove', cancel)

  const cleanup = () => {
    clearTimeout(longPressTimeout)

    mapInstance.off('mousedown', start)
    mapInstance.off('mouseup', cancel)
    mapInstance.off('mousemove', cancel)

    mapInstance.off('touchstart', start)
    mapInstance.off('touchend', cancel)
    mapInstance.off('touchmove', cancel)

    // optional: removePin()
  }

  // return controls
  return {
    cleanup,
    show: (lngLat) => showPinAt(mapInstance, lngLat),
    hide: hidePin,
    remove: removePin,
  }
}
