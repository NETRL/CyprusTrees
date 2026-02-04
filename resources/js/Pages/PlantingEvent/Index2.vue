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

    <DataTable ref="dt" class="m-4 rounded-xl border border-gray-200 dark:border-gray-800 truncate"
      :value="tableData.data" :lazy="true" dataKey="planting_id" v-model:expandedRows="expandedRows" :paginator="true"
      :rows="perPage" :totalRecords="tableData.total" :first="(tableData.current_page - 1) * tableData.per_page"
      :rowsPerPageOptions="[5, 10, 25, 50, 100]" responsiveLayout="scroll" :loading="isLoading" @page="onPage"
      :sortField="sortField" :sortOrder="sortOrder === 'asc' ? 1 : sortOrder === 'desc' ? -1 : null" @sort="onSort">
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

      <Column field="planting_id" header="Event ID" sortable />

      <Column class="max-w-[400px] overflow-hidden" field="campaign_label" header="Campaign" sortField="campaign_id"
        sortable>
        <template #body="{ data }">
          <div v-tooltip="data.campaign_label" class="max-w-full truncate cursor-pointer hover:text-primary"
            @click="toggleNote($event, data.campaign_label)">
            {{ data.campaign_label }}
          </div>
        </template>
      </Column>
      <Column field="neighborhood_label" header="Neighborhood" sortField="neighborhood_id" sortable />

      <Column field="assigned_to_label" header="Assigned To" sortField="assigned_to" sortable />

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

      <Column field="trees_count" header="Actual Trees">
        <template #body="{ data }">{{ data.trees_count ?? 0 }}</template>
      </Column>

      <!-- Location  -->
      <Column field="location" header="Location" sortField="lat" sortable>
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

            <!-- Maybe make a "Manage Trees" page -->
            <!-- <Link :href="route('plantingEvents.show', data.planting_id)" class="text-sm text-brand-600 hover:underline">
              Manage Trees
            </Link> -->
          </div>

          <DataTable :value="data.planted_trees" :key="data.planting_id"
            class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-900 "
            responsiveLayout="scroll">
            <Column field="id" header="Planting Id" sortable />
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

            <Column field="planted_by_label" header="Planted By" sortable />

            <Column field="planted_at" header="Planted At" sortable>
              <template #body="{ data: row }">{{ formatDate(row.planted_at) }}</template>
            </Column>

            <Column field="planting_method" header="Method" />
            <Column field="notes" header="Notes" />
            <Column :exportable="false" header="Actions">
              <template #body="{ data: row }">
                <Button class="p-button-rounded mr-2 max-sm:text-sm! my-1" severity="primary" icon="pi pi-pencil"
                  @click="openItemEditForm(row)" />
                <Button class="p-button-rounded max-sm:text-sm!" severity="danger" icon="pi pi-trash"
                  @click="deleteItemResource(row.id)" />
              </template>
            </Column>
          </DataTable>
        </div>
      </template>
    </DataTable>

    <PlantingEventForm v-model:visible="formVisible" routeResource="plantingEvents" :action="formAction"
      :campaigns="campaignData" :neighborhoods="neighborhoodData" :users="userData" :statusOptions="statusOptions"
      :dataRow="formRow" @created="reloadTable" @updated="reloadTable" />

    <PlantingEventTreeForm v-model:visible="itemFormVisible" routeResource="plantingEventTrees" :action="itemFormAction"
      :users="userData" :dataRow="itemFormRow" @updated="reloadTable" />
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
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { ExternalLink } from "lucide-vue-next";
import PlantingEventForm from "@/Pages/PlantingEvent/Partials/PlantingEventForm.vue";
import PlantingEventTreeForm from "@/Pages/PlantingEvent/Partials/PlantingEventTreeForm.vue";
import { useDateFormatter } from "@/Composables/useDateFormatter";
import { debounce } from "lodash";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
  tableData: {
    type: Object,
    required: true
  },
  campaignData: {
    type: Array, default: () => []
  },
  neighborhoodData: {
    type: Array, default: () => []
  },
  userData: {
    type: Array, default: () => []
  },
  statusOptions: {
    type: Array, default: () => []
  },
});

// --- UI helpers ---
const statusColors = {
  draft: "text-gray-500",
  scheduled: "text-blue-600",
  in_progress: "text-yellow-600",
  completed: "text-green-600",
  cancelled: "text-red-600",
};

const statusInfo = (status) => {
  const found = props.statusOptions.find((item) => item.value === status);
  if (!found) return { label: "", color: "" };

  return {
    label: found.label,
    color: statusColors[status] || "",
  };
};

const { formatDate } = useDateFormatter();

const dt = ref(null);
const exportCSV = () => dt.value?.exportCSV?.();

// --- URL state (search/sort/paging) ---


// Read query param from current URL.
// Using URL here keeps behavior consistent even if Inertia props aren't used for query.
const getParam = (k, fallback = null) => {
  const sp = new URL(window.location.href).searchParams;
  return sp.get(k) ?? fallback;
};

// Normalize search values for the backend:
const asSearchParam = (val) => {
  const t = (val ?? "").trim();
  return t === "" ? null : t;
};


// Query state (initialized from URL for back/refresh consistency).
const searchQuery = ref(getParam("search", "") || "");
const sortField = ref(getParam("sortField", null));
const sortOrder = ref(getParam("sortOrder", null)); // "asc" | "desc"
const perPage = ref(parseInt(getParam("per_page", "10"), 10) || 10);

