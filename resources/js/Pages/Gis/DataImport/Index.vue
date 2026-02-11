<template>
  <div class="p-6 space-y-4 mx-auto">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold tracking-tight text-gray-900">GIS Layer Management</h1>
    </div>

    <transition enter-active-class="transition ease-out duration-300"
      enter-from-class="transform opacity-0 -translate-y-2" enter-to-class="transform opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="flash" class="rounded-lg border px-4 py-3 text-sm font-medium shadow-sm flex items-center gap-2"
        :class="flash.type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800'">
        <span v-if="flash.type === 'success'">✓</span>
        <span v-else>!</span>
        {{ flash.message }}
      </div>
    </transition>

    <div class="grid grid-cols-12 gap-6">

      <div class="col-span-12 lg:col-span-4 space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
          <div class="bg-gray-50/50 px-4 py-3 border-b border-gray-200">
            <h2 class="font-semibold text-gray-700">Available Layers</h2>
          </div>
          <div class="divide-y divide-gray-100 max-h-[calc(100vh-200px)] overflow-y-auto">
            <button v-for="l in layers" :key="l.id"
              class="w-full text-left px-4 py-3 transition-colors duration-150 hover:bg-gray-50 focus:outline-none focus:bg-gray-50"
              :class="Number(selectedLayerId) === l.id ? 'bg-blue-50/60 border-l-4 border-l-blue-500 pl-3' : 'border-l-4 border-l-transparent'"
              @click="onSelectLayer(l.id)">

              <div class="flex items-center justify-between gap-2">
                <div class="min-w-0 flex items-center gap-2">


                  <div class="font-medium truncate">{{ l.display_name }}</div>
                </div>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] border"
                  :class="l.is_active ? 'border-green-200 bg-green-50 text-green-700' : 'border-gray-200 bg-gray-50 text-gray-600'">
                  {{ l.is_active ? 'active' : 'inactive' }}
                </span>
              </div>
            </button>
          </div>
        </div>
      </div>

      <div class="col-span-12 lg:col-span-8">

        <div v-if="!selectedLayer"
          class="h-64 flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 text-gray-500">
          <svg class="w-10 h-10 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0121 18.382V7.618a1 1 0 01-.447-.894L15 7m0 13V7m0 0L9.553 2.553a1 1 0 00-.894 0L3 5.382">
            </path>
          </svg>
          <span class="font-medium">Select a layer to manage data</span>
        </div>

        <div v-else class="space-y-6">

          <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
              <div>
                <h2 class="text-xl font-bold text-gray-900">{{ selectedLayer.display_name }}</h2>
                <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                  <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs">key: {{ selectedLayer.key }}</span>
                  <span>•</span>
                  <span>Default mode: <strong class="text-gray-700">{{ selectedLayer.default_import_mode
                      }}</strong></span>
                </div>
              </div>

              <!-- <div class="text-right">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Active Revision</div>
                <div class="font-medium text-gray-900 mt-0.5 flex items-center justify-end gap-2">
                  <span v-if="selectedLayer.active_revision"
                    class="flex items-center gap-1.5 text-green-700 bg-green-50 px-2 py-1 rounded-md text-sm border border-green-100">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                      <path d="M5 13l4 4L19 7" />
                    </svg>
                    {{ selectedLayer.active_revision.label }}
                  </span>
                  <span v-else class="text-gray-400 italic">No active data</span>
                </div>
              </div> -->
              <div class="text-xs text-gray-500">
                Active: <span class="text-gray-800 font-medium">{{ selectedLayer.active_revision?.label || '—' }}</span>
              </div>
            </div>
          </div>

          <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="bg-gray-50/50 px-5 py-3 border-b border-gray-200 flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <h3 class="text-sm font-semibold text-gray-700">Import New Revision</h3>
            </div>

            <div class="p-5">
              <div class="grid grid-cols-12 gap-4 items-end">
                <div class="col-span-12 md:col-span-4">
                  <label class="block text-xs font-medium text-gray-700 mb-1">Label (Optional)</label>
                  <input v-model="upload.label" type="text"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-2"
                    placeholder="e.g. Q1 2026 Update" />
                </div>

                <div class="col-span-12 md:col-span-3">
                  <label class="block text-xs font-medium text-gray-700 mb-1">Import Mode</label>
                  <select v-model="upload.import_mode"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-2">
                    <option value="replace">Replace (New Baseline)</option>
                    <option value="append">Append (Additive)</option>
                  </select>
                </div>

                <div class="col-span-12 md:col-span-5 flex gap-3">
                  <div class="flex-grow">
                    <label class="block text-xs font-medium text-gray-700 mb-1">GeoJSON File</label>
                    <input type="file" ref="fileInput" accept=".geojson,.json" @change="onFileChange"
                      class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" />
                  </div>
                  <button
                    class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all h-[38px] self-end"
                    :disabled="busy || !upload.file" @click="submitUpload">
                    <svg v-if="busy" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                      </path>
                    </svg>
                    {{ busy ? 'Uploading...' : 'Import' }}
                  </button>
                </div>
              </div>
              <p class="mt-2 text-xs text-gray-500">
                <span class="font-medium text-gray-700">Replace:</span> Replaces all data for this layer.
                <span class="font-medium text-gray-700 ml-2">Append:</span> Adds features to existing data.
              </p>
            </div>
          </div>

          <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="bg-gray-50/50 px-5 py-3 border-b border-gray-200 flex justify-between items-center">
              <h3 class="text-sm font-semibold text-gray-700">Revision History</h3>
              <div v-if="isPolling" class="flex items-center gap-2 text-xs text-blue-600 animate-pulse">
                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                Syncing status...
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold tracking-wider border-b">
                  <tr>
                    <th class="px-5 py-3">Label / File</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-center">Active</th>
                    <th class="px-5 py-3">Mode</th>
                    <th class="px-5 py-3 text-right">Features</th>
                    <th class="px-5 py-3 text-right">Date</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="r in revisions" :key="r.id" class="group hover:bg-gray-50/80 transition-colors">

                    <td class="px-5 py-3 max-w-[250px]">
                      <div class="font-medium text-gray-900 truncate" :title="r.label">
                        {{ r.label || ('Revision #' + r.revision_no) }}
                      </div>
                      <div class="text-xs text-gray-500 truncate" :title="r.original_name">
                        {{ r.original_name || '—' }}
                      </div>
                      <div v-if="r.error" class="text-xs text-red-600 mt-1 font-medium bg-red-50 p-1 rounded">
                        Error: {{ r.error }}
                      </div>
                    </td>

                    <td class="px-5 py-3 whitespace-nowrap">
                      <span :class="statusBadgeClass(r.status)"
                        class="px-2.5 py-0.5 rounded-full text-xs font-medium border inline-flex items-center gap-1.5 capitalize">
                        <span v-if="r.status === 'processing' || r.status === 'queued'"
                          class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                        {{ r.status }}
                      </span>
                    </td>

                    <td class="px-5 py-3 text-center">
                      <input type="checkbox"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer disabled:opacity-30"
                        :checked="!!r.is_included" :disabled="r.status !== 'completed'"
                        @change="setIncluded(r, $event.target.checked)" title="Include in Map" />
                    </td>

                    <td class="px-5 py-3 whitespace-nowrap text-gray-500 text-xs uppercase tracking-wide">
                      {{ r.import_mode }}
                    </td>

                    <td class="px-5 py-3 whitespace-nowrap text-right font-mono text-gray-600">
                      {{ r.features_imported?.toLocaleString() ?? '—' }}
                    </td>

                    <td class="px-5 py-3 whitespace-nowrap text-right text-gray-500 text-xs">
                      {{ new Date(r.created_at).toLocaleDateString() }}<br>
                      {{ new Date(r.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                    </td>

                    <td class="px-5 py-3 whitespace-nowrap text-right">
                      <div class="inline-flex items-center gap-1">

                        <button v-if="r.import_mode === 'replace'"
                          class="p-1.5 rounded text-gray-500 hover:text-green-600 hover:bg-green-50 transition-colors disabled:opacity-30"
                          :disabled="r.status !== 'completed'" @click="activateReplace(r)" title="Activate as Baseline">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                        </button>

                        <button
                          class="p-1.5 rounded text-gray-500 hover:text-orange-600 hover:bg-orange-50 transition-colors disabled:opacity-30"
                          :disabled="r.status === 'archived'" @click="archiveRevision(r)" title="Archive">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                          </svg>
                        </button>

                        <button
                          class="p-1.5 rounded text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors disabled:opacity-30"
                          :disabled="r.is_included || (selectedLayer.active_revision_id === r.id)"
                          @click="purgeRevision(r)" title="Purge Permanently">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="!revisions?.length">
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500 bg-gray-50/30">
                      No revisions found for this layer.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
  'layout': AuthenticatedLayout,
})

const props = defineProps({
  layers: Array,
  selectedLayerId: [Number, null],
  selectedLayer: Object,
  revisions: Array,
})

const page = usePage()
const flash = computed(() => page.props?.flash?.message)
const fileInput = ref(null)

// --- Upload State ---
const upload = ref({
  label: '',
  import_mode: 'replace',
  file: null,
})

const busy = ref(false)

// Reset upload form defaults when layer changes
watch(() => props.selectedLayer, (newLayer) => {
  if (newLayer) {
    upload.value.import_mode = newLayer.default_import_mode || 'replace'
    upload.value.label = ''
    upload.value.file = null
    if (fileInput.value) fileInput.value.value = '' // Clear native input
  }
}, { immediate: true })


// --- Polling Logic ---
const isPolling = ref(false)
let pollTimer = null

const startPolling = () => {
  if (pollTimer) return
  isPolling.value = true

  pollTimer = setInterval(() => {
    router.reload({
      only: ['revisions', 'selectedLayer'], // Only fetch revisions and layer status
      preserveScroll: true,
      preserveState: true,
      onFinish: () => {
        // Stop polling if we navigated away or condition met (handled by watcher)
      }
    })
  }, 3000)
}

const stopPolling = () => {
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
  isPolling.value = false
}

// Watch revisions for processing/queued status
watch(() => props.revisions, (newRevisions) => {
  const hasActiveJobs = newRevisions?.some(r => ['queued', 'processing'].includes(r.status))

  if (hasActiveJobs) {
    startPolling()
  } else {
    stopPolling()
  }
}, { immediate: true, deep: true })

onUnmounted(() => {
  stopPolling()
})


// --- Actions ---

const onSelectLayer = (layerId) => {
  router.get(route('gisLayers.data.index'), { layer_id: layerId }, { preserveState: true })
}

const onFileChange = (e) => {
  upload.value.file = e.target.files?.[0] || null
}

const submitUpload = () => {
  if (!props.selectedLayer?.id) return
  if (!upload.value.file) return

  busy.value = true
  const form = new FormData()
  form.append('label', upload.value.label || '')
  form.append('import_mode', upload.value.import_mode)
  form.append('file', upload.value.file)

  router.post(route('gisLayers.revisions.store', props.selectedLayer.id), form, {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => {
      busy.value = false
      // Clear form
      upload.value.label = ''
      upload.value.file = null
      if (fileInput.value) fileInput.value.value = ''
    },
  })
}

const setIncluded = (rev, isIncluded) => {
  router.patch(route('gisLayers.revisions.included', [props.selectedLayer.id, rev.id]), { is_included: isIncluded }, {
    preserveScroll: true,
  })
}

const activateReplace = (rev) => {
  router.post(route('gisLayers.revisions.activateReplace', [props.selectedLayer.id, rev.id]), {}, { preserveScroll: true })
}

const archiveRevision = (rev) => {
  router.post(route('gisLayers.revisions.archive', [props.selectedLayer.id, rev.id]), {}, { preserveScroll: true })
}

const purgeRevision = (rev) => {
  if (!confirm('Are you sure? This cannot be undone.')) return;
  router.delete(route('gisLayers.revisions.purge', [props.selectedLayer.id, rev.id]), {
    preserveScroll: true,
  })
}

// --- Visual Helpers ---

const statusBadgeClass = (s) => {
  switch (s) {
    case 'completed': return 'bg-green-50 text-green-700 border-green-200'
    case 'processing': return 'bg-blue-50 text-blue-700 border-blue-200'
    case 'queued': return 'bg-yellow-50 text-yellow-700 border-yellow-200'
    case 'failed': return 'bg-red-50 text-red-700 border-red-200'
    case 'archived': return 'bg-gray-100 text-gray-600 border-gray-200'
    default: return 'bg-gray-50 text-gray-700 border-gray-200'
  }
}
</script>