<template>
  <div class="p-6 space-y-4">
    <div class="text-xl font-semibold">GIS Layers</div>

    <div v-if="flash" class="rounded-lg border p-3 text-sm"
      :class="flash.type === 'success' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'">
      {{ flash.message }}
    </div>

    <div class="grid grid-cols-12 gap-4">
      <!-- Layers list -->
      <div class="col-span-12 lg:col-span-4">
        <div class="rounded-xl border bg-white">
          <div class="flex justify-between items-center  border-b">
            <div class="p-4 font-semibold">Layers</div>
          </div>
          <div class="divide-y">
            <button v-for="l in layers" :key="l.id" class="w-full text-left p-4 hover:bg-gray-50"
              :class="Number(selectedLayerId) === l.id ? 'bg-gray-50' : ''" @click="onSelectLayer(l.id)">
              <div class="flex items-center justify-between gap-2">
                <div class="font-medium">{{ l.display_name }}</div>
                <div class="text-xs text-gray-500">{{ l.category || '—' }}</div>
              </div>
              <div class="text-xs text-gray-500 mt-1">
                key: {{ l.key }} • default: {{ l.default_import_mode }}
                <span v-if="!l.is_active" class="ml-2 text-red-600">inactive</span>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Selected layer panel -->
      <div class="col-span-12 lg:col-span-8">
        <div v-if="!selectedLayer" class="rounded-xl border bg-white p-6 text-sm text-gray-600">
          Select a layer to manage revisions.
        </div>

        <div v-else class="space-y-4">
          <div class="rounded-xl border bg-white">
            <div class="p-4 border-b">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <div class="text-lg font-semibold">{{ selectedLayer.display_name }}</div>
                  <div class="text-xs text-gray-500 mt-1">
                    key: {{ selectedLayer.key }} • default import: {{ selectedLayer.default_import_mode }}
                  </div>
                </div>
                <div class="text-xs text-gray-500">
                  Active revision:
                  <span class="font-medium">
                    {{ selectedLayer.active_revision?.label || '—' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Upload -->
            <div class="p-4 space-y-3">
              <div class="text-sm font-medium">Import GeoJSON</div>

              <div class="grid grid-cols-12 gap-3 items-end">
                <div class="col-span-12 md:col-span-5">
                  <label class="text-xs text-gray-600">Label (optional)</label>
                  <input v-model="upload.label" type="text" class="w-full rounded-lg border px-3 py-2 text-sm"
                    placeholder="Survey Feb 2026" />
                </div>

                <div class="col-span-12 md:col-span-3">
                  <label class="text-xs text-gray-600">Mode</label>
                  <select v-model="upload.import_mode" class="w-full rounded-lg border px-3 py-2 text-sm">
                    <option value="replace">replace (baseline)</option>
                    <option value="append">append (additive)</option>
                  </select>
                </div>

                <div class="col-span-12 md:col-span-4">
                  <label class="text-xs text-gray-600">File</label>
                  <input type="file" accept=".geojson,.json" @change="onFileChange" class="w-full text-sm" />
                </div>
              </div>

              <button class="rounded-lg bg-black text-white px-4 py-2 text-sm disabled:opacity-50"
                :disabled="busy || !upload.file" @click="submitUpload">
                {{ busy ? 'Uploading…' : 'Upload & Queue Import' }}
              </button>

              <div class="text-xs text-gray-500">
                Replace = new baseline (excludes older revisions). Append = keeps older included + adds this revision.
              </div>
            </div>
          </div>

          <!-- Revisions table -->
          <div class="rounded-xl border bg-white overflow-hidden">
            <div class="p-4 border-b font-semibold">Revisions</div>

            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-600">
                  <tr>
                    <th class="text-left px-4 py-3">Label</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Mode</th>
                    <th class="text-left px-4 py-3">Included</th>
                    <th class="text-left px-4 py-3">Features</th>
                    <th class="text-left px-4 py-3">Created</th>
                    <th class="text-right px-4 py-3">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <tr v-for="r in revisions" :key="r.id">
                    <td class="px-4 py-3">
                      <div class="font-medium">{{ r.label || ('Revision #' + r.revision_no) }}</div>
                      <div class="text-xs text-gray-500 truncate max-w-[420px]">
                        {{ r.original_name || '—' }}
                      </div>
                      <div v-if="r.error" class="text-xs text-red-600 mt-1">
                        {{ r.error }}
                      </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ statusBadge(r.status) }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ r.import_mode }}</td>

                    <td class="px-4 py-3">
                      <div class="flex items-center gap-2">
                        <input type="checkbox" :checked="!!r.is_included" :disabled="r.status !== 'completed'"
                          @change="setIncluded(r, $event.target.checked)" />
                        <span class="text-xs text-gray-600">
                          {{ r.is_included ? 'yes' : 'no' }}
                        </span>
                      </div>
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">
                      {{ r.features_imported ?? 0 }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">
                      {{ new Date(r.created_at).toLocaleString() }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap text-right">
                      <div class="inline-flex gap-2">
                        <button class="px-3 py-1 rounded-md border text-xs disabled:opacity-50"
                          :disabled="r.status !== 'completed'" @click="activateReplace(r)">
                          Activate (replace)
                        </button>

                        <button class="px-3 py-1 rounded-md border text-xs disabled:opacity-50"
                          :disabled="r.status === 'archived'" @click="archiveRevision(r)">
                          Archive
                        </button>

                        <button class="px-3 py-1 rounded-md border text-xs text-red-600 disabled:opacity-50"
                          :disabled="r.is_included || (selectedLayer.active_revision_id === r.id)"
                          @click="purgeRevision(r)">
                          Purge
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="!revisions?.length">
                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                      No revisions yet.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="p-4 text-xs text-gray-500 border-t">
              Included revisions define what the map shows for this layer (Model A).
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
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

const selectedLayer = computed(() => props.selectedLayer)
const selectedLayerId = computed(() => props.selectedLayerId)

const upload = ref({
  label: '',
  import_mode: selectedLayer.value?.default_import_mode || 'replace',
  file: null,
})

const busy = ref(false)

const flash = computed(() => page.props?.flash?.message)

const onSelectLayer = (layerId) => {
  router.get(route('gisLayers.data.index'), { layer_id: layerId }, { preserveState: true })
}

const onFileChange = (e) => {
  upload.value.file = e.target.files?.[0] || null
}

const submitUpload = () => {
  if (!selectedLayer.value?.id) return
  if (!upload.value.file) return

  busy.value = true
  const form = new FormData()
  form.append('label', upload.value.label || '')
  form.append('import_mode', upload.value.import_mode)
  form.append('file', upload.value.file)

  router.post(route('gisLayers.revisions.store', selectedLayer.value.id), form, {
    forceFormData: true,
    onFinish: () => {
      busy.value = false
      upload.value.file = null
    },
  })
}

const setIncluded = (rev, isIncluded) => {
  router.patch(route('gisLayers.revisions.included', [selectedLayer.value.id, rev.id]), { is_included: isIncluded }, {
    preserveScroll: true,
  })
}

const activateReplace = (rev) => {
  router.post(route('gisLayers.revisions.activateReplace', [selectedLayer.value.id, rev.id]), {}, { preserveScroll: true })
}

const archiveRevision = (rev) => {
  router.post(route('gisLayers.revisions.archive', [selectedLayer.value.id, rev.id]), {}, { preserveScroll: true })
}

const purgeRevision = (rev) => {
  router.delete(route('gisLayers.revisions.purge', [selectedLayer.value.id, rev.id]), {
    preserveScroll: true,
  })
}

const statusBadge = (s) => {
  if (s === 'completed') return '✅ completed'
  if (s === 'processing') return '⏳ processing'
  if (s === 'queued') return '🕓 queued'
  if (s === 'failed') return '❌ failed'
  if (s === 'archived') return '📦 archived'
  return s
}
</script>
