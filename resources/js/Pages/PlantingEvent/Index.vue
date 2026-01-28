<template>
  <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
    <Toolbar class="m-4 dark:border-gray-800! dark:bg-transparent!">
      <template #start>
        <Button severity="success" class="mr-2 mb-2 max-sm:text-sm!" icon="pi pi-plus" label="New Event"
          @click="openCreateForm()" />
      </template>

      <template #end>
        <Button class="mb-2 max-sm:text-sm!" severity="help" icon="pi pi-upload" label="Export"
          @click="exportCSV($event)" />
      </template>
    </Toolbar>

    <DataTable class="m-4 rounded-xl border border-gray-200 dark:border-gray-800 truncate" ref="dt"
      v-model:expandedRows="expandedRows" :filters="filters" :value="tableData.data" :lazy="true" :paginator="true" 
      :rows="perPage" :totalRecords="tableData.total" :first="(tableData.current_page - 1) * tableData.per_page" 
      :rowsPerPageOptions="[5, 10, 25, 50, 100]" responsiveLayout="scroll" :loading="isLoading" @page="onPage" @sort="onSort">
      <template #header>
        <div class="table-header flex flex-column md:flex-row md:justiify-content-between">
          <div>

            <h5 class="mb-2 md:m-0 p-as-md-center">Manage Planting Events</h5>

            <span class="flex gap-2 items-center max-md:w-full">
              <Button class="my-2 max-sm:text-sm! text-xs!" severity="primary" icon="pi pi-plus" label="Expand All"
                @click="expandAll" />
              <Button class="my-2 max-sm:text-sm! text-xs!" severity="primary" icon="pi pi-minus" label="Collapse All"
                @click="collapseAll" />
            </span>
          </div>
          <InputText v-model="searchQuery" class="p-inputtext-sm max-md:w-full" placeholder="Search..." />
        </div>
      </template>

      <template #empty>No planting events found.</template>

      <Column :expander="true" headerStyle="width:3rem" />

      <Column field="planting_id" header="Id" sortable />

      <Column class="max-w-[400px] overflow-hidden" field="campaign_label" header="Campaign" sortable>
        <template #body="{ data }">
          <div v-tooltip="data.campaign_label" class="max-w-full truncate cursor-pointer hover:text-primary"
            @click="toggleNote($event, data.campaign_label)">
            {{ data.campaign_label }}
          </div>
        </template>
      </Column>
      <Column field="neighborhood_label" header="Neighborhood" sortable />

      <Column field="assigned_to_label" header="Assigned To" sortable />

      <Column field="status" header="Status" sortable>
        <template #body="{ data }">
          <span :class="statusInfo(data.status).color">
            {{ statusInfo(data.status).label }}
          </span>
        </template>
      </Column>

      <Column field="started_at" header="Started" sortable>
        <template #body="{ data }">{{ formatDate(data.started_at) }}</template>
      </Column>

      <Column field="completed_at" header="Completed" sortable>
        <template #body="{ data }">{{ formatDate(data.completed_at) }}</template>
      </Column>

      <Column field="target_tree_count" header="Target" sortable>
        <template #body="{ data }">{{ data.target_tree_count ?? '-' }}</template>
      </Column>

      <Column field="trees_count" header="Actual Trees" sortable>
        <template #body="{ data }">{{ data.trees_count ?? 0 }}</template>
      </Column>

      <!-- Location (your addition) -->
      <Column field="location" header="Location" sortable>
        <template #body="{ data }">
          <Link v-if="data.location" :href="route('/', { lat: data.lat, lon: data.lon })"
            class="flex justify-start items-center spece-x-2 hover:cursor-pointer hover:text-brand-600">
            {{ data.location }}
            <ExternalLink class="w-3.5 h-3.5 mx-1" />
          </Link>
          <template v-else>-</template>
        </template>
      </Column>

      <Column class="max-w-[400px] overflow-hidden" field="notes" header="Notes" sortable>
        <template #body="{ data }">
          <div v-tooltip="data.notes" class="max-w-full truncate cursor-pointer hover:text-primary"
            @click="toggleNote($event, data.notes)">
            {{ data.notes }}
          </div>
        </template>
      </Column>
      <Column :exportable="false" header="Actions">
        <template #body="{ data }">
          <Button class="p-button-rounded mr-2 max-sm:text-sm! my-1" severity="primary" icon="pi pi-pencil"
            @click="openEditForm(data)" />
          <Button class="p-button-rounded max-sm:text-sm!" severity="danger" icon="pi pi-trash"
            @click="deleteResource(data.planting_id)" />
        </template>
      </Column>

      <!-- Expansion -->
      <template #expansion="{ data }">
        <div class="pt-3">
          <div class="flex items-center justify-between mb-2">
            <div class="text-sm font-medium">
              Planted Trees ({{ data.planted_trees?.length ?? 0 }})
            </div>

            <!-- optional: link to a "Manage Trees" page -->
            <!-- <Link :href="route('plantingEvents.show', data.planting_id)" class="text-sm text-brand-600 hover:underline">
              Manage Trees
            </Link> -->
          </div>

          <DataTable :value="data.planted_trees" class="rounded-lg border border-gray-200 dark:border-gray-800"
            responsiveLayout="scroll">
            <Column field="id" header="Item Id" sortable />
            <Column field="tree_id" header="Tree Id" sortable />

            <Column field="tree_label" header="Tree">
              <template #body="{ data: row }">
                <Link :href="route('/', { tree_id: row.tree_id })"
                  class="flex items-center spece-x-2 hover:cursor-pointer hover:text-brand-600">
                  {{ row.tree_label }}
                  <ExternalLink class="w-3.5 h-3.5 mx-1" />
                </Link>
              </template>
            </Column>

            <Column field="planter_label" header="Planted By" sortable />

            <Column field="planted_at" header="Planted At" sortable>
              <template #body="{ data: row }">{{ formatDate(row.planted_at) }}</template>
            </Column>

            <Column field="planting_method" header="Method" />
            <Column field="notes" header="Notes" />
          </DataTable>
        </div>
      </template>
    </DataTable>

    <PlantingEventForm v-model:visible="formVisible" routeResource="plantingEvents" :action="formAction"
      :campaigns="campaignData" :neighborhoods="neighborhoodData" :users="userData" :statusOptions="statusOptions"
      :dataRow="formRow" @created="reloadTable" @updated="reloadTable" />
  </div>

  <Popover ref="op">
    <div class="p-3 max-w-[300px] text-sm leading-relaxed">
      {{ selectedNote }}
    </div>
  </Popover>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { reactive, ref, watch } from "vue";
