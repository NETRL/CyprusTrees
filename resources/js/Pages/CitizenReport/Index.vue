<template>
  <div>
    <ReusableDataTable routeResource="citizenReports" :columns="dataColumns" :tableData="tableData"
      inertiaKey="tableData" pageTitle="Manage Citizen Reports" :showCreateButton="false" @edit="openEditForm"
      @afterDelete="onAfterDelete" @afterMassDelete="onAfterMassDelete" :showDateFilter="true"
      :dateFilterable="dateFilterable">


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

        <Column v-if="isColumnVisible('created_by')" field="created_by" header="Created By" sortable>
          <template #body="{ data }">
            {{ data.creator_label }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('tree_id')" field="tree_id" header="Tree" sortable>
          <template #body="{ data }">
            <Link :href="route('/', { tree_id: data.tree_id })"
              class="flex justify-start items-center spece-x-2 hover:cursor-pointer hover:text-brand-600">
            {{ data.tree_label }}
            <ExternalLink class="w-3.5 h-3.5 mx-1" />
            </Link>
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


        <Column v-if="isColumnVisible('description')" field="description" header="Description" sortable>
          <template #body="{ data }">
            {{ data.description ?? '-' }}
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
                class="relative w-12 h-12 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer hover:ring-2 hover:ring-green-500 transition-all group">
                <img :src="data.photo.url || '/placeholder-tree.jpg'" :alt="`Tree ${data.tree_id} photo`"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-200" />
                <div
                  class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                  <Eye class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                </div>
              </div>

              <!-- Photo Link -->
              <NavLinkButton :href="route('photos.index', { tree_id: data.tree_id, search: data.photo_id })"
                class="text-xs font-medium text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 flex items-center gap-1">
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

        <Column>
          <template #body="{ data }">
            <button @click="openMntForm(data)" :disabled="data.status === 'resolved'" :class="[
              'inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
              data.status === 'resolved'
                ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed border border-gray-200 dark:border-gray-700'
                : 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/40 border border-green-200 dark:border-green-800 hover:shadow-sm focus:ring-green-500'
            ]" :title="data.status === 'resolved' ? 'Report already resolved' : 'Promote this report to maintenance'">
              <ArrowUpCircle class="w-4 h-4" />
              <span>{{ data.status === 'resolved' ? 'Resolved' : 'Promote' }}</span>
            </button>
          </template>
        </Column>

      </template>

    </ReusableDataTable>

    <CitizenReportForm v-model:visible="formVisible" routeResource="citizenReports" :action="formAction"
      :dataRow="formRow" @updated="reloadTable" @created="reloadTable" :trees="treeData" :types="typeData"
      :users="userData" :reportStatus="reportStatus" />

    <PhotoPreview v-model:visible="previewVisible" :photo="previewPhoto" />

    <PromoteMntEventForm v-model:visible="mntFormVisible" routeResource="maintenanceEvents" :dataRow="formRow"
      @updated="reloadTable" @created="reloadTable" :trees="treeData" :types="mntTypeData" :users="userData" />
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import CitizenReportForm from "@/Pages/CitizenReport/Partials/CitizenReportForm.vue";
import { useDateFormatter } from "@/Composables/useDateFormatter";
import NavLinkButton from "@/Components/NavLinkButton.vue";
import { Eye, ImageIcon, ImageOff, ExternalLink, ArrowUpCircle } from 'lucide-vue-next';
import PhotoPreview from "@/Pages/Photo/Partials/PhotoPreview.vue";
import PromoteMntEventForm from "./Partials/PromoteMntEventForm.vue";


defineOptions({
  layout: AuthenticatedLayout,
});

const props = defineProps({
  tableData: { type: Object, required: true, },
  dataColumns: { type: Object, },
  treeData: { type: Array, default: () => [], },
  typeData: { type: Array, default: () => [], },
  mntTypeData: { type: Array, default: () => [], },
  userData: { type: Array, default: () => [], },
  reportStatus: { type: Array, default: () => [], },
  reportTypes: { type: Object, default: null, },
  dateFilterable: { type: Array, default: () => [], },
});

const { formatDate } = useDateFormatter();

// --- preview state ---
const previewVisible = ref(false)
const previewPhoto = ref(null)

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

// --- Maintenance Form State ---
const mntFormVisible = ref(false);
const openMntForm = (row) => {
  formRow.value = row;
  mntFormVisible.value = true;
};

// --- form state ---
const formVisible = ref(false);
const formAction = ref('');      // 'Create' or 'Edit'
const formRow = ref(null);       // current row

const openEditForm = (row) => {
  formRow.value = row;
  formAction.value = 'Edit';
  formVisible.value = true;
};

// reload table when form finishes
const reloadTable = () => {
  router.reload({ only: ['tableData'] });
};

const onAfterDelete = () => {
  reloadTable();
};

const onAfterMassDelete = () => {
  reloadTable();
};
</script>