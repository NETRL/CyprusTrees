<template>
  <Teleport to="body" :baseZIndex="900">
    <div v-if="visible"  class="fixed inset-0 z-3000">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/50" @click="close(false)"></div>

      <!-- Modal -->
      <div
        class="absolute left-1/2 top-1/2 w-[min(980px,calc(100vw-1rem))] max-h-[calc(100vh-9rem)] -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700 flex flex-col sm:w-[min(980px,calc(100vw-2rem))] sm:max-h-[calc(100vh-9rem)]"
      >
        <!-- Header -->
        <div
          class="flex items-center justify-between gap-2 border-b border-slate-200 px-3 py-3 dark:border-slate-700 sm:gap-3 sm:px-5 sm:py-4"
        >
          <div class="min-w-0 flex-1">
            <div class="text-sm font-semibold text-slate-900 dark:text-slate-100 sm:text-base">Select location</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 hidden sm:block">
              Search an address or click on the map.
            </div>
          </div>

          <div class="flex items-center gap-1.5 sm:gap-2">
            <button
              type="button"
              class="rounded-lg px-2.5 py-1.5 text-xs ring-1 ring-slate-200 hover:bg-slate-50 dark:ring-slate-700 dark:hover:bg-slate-800 sm:rounded-xl sm:px-3 sm:py-2 sm:text-sm"
              @click="useMyLocation"
              :disabled="geoBusy"
              title="Use current location"
            >
              <span class="hidden sm:inline">{{ geoBusy ? 'Locating…' : 'My location' }}</span>
              <span class="sm:hidden">📍</span>
            </button>

            <!-- X Close Button -->
            <button
              type="button"
              class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300 sm:h-9 sm:w-9 sm:rounded-xl"
              @click="close(false)"
              title="Close"
              aria-label="Close"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-auto">
          <div class="grid grid-cols-1 gap-3 p-3 sm:gap-4 sm:p-5 lg:grid-cols-[360px_1fr]">
            <!-- Search panel -->
            <div class="space-y-3 lg:max-h-[520px] lg:overflow-auto">
              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Address</label>

                <!-- Autocomplete -->
                <div ref="dropdownWrapEl" class="relative">
                  <input
                    v-model="query"
                    type="text"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:ring-2 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 sm:rounded-xl"
                    placeholder="e.g. Strovolos, Nicosia"
                    @focus="openDropdown()"
                    @keydown.down.prevent="onArrowDown"
                    @keydown.up.prevent="onArrowUp"
                    @keydown.enter.prevent="onEnter"
                    @keydown.esc.prevent="closeDropdown"
                    inputmode="search"
                    autocomplete="off"
                    autocapitalize="off"
                    spellcheck="false"
                  />

                  <!-- Dropdown -->
                  <div
                    v-if="dropdownOpen && (searchBusy || results.length || dropdownHint || dropdownEmpty)"
                    class="absolute z-50 mt-2 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900 sm:rounded-xl"
                  >
                    <div v-if="searchBusy" class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400">
                      Searching…
                    </div>

                    <div v-else-if="dropdownHint" class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400">
                      Type at least 4 characters…
                    </div>

                    <div v-else class="max-h-[200px] overflow-auto sm:max-h-[280px]">
                      <button
                        v-for="(r, idx) in results"
                        :key="r.place_id"
                        type="button"
                        class="block w-full border-b border-slate-200 px-3 py-2 text-left text-sm hover:bg-slate-50 active:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800 dark:active:bg-slate-700 last:border-b-0"
                        :class="idx === activeIndex ? 'bg-slate-50 dark:bg-slate-800' : ''"
                        @mousedown.prevent="selectResult(r)"
                        @touchstart.prevent="selectResult(r)"
                      >
                        <div class="font-medium text-slate-900 dark:text-slate-100 line-clamp-2">
                          {{ r.display_name }}
                        </div>
                        <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                          {{ formatLatLon(r.lat, r.lon) }}
                        </div>
                      </button>

                      <div v-if="dropdownEmpty" class="px-3 py-2 text-sm text-slate-500 dark:text-slate-400">
                        No results.
                      </div>
                    </div>
                  </div>
                </div>

                <div class="text-xs text-slate-500 dark:text-slate-400">
                  Powered by OpenStreetMap Nominatim.
                </div>
              </div>

              <div
                v-if="error"
                class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-200 sm:rounded-xl"
              >
                {{ error }}
              </div>

              <!-- Selected -->
              <div class="space-y-2 pt-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                  Selected
                </div>

                <div class="rounded-lg border border-slate-200 px-3 py-3 dark:border-slate-700 sm:rounded-xl">
                  <div class="text-sm text-slate-900 dark:text-slate-100">
                    {{ selected.address || '—' }}
                  </div>
                  <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    {{ selected.lat != null ? formatLatLon(selected.lat, selected.lon) : '—' }}
                  </div>

                  <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                    <button
                      type="button"
                      class="w-full rounded-lg px-3 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto sm:rounded-xl"
                      @click="confirm"
                      :disabled="selected.lat == null || selected.lon == null || confirmBusy"
                    >
                      {{ confirmBusy ? 'Resolving…' : 'Use this location' }}
                    </button>

                    <button
                      type="button"
                      class="w-full rounded-lg px-3 py-2.5 text-sm font-medium ring-1 ring-slate-200 hover:bg-slate-50 active:bg-slate-100 dark:ring-slate-700 dark:hover:bg-slate-800 dark:active:bg-slate-700 sm:w-auto sm:rounded-xl"
                      @click="clearSelection"
                    >
                      Clear
                    </button>
                  </div>

                  <div v-if="pendingAddressHint" class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                    Address will be resolved when you confirm.
                  </div>
                </div>
              </div>
            </div>

            <!-- Map -->
            <div
              class="min-h-[280px] overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700 sm:min-h-[360px] sm:rounded-2xl lg:min-h-[520px]"
            >
              <div ref="mapEl" class="h-[280px] w-full sm:h-[360px] lg:h-[520px]"></div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div
          class="flex flex-col gap-1 border-t border-slate-200 px-3 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-5 sm:py-4"
        >
          <div>
            Tip:
            <span class="hidden sm:inline">click anywhere on the map to place the marker; drag marker to adjust.</span>
            <span class="sm:hidden">tap map to place marker; drag to adjust.</span>
          </div>
          <div class="hidden sm:block">Lat/Lon are emitted as numbers.</div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import maplibregl from 'maplibre-gl'
