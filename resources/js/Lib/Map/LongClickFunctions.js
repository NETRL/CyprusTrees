import { useAuth } from '@/Composables/useAuth'
import maplibregl from 'maplibre-gl'

export function storeNewTree(mapInstance, { onLatLng, requiresAuth, onPinClick, delay = 500, moveTolerancePx = 8 } = {}) {
  let longPressTimeout = null
  let pinMarker = null

  let startPoint = null
  let startLngLat = null
  let pointerDown = false

  const { _, isAuthenticated } = useAuth();

  function ensurePin(map) {
    if (pinMarker) return pinMarker
    pinMarker = new maplibregl.Marker({ color: '#e11d48' })
      .setLngLat([0, 0])
      .addTo(map)
    pinMarker.getElement().style.display = 'none'

    const el = pinMarker.getElement()
    el.style.display = 'none'

    el.addEventListener('click', (e) => {
      e.stopPropagation() // to stop map click handlers
      onPinClick?.(true)
    })

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

  const cancel = () => {
    clearTimeout(longPressTimeout)
    longPressTimeout = null
    pointerDown = false
    startPoint = null
    startLngLat = null
  }

  const start = (e) => {
    // Only primary mouse button (avoid right-click etc.)
    const oe = e.originalEvent
    if (oe && oe.type?.startsWith('mouse') && oe.button !== 0) return

    cancel() // clear any previous timers
    pointerDown = true

    startPoint = e.point // pixel coords
    startLngLat = e.lngLat

    longPressTimeout = setTimeout(() => {
      // If map started moving/dragging, do nothing
      if (!pointerDown) return
      if (mapInstance.isMoving && mapInstance.isMoving()) return
      // Some builds have isDragging(); if present, use it
      if (mapInstance.isDragging && mapInstance.isDragging()) return

      if (isAuthenticated.value) {
        showPinAt(mapInstance, startLngLat)
        onLatLng?.(startLngLat)
      } else {
        requiresAuth?.(true)
      }

      // end this gesture
      pointerDown = false
    }, delay)
  }

  const onMove = (e) => {
    if (!pointerDown || !startPoint) return

    // If the pointer moved enough, it’s a pan → cancel long-press
    const dx = e.point.x - startPoint.x
    const dy = e.point.y - startPoint.y
    if ((dx * dx + dy * dy) >= (moveTolerancePx * moveTolerancePx)) {
      cancel()
    }
  }

  // Pointer (mouse)
  mapInstance.on('mousedown', start)
  mapInstance.on('mousemove', onMove)
  mapInstance.on('mouseup', cancel)
  mapInstance.on('mouseleave', cancel)

  // Touch
  mapInstance.on('touchstart', start)
  mapInstance.on('touchmove', onMove)
  mapInstance.on('touchend', cancel)
  mapInstance.on('touchcancel', cancel)

  // Critical: cancel when MapLibre begins map gestures (pan/zoom/rotate)
  mapInstance.on('dragstart', cancel)
  mapInstance.on('movestart', cancel)
  mapInstance.on('zoomstart', cancel)
  mapInstance.on('rotatestart', cancel)
  mapInstance.on('pitchstart', cancel)

  const cleanup = () => {
    cancel()

    mapInstance.off('mousedown', start)
    mapInstance.off('mousemove', onMove)
    mapInstance.off('mouseup', cancel)
    mapInstance.off('mouseleave', cancel)

    mapInstance.off('touchstart', start)
    mapInstance.off('touchmove', onMove)
    mapInstance.off('touchend', cancel)
    mapInstance.off('touchcancel', cancel)

    mapInstance.off('dragstart', cancel)
    mapInstance.off('movestart', cancel)
    mapInstance.off('zoomstart', cancel)
    mapInstance.off('rotatestart', cancel)
    mapInstance.off('pitchstart', cancel)
  }

  return {
    cleanup,
    show: (lngLat) => showPinAt(mapInstance, lngLat),
    requiresAuth: (value = true) => requiresAuth?.(value),
    hide: hidePin,
    remove: removePin,
    triggerPinClick: (value = true) => onPinClick?.(value),
  }
}