// Persist per-page preference.
const PER_PAGE_KEY = "plantingEvents_per_page";
watch(perPage, (v) => localStorage.setItem(PER_PAGE_KEY, String(v)));


// Build params for backend navigation. This is the single source of truth for query params.
const buildQueryParams = (overrides = {}) => ({
  page: props.tableData?.current_page ?? 1,
  per_page: perPage.value,
  search: asSearchParam(searchQuery.value),
  ...(sortField.value ? { sortField: sortField.value } : {}),
  ...(sortOrder.value ? { sortOrder: sortOrder.value } : {}),
  ...overrides,
});


// Inertia navigation helper:
// preserveState keeps form/open panels intact
// replace prevents history spam during search typing
// only keeps payload minimal
const navigate = (overrides = {}) => {
  router.get(route("plantingEvents.index"), buildQueryParams(overrides), {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    only: ["tableData"],
  });
};

// --- Search (debounced, server-side) ---

const debouncedSearch = debounce(() => {
  navigate({ page: 1 });
}, 400);

watch(searchQuery, () => {
  debouncedSearch();
});


//--- Expanded row persistence ---
const expandedRows = ref({});
const EXPANDED_KEY = "plantingEvents_expanded_ids";
const expandedIdSet = ref(new Set());

const loadExpandedSet = () => {
  try {
    const raw = localStorage.getItem(EXPANDED_KEY);
    const arr = raw ? JSON.parse(raw) : [];
    expandedIdSet.value = new Set(Array.isArray(arr) ? arr.map(Number) : []);
  } catch {
    expandedIdSet.value = new Set();
  }
};

const saveExpandedSet = () => {
  localStorage.setItem(EXPANDED_KEY, JSON.stringify([...expandedIdSet.value]));
};


// Apply persisted expansions to CURRENT page only. PrimeVue will only expand rows present in :value anyway.
const applyExpandedToCurrentPage = () => {
  const map = {};
  for (const row of props.tableData?.data ?? []) {
    if (expandedIdSet.value.has(Number(row.planting_id))) {
      map[row.planting_id] = true;
    }
  }
  expandedRows.value = map;
};


// Keep the global Set in sync when user expands/collapses on the current page.
watch(
  expandedRows,
  (newMap) => {
    // Add expanded rows from current page
    Object.keys(newMap || {}).forEach((id) => {
      if (newMap[id]) expandedIdSet.value.add(Number(id));
    });

    // Remove rows from current page that are now collapsed
    for (const row of props.tableData?.data ?? []) {
      if (!newMap?.[row.planting_id]) expandedIdSet.value.delete(Number(row.planting_id));
    }

    saveExpandedSet();
  },
  { deep: true }
);

// When backend data changes (pagination/sort/search), re-apply expansions.
watch(
  () => props.tableData?.data,
  () => applyExpandedToCurrentPage(),
  { deep: false }
);


// Expand/collapse all on the CURRENT page (not globally).
const expandAll = () => {
  for (const row of props.tableData?.data ?? []) {
    expandedIdSet.value.add(Number(row.planting_id));
  }
  saveExpandedSet();
  applyExpandedToCurrentPage();
};

const collapseAll = () => {
  for (const row of props.tableData?.data ?? []) {
    expandedIdSet.value.delete(Number(row.planting_id));
  }
  saveExpandedSet();
  applyExpandedToCurrentPage();
};

// --- Loading state  ---

const isLoading = ref(false);
let offStart, offFinish;

onMounted(() => {
  // Subscribe once; clean up on unmount
  offStart = router.on("start", () => (isLoading.value = true));
  offFinish = router.on("finish", () => (isLoading.value = false));

  // Expansion persistence
  loadExpandedSet();
  applyExpandedToCurrentPage();

  // perPage persistence
  const storedPerPage = parseInt(localStorage.getItem(PER_PAGE_KEY) || "", 10);
  if (!Number.isNaN(storedPerPage) && storedPerPage > 0 && storedPerPage !== perPage.value) {
    perPage.value = storedPerPage;
    // do not auto-navigate on mount; only update next interaction
  }
});

onBeforeUnmount(() => {
  offStart?.();
  offFinish?.();
});

// --- DataTable events (server-side) ---
const onPage = (e) => {
  // PrimeVue gives 0-based e.page and actual page size e.rows
  perPage.value = e.rows;
  navigate({ page: e.page + 1 });
};

const onSort = (e) => {
  // PrimeVue gives sortOrder as 1 (asc) or -1 (desc)
  sortField.value = e.sortField ?? null;
  sortOrder.value = e.sortOrder === 1 ? "asc" : "desc";
  navigate({ page: 1 });
};

// --- CRUD ---
const formVisible = ref(false);
const formAction = ref("");
const formRow = ref(null);

const itemFormVisible = ref(false);
const itemFormAction = ref("");
const itemFormRow = ref(null);

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
  router.delete(route("plantingEvents.destroy", id), {
    preserveScroll: true,
    onSuccess: () => reloadTable(),
  });
};

const openItemEditForm = (row) => {
  itemFormRow.value = row;
  itemFormAction.value = "Edit";
  itemFormVisible.value = true;
};

const deleteItemResource = (id) => {
  router.delete(route("plantingEventTrees.destroy", id), {
    preserveScroll: true,
    onSuccess: () => reloadTable(),
  });
};

// --- Notes popover ---
const op = ref(null);
const selectedNote = ref("");

const toggleNote = (event, text) => {
  selectedNote.value = text;
  op.value?.toggle?.(event);
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