import 'maplibre-gl/dist/maplibre-gl.css'

const props = defineProps({
  visible: { type: Boolean, default: false },
  initialCenter: { type: Array, default: () => [33.3823, 35.1856] }, // [lon, lat]
  initialZoom: { type: Number, default: 12 },
  countryCodes: { type: String, default: 'cy,gr' },
})

const emit = defineEmits(['update:visible', 'selected', 'close'])

const visible = computed(() => props.visible)

const mapEl = ref(null)
let map = null
let marker = null

const dropdownWrapEl = ref(null)

const query = ref('')
const searchBusy = ref(false)
const geoBusy = ref(false)
const confirmBusy = ref(false)
const error = ref('')
const results = ref([])

const selected = ref({
  lat: null,
  lon: null,
  address: '',
})

// dropdown state
const dropdownOpen = ref(false)
const activeIndex = ref(-1)

// debounce + abort
const MIN_CHARS = 4
const DEBOUNCE_MS = 450
let debounceTimer = null
let searchAbort = null

const dropdownHint = computed(() => {
  const q = query.value.trim()
  return q.length > 0 && q.length < MIN_CHARS
})

const dropdownEmpty = computed(() => {
  const q = query.value.trim()
  if (q.length < MIN_CHARS) return false
  if (searchBusy.value) return false
  return dropdownOpen.value && results.value.length === 0
})

// address-resolution behavior:
const pendingAddressHint = computed(() => {
  return selected.value.lat != null && selected.value.lon != null && !selected.value.address
})