import { ExternalLink } from "lucide-vue-next";
import PlantingEventForm from "@/Pages/PlantingEvent/Partials/PlantingEventForm.vue";
import { useDateFormatter } from "@/Composables/useDateFormatter";
import { FilterMatchMode } from '@primevue/core/api';
import { debounce } from "lodash";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
  tableData: { type: Object, required: true }, // paginator
  campaignData: { type: Array, default: () => [] },
  neighborhoodData: { type: Array, default: () => [] },
  userData: { type: Array, default: () => [] },
  statusOptions: { type: Array, default: () => [] },
});

const statusColors = {
  draft: 'text-gray-500',
  scheduled: 'text-blue-600',
  in_progress: 'text-yellow-600',
  completed: 'text-green-600',
  cancelled: 'text-red-600',
}

const statusInfo = (status) => {
  const found = props.statusOptions.find(item => item.value === status)
  if (!found) return { label: '', color: '' }

  return {
    label: found.label,
    color: statusColors[status] || ''
  }
}

const { formatDate } = useDateFormatter();

const dt = ref(null);
const expandedRows = ref([]);

const formVisible = ref(false);
const formAction = ref("");
const formRow = ref(null);

const exportCSV = () => dt.value?.exportCSV?.();

const collapseAll = () => (expandedRows.value = []);
const expandAll = () => {
  // PrimeVue expander expects the full row objects
  expandedRows.value = props.tableData.data.slice();
};

const sortField = ref(null)
const sortOrder = ref(null) // PrimeVue gives 1 or -1

const PER_PAGE_KEY = props.routeResource + `_per_page`
const isLoading = ref(false);

const perPage = ref(parseInt(localStorage.getItem(PER_PAGE_KEY)) || 10);
watch(perPage, (v) => localStorage.setItem(PER_PAGE_KEY, String(v)))

const searchQuery = ref('');
watch(searchQuery, (newValue) => {
  const t = (newValue ?? '').trim()
  filters.value.global.value = t === '' ? null : t
  debouncedSearch(newValue)
})

const filters = ref({
  'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// ---- Loading state management ----
router.on('start', () => { isLoading.value = true });
router.on('finish', () => { isLoading.value = false });

// helper: normalize search param for requests
const asSearchParam = (val) => {
  const t = (val ?? '').trim()
  return t === '' ? null : t
}

// ---- Debounced search ----
const debouncedSearch = debounce((value) => {
  router.get(
    route('plantingEvents' + '.index'),
    buildQueryParams({ page: 1, search: asSearchParam(value) }),
    { preserveState: true, preserveScroll: true, only: [props.inertiaKey], replace: true }
  )
}, 500)


const buildQueryParams = (overrides = {}) => ({
  page: props.tableData?.current_page ?? 1,
  per_page: perPage.value,
  search: asSearchParam(searchQuery.value),
  ...(sortField.value ? { sortField: sortField.value } : {}),
  ...(sortOrder.value ? { sortOrder: sortOrder.value === 1 ? 'asc' : 'desc' } : {}),
  ...overrides,
})


const openCreateForm = () => {
  formRow.value = null;
  formAction.value = "Create";
  formVisible.value = true;
};

const openEditForm = (row) => {
  formRow.value = row;
  formAction.value = "Edit";
  formVisible.value = true;
};

const reloadTable = () => router.reload({ only: ["tableData"] });

const deleteResource = (id) => {
  // adapt to your confirmation mechanism if you have it globally
  router.delete(route("plantingEvents.destroy", id), {
    preserveScroll: true,
    onSuccess: () => reloadTable(),
  });
};

// Optional: if you want paging/sorting to hit backend (since your controller paginates)
const onPage = (e) => {
  router.get(route("plantingEvents.index"), { page: e.page + 1, per_page: e.rows }, { preserveState: true, replace: true });
};

const onSort = (e) => {
  router.get(route("plantingEvents.index"), { sort: e.sortField, direction: e.sortOrder === 1 ? "asc" : "desc" }, { preserveState: true, replace: true });
};

const op = ref(null);
const selectedNote = ref('');

const toggleNote = (event, text) => {
  selectedNote.value = text;
  if (op.value) {
    op.value.toggle(event);
  }
};
</script>

<style scoped>
.table-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

@media screen and (max-width: 960px) {
  .table-header {
    align-items: start;
  }
}
</style>
