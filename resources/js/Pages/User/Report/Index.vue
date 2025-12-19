<template>
  <div>
    <ReusableDataTable routeResource="citizenReports" :columns="dataColumns" :tableData="tableData"
      inertiaKey="tableData" pageTitle="Manage My Reports" :showToolbar="false" :showEditButton="false"
      :showDeleteButton="false">


      <template #columns="{ isColumnVisible }">
        <!-- ID-->
        <Column v-if="isColumnVisible('report_id')" field="report_id" header="Id" sortable>
          <template #body="{ data }">
            {{ data.report_id }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('report_type_id')" field="report_type_id" header="Report Type" sortable>
          <template #body="{ data }">
            {{ data.type_label }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('tree_id')" field="tree_label" header="Tree" sortable>
          <template #body="{ data }">
            <span class="block max-w-152 truncate" v-tooltip.top="data.tree_label">
              {{ data.tree_label ?? '-' }}
            </span>
          </template>
        </Column>


        <Column v-if="isColumnVisible('lat')" field="lat" header="Lat" sortable>
          <template #body="{ data }">
            {{ data.lat }}
          </template>
        </Column>


        <Column v-if="isColumnVisible('lon')" field="lon" header="Lon" sortable>
          <template #body="{ data }">
            {{ data.lon }}
          </template>
        </Column>


        <Column v-if="isColumnVisible('description')" header="Description">
          <template #body="{ data }">
            <div class="flex items-start gap-2 min-w-0">
              <div class="min-w-0 max-w-184 text-sm text-slate-700 dark:text-slate-200 line-clamp-2"
                v-tooltip.top="data.description">
                {{ data.description_preview ?? '-' }}
              </div>

              <button v-if="data.description && data.description.length > 140"
                class="shrink-0 text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-100"
                @click.stop="openReportDetails(data)" type="button">
                More
              </button>
            </div>
          </template>
        </Column>

        <Column v-if="isColumnVisible('status')" field="status" header="Status" sortable>
          <template #body="{ data }">
            <span :class="statusInfo(data.status).color">
              {{ statusInfo(data.status).label }}
            </span>
          </template>
        </Column>

        <!-- Photo Column -->
        <Column v-if="isColumnVisible('photo_id')" field="photo_id" header="Photo" sortable>
          <template #body="{ data }">
            <div v-if="data.photo_id" class="flex items-center gap-2">
              <!-- Thumbnail -->
              <div @click.stop="openPreview(data.photo)"
                class="relative w-12 h-12 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer hover:ring-2 hover:ring-emerald-500 transition-all group">
                <img :src="data.photo?.url || '/placeholder-tree.jpg'" :alt="`Tree ${data.tree_id} photo`"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-200" />
                <div
                  class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                  <Eye class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                </div>
              </div>

              <!-- Photo Link -->
              <NavLinkButton :href="route('photos.index', { tree_id: data.tree_id, search: data.photo_id })"
                class="text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 flex items-center gap-1">
                <ExternalLink class="w-3.5 h-3.5 mr-1" />
                #{{ data.photo_id }}
              </NavLinkButton>

            </div>

            <!-- No Photo State -->
            <div v-else class="flex items-center gap-2 text-gray-400 dark:text-gray-600 select-none">
              <div
                class="w-12 h-12 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center">
                <ImageOff class="w-5 h-5" />
              </div>
              <span class="text-xs">No photo</span>
            </div>
          </template>
        </Column>


        <Column v-if="isColumnVisible('created_at')" field="created_at" header="Created At" sortable>
          <template #body="{ data }">
            {{ formatDate(data.created_at) }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('resolved_at')" field="resolved_at" header="Resolved At" sortable>
          <template #body="{ data }">
            {{ formatDate(data.resolved_at) }}
          </template>
        </Column>

      </template>

    </ReusableDataTable>
      <UserReportPreview v-model:visible="formVisible" :dataRow="selected" />
    <PhotoPreview v-model:visible="previewVisible" :photo="previewPhoto" />

  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import { useDateFormatter } from "@/Composables/useDateFormatter";
import NavLinkButton from "@/Components/NavLinkButton.vue";
import { Eye, ImageIcon, ImageOff, ExternalLink } from 'lucide-vue-next';
import PhotoPreview from "@/Pages/Photo/Partials/PhotoPreview.vue";
import UserReportPreview from "@/Pages/User/Report/Partials/UserReportPreview.vue";


defineOptions({
  layout: AuthenticatedLayout,
});

const props = defineProps({
  tableData: {
    type: Object,
    required: true,
  },
  dataColumns: {
    type: Object,
  },
  reportStatus: {
    type: Array,
    default: () => [],
  },
});


console.log(props.tableData);

const { formatDate } = useDateFormatter();

// --- preview state ---
const previewVisible = ref(false)
const previewPhoto = ref(null)

const selected = ref(null)
const formVisible = ref(false)

function openReportDetails(row) {
  selected.value = row
  formVisible.value = true
}


async function openPreview(photo) {
  previewPhoto.value = photo
  previewVisible.value = true
}

const statusColors = {
  open: 'text-red-600',
  triaged: 'text-yellow-600',
  resolved: 'text-green-600',
}

const statusInfo = (status) => {
  const found = props.reportStatus.find(item => item.value === status)
  if (!found) return { label: '', color: '' }

  return {
    label: found.label,
    color: statusColors[status] || ''
  }
}

</script>