function formatLatLon(lat, lon) {
  const a = Number(lat)
  const o = Number(lon)
  if (!Number.isFinite(a) || !Number.isFinite(o)) return '—'
  return `${a.toFixed(6)}, ${o.toFixed(6)}`
}

function close(emitClose = true) {
  emit('update:visible', false)
  if (emitClose) emit('close')
}

function openDropdown() {
  dropdownOpen.value = true
}

function closeDropdown() {
  dropdownOpen.value = false
  activeIndex.value = -1
}

function clearSelection() {
  selected.value = { lat: null, lon: null, address: '' }
  if (marker) marker.remove()
  marker = null
}

function setMarker(lon, lat) {
  if (!map) return
  const lngLat = [Number(lon), Number(lat)]

  if (!marker) {
    marker = new maplibregl.Marker({ draggable: true }).setLngLat(lngLat).addTo(map)
    marker.on('dragend', () => {
      const p = marker.getLngLat()
      setSelectedCoordsOnly(p.lat, p.lng)
    })
  } else {
    marker.setLngLat(lngLat)
  }
}

function setSelectedCoordsOnly(lat, lon) {
  const a = Number(lat)
  const o = Number(lon)
  if (!Number.isFinite(a) || !Number.isFinite(o)) return

  selected.value = { lat: a, lon: o, address: '' }
  emit('selected', { ...selected.value })
}

// reverse geocode ONLY when confirming (or when "My location" used)
async function reverseSearch() {
  const a = Number(selected.value.lat)
  const o = Number(selected.value.lon)
  if (!Number.isFinite(a) || !Number.isFinite(o)) return

  const res = await fetch(`/api/geocode/reverse?lat=${a}&lon=${o}`, {
    headers: { Accept: 'application/json' },
  })
  if (!res.ok) return

  const data = await res.json()
  const addr = data?.display_name || ''

  selected.value = { lat: a, lon: o, address: addr }
  emit('selected', { ...selected.value })
}

async function search(q) {
  error.value = ''
  results.value = []
  activeIndex.value = -1

  if (q.length < MIN_CHARS) return

  if (searchAbort) searchAbort.abort()
  searchAbort = new AbortController()

  searchBusy.value = true
  try {
    const params = new URLSearchParams({ q })
    if (props.countryCodes?.trim()) params.set('countrycodes', props.countryCodes)

    const res = await fetch(`/api/geocode/search?${params.toString()}`, {
      headers: { Accept: 'application/json' },
      signal: searchAbort.signal,
    })

    if (!res.ok) throw new Error(`Search failed: ${res.status}`)
    const data = await res.json()
    results.value = Array.isArray(data) ? data : []
    activeIndex.value = results.value.length ? 0 : -1
  } catch (e) {
    if (e?.name === 'AbortError') return
    error.value = e?.message || 'Search failed.'
  } finally {
    searchBusy.value = false
  }
}

watch(query, (val) => {
  const q = val.trim()
  openDropdown()

  // abort pending request + clear debounce
  clearTimeout(debounceTimer)

  if (q.length < MIN_CHARS) {
    results.value = []
    activeIndex.value = -1
    if (searchAbort) searchAbort.abort()
    searchBusy.value = false
    return
  }

  debounceTimer = setTimeout(() => {
    search(q)
  }, DEBOUNCE_MS)
})

function onArrowDown() {
  if (!dropdownOpen.value) dropdownOpen.value = true
  if (!results.value.length) return
  activeIndex.value = Math.min(activeIndex.value + 1, results.value.length - 1)
}

function onArrowUp() {
  if (!dropdownOpen.value) dropdownOpen.value = true
  if (!results.value.length) return
  activeIndex.value = Math.max(activeIndex.value - 1, 0)
}

function onEnter() {
  if (!dropdownOpen.value) return
  if (activeIndex.value < 0 || activeIndex.value >= results.value.length) return
  selectResult(results.value[activeIndex.value])
}

