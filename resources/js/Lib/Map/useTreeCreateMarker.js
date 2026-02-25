import { ref, computed, watch, onBeforeUnmount } from "vue"
import { attachLongPressPin } from "@/Lib/Map/useMapLongPressPin"
import { MAP_PANELS, useMapUiState } from "./useMapUiState"

export function useTreeCreateMarker(mapRef, { onClearSelection } = {}) {
  const markerLatLng = ref(null)
  const showAuthPrompt = ref(false)
  const pinClickFlag = ref(0)

  const { ui } = useMapUiState()

  const isInteractionEnabled = computed(() => markerLatLng.value == null
    // && ui.activePanel !== MAP_PANELS.EVENTS
  )
  //TODO 
  // watch(() => ui.activePanel, v => { console.log('active: ', v); console.log(isInteractionEnabled.value)})
  let longPressPin = null

  function attach() {
    const m = mapRef?.value
    if (!m) return

    longPressPin = attachLongPressPin(m, {
      onLatLng: (latLng) => {
        markerLatLng.value = latLng
      },
      requiresAuth: (v) => {
        showAuthPrompt.value = v
      },
      onPinClick: () => {
        pinClickFlag.value++
      },
    })
  }

  function cancelCreate() {
    onClearSelection?.()
    markerLatLng.value = null
    longPressPin?.hide?.()
  }

  function createSuccessCleanup() {
    markerLatLng.value = null
    longPressPin?.remove?.()
  }

  // When user is in "create marker" mode, clear map selection + reset cursor.
  watch(
    markerLatLng,
    (v) => {
      if (v == null) return

      onClearSelection?.()

      const m = mapRef?.value
      const c = m?.getCanvas?.()
      if (c) c.style.cursor = ""
    },
    { flush: "post" }
  )

  onBeforeUnmount(() => {
    longPressPin?.cleanup?.()
    longPressPin?.remove?.()
    longPressPin = null
  })

  return {
    markerLatLng,
    showAuthPrompt,
    pinClickFlag,
    isInteractionEnabled,

    attach,
    cancelCreate,
    createSuccessCleanup,

    // if you still need it:
    _getLongPressController: () => longPressPin,
  }
}