function selectResult(r) {
  closeDropdown()
  clearTimeout(debounceTimer)
  if (searchAbort) searchAbort.abort()

  const lat = Number(r.lat)
  const lon = Number(r.lon)

  selected.value = {
    lat,
    lon,
    address: r.display_name || '',
  }

  // reflect selection in input (helps mobile)
  query.value = selected.value.address

  emit('selected', { ...selected.value })

  setMarker(lon, lat)
  map?.easeTo({ center: [lon, lat], zoom: Math.max(map.getZoom(), 15) })
}

async function useMyLocation() {
  error.value = ''
  geoBusy.value = true
  try {
    const pos = await new Promise((resolve, reject) => {
      if (!navigator.geolocation) return reject(new Error('Geolocation not supported'))
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
      })
    })

    const lat = pos.coords.latitude
    const lon = pos.coords.longitude

    setMarker(lon, lat)
    map?.easeTo({ center: [lon, lat], zoom: 16 })

    // set coords first, resolve address second (and ONLY once)
    selected.value = { lat: Number(lat), lon: Number(lon), address: '' }
    emit('selected', { ...selected.value })

    await reverseSearch()
  } catch (e) {
    error.value = e?.message || 'Failed to fetch current location.'
  } finally {
    geoBusy.value = false
  }
}

async function confirm() {
  if (selected.value.lat == null || selected.value.lon == null) return

  confirmBusy.value = true
  try {
    // Only reverse-geocode if address missing
    if (!selected.value.address) {
      await reverseSearch()
    }
    emit('selected', { ...selected.value }) // final emit
    close(true)
  } finally {
    confirmBusy.value = false
  }
}

const OSM_RASTER_STYLE = {
  version: 8,
  sources: {
    osm: {
      type: 'raster',
      tiles: [
        'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'https://c.tile.openstreetmap.org/{z}/{x}/{y}.png',
      ],
      tileSize: 256,
      attribution: '© OpenStreetMap contributors',
    },
  },
  layers: [{ id: 'osm', type: 'raster', source: 'osm' }],
}

function initMap() {
  if (map || !mapEl.value) return

  map = new maplibregl.Map({
    container: mapEl.value,
    style: OSM_RASTER_STYLE,
    center: props.initialCenter,
    zoom: props.initialZoom,
    attributionControl: true,
  })

  map.addControl(new maplibregl.NavigationControl({ showCompass: true }), 'top-right')

  //no reverse-geocode here (prevents Nominatim blocks)
  map.on('click', (e) => {
    const { lng, lat } = e.lngLat
    setMarker(lng, lat)
    setSelectedCoordsOnly(lat, lng)
  })
}

function destroyMap() {
  if (marker) {
    marker.remove()
    marker = null
  }
  if (map) {
    map.remove()
    map = null
  }
}

// close dropdown when clicking outside it
function onGlobalPointerDown(e) {
  if (!dropdownOpen.value) return
  const el = dropdownWrapEl.value
  if (!el) return
  if (el.contains(e.target)) return
  closeDropdown()
}

watch(
  () => props.visible,
  async (v) => {
    if (v) {
      await nextTick()
      initMap()

      document.addEventListener('pointerdown', onGlobalPointerDown, { capture: true })

      // restore marker if selected
      if (selected.value.lat != null && selected.value.lon != null) {
        setMarker(selected.value.lon, selected.value.lat)
        map?.jumpTo({
          center: [selected.value.lon, selected.value.lat],
          zoom: Math.max(props.initialZoom, 15),
        })
      }
    } else {
      document.removeEventListener('pointerdown', onGlobalPointerDown, { capture: true })

      destroyMap()

      // cancel search timers / requests
      clearTimeout(debounceTimer)
      if (searchAbort) searchAbort.abort()

      results.value = []
      error.value = ''
      searchBusy.value = false
      geoBusy.value = false
      confirmBusy.value = false
      closeDropdown()
    }
  },
  { immediate: true }
)

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', onGlobalPointerDown, { capture: true })
  clearTimeout(debounceTimer)
  if (searchAbort) searchAbort.abort()
  destroyMap()
})
</script>
