<template>
  <div class="h-full flex flex-col">
    <!-- Top Toolbar -->
    <Toolbar v-if="showToolbar"
      class="m-4 rounded-xl border border-gray-200 bg-white/70 backdrop-blur dark:border-gray-800! dark:bg-transparent!">
      <template #start>
        <div class="flex flex-wrap items-center gap-2">
          <Button v-if="showCreateButton"
            v-has-permission="{ props: $page.props, permissions: [finalPermissionCreate] }" severity="success"
            icon="pi pi-plus" label="New" class="max-sm:text-sm!" @click="onCreateClick" />

          <Button v-if="showMassDeleteButton"
            v-has-permission="{ props: $page.props, permissions: [finalPermissionDelete] }" severity="danger"
            icon="pi pi-trash" label="Delete" class="max-sm:text-sm!" :disabled="!selected?.length"
            :badge="selected?.length ? String(selected.length) : null" @click="onMassDeleteClick" />

          <!-- Toolbar start slot -->
          <slot name="toolbarStart"></slot>
        </div>
      </template>

      <template #end>
        <div class="flex flex-wrap items-center gap-2 justify-end">
          <!-- Toolbar end slot -->
          <slot name="toolbarEnd"></slot>

          <Button v-if="showExportButton" severity="help" icon="pi pi-upload" label="Export" class="max-sm:text-sm!"
            :disabled="!tableData?.data?.length" @click="exportCSV" />
        </div>
      </template>
    </Toolbar>

    <!-- Column chooser -->
    <div class="mx-4 mb-2">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
          <span class="text-sm text-slate-600 dark:text-slate-300">Columns</span>
          <span class="text-xs text-slate-500 dark:text-slate-400">
            {{ selectedColumnDefs.length }} / {{ columnsToShow.length }}
          </span>
        </div>

        <div class="w-full sm:w-[380px]">
          <FormField component="MultiSelect" v-model="selectedColumns" :options="columnsToShow" optionLabel="label"
            optionValue="value" :maxSelectedLabels="1" filter placeholder="Select columns" />
        </div>
      </div>
    </div>

    <div class="flex-1 min-h-0">
      <DataTable ref="dt" class="m-4 rounded-xl border border-gray-200 dark:border-gray-800 truncate" :value="tableData.data"
        :lazy="true" :paginator="true" :rows="perPage" :totalRecords="tableData.total"
        :first="(tableData.current_page - 1) * tableData.per_page" :rowsPerPageOptions="[5, 10, 25, 50, 100]"
        responsiveLayout="scroll" :loading="isLoading" v-model:selection="selected" v-model:filters="filters"
        dataKey="id" :rowHover="true" @page="onPage" @sort="onSort">
        <!-- Header -->
        <template #header>
          <div class="flex flex-col gap-3">
            <!-- Title + meta -->
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h5 class="m-0 text-lg md:text-xl font-semibold truncate">
                  {{ pageTitle }}
                </h5>

                <div
                  class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                  <span v-if="tableData?.total != null">
                    {{ tableData.total }} total
                  </span>
                  <span v-if="tableData?.current_page && tableData?.last_page">
                    Page {{ tableData.current_page }} / {{ tableData.last_page }}
                  </span>
                  <span v-if="activeFiltersCount > 0">
                    {{ activeFiltersCount }} filter{{ activeFiltersCount === 1 ? '' : 's' }} active
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <!-- Quick clear -->
                <Button v-if="activeFiltersCount > 0" severity="secondary" outlined icon="pi pi-times" label="Clear"
                  class="max-sm:text-sm!" @click="clearFilters" />

                <!-- filter toggle -->
                <Button v-if="(showSearch || showDateFilter)" icon="pi pi-filter" severity="secondary" outlined
                  @click="toggleFilters"
                  :badge="activeFiltersCount > 0 ? String(activeFiltersCount) : null" aria-label="Toggle filters" />
              </div>
            </div>

            <!-- Filters -->
            <div v-if="showFilters"
              class="rounded-xl border border-slate-200 bg-gray-50 p-3 dark:border-slate-800 dark:bg-white/1">
              <div class="flex flex-col gap-3">
                <!-- Search -->
                <div v-if="showSearch" class="flex flex-col gap-1">
                  <label class="text-xs text-slate-600 dark:text-slate-300">Search</label>
                  <InputText v-model="searchQuery" class="w-full" placeholder="Search..." inputClass="w-full" />
                </div>

                <!-- Date Filters -->
                <div v-if="showDateFilter && props.dateFilterable?.length" class="flex flex-col gap-2">
                  <label class="text-xs text-slate-600 dark:text-slate-300">Date filters</label>

                  <FormField component="MultiSelect" v-model="selectedDateFields"
                    :options="props.dateFilterable.map(f => ({ label: f, value: f }))" optionLabel="label"
                    optionValue="value" :maxSelectedLabels="1" placeholder="Date fields" class="w-full" />

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <FormField component="Calendar" v-model="dateFrom" placeholder="From" dateFormat="dd/mm/yy" showIcon
                      class="w-full" />
                    <FormField component="Calendar" v-model="dateTo" placeholder="To" dateFormat="dd/mm/yy" showIcon
                      class="w-full" />
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-2">
                  <Button size="small" label="Apply" icon="pi pi-check" class="flex-1 min-w-[140px]"
                    @click="applyFilters" />
                  <Button v-if="activeFiltersCount > 0" size="small" severity="secondary" label="Clear"
                    icon="pi pi-times" class="flex-1 min-w-[140px]" @click="clearFilters" />
                </div>
              </div>
            </div>

            <!-- Selection hint -->
            <div v-if="selected?.length"
              class="flex items-center justify-between gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-900/10 dark:text-amber-100">
              <div class="truncate">
                <i class="pi pi-check-circle mr-2" />
                {{ selected.length }} selected
              </div>
              <Button severity="secondary" outlined size="small" icon="pi pi-times" label="Clear selection"
                @click="selected = []" />
            </div>
          </div>
        </template>

        <!-- Empty -->
        <template #empty>
          <div class="text-center py-10 text-slate-500 dark:text-slate-400">
            <i class="pi pi-inbox text-4xl mb-3 block"></i>
            <p class="m-0">{{ emptyMessage }}</p>
            <p v-if="activeFiltersCount > 0" class="mt-2 text-sm">
              Try clearing filters to see more results.
            </p>
          </div>
        </template>

        <!-- Selection column -->
        <Column :exportable="false" selectionMode="multiple" style="width: 3rem" />

        <!-- Custom columns slot OR auto columns -->
        <template v-if="$slots.columns">
          <slot name="columns" :isColumnVisible="isColumnVisible" :selectedColumns="selectedColumns"
            :columnsToShow="columnsToShow" />
        </template>

        <template v-else>
          <Column v-for="col in selectedColumnDefs" :key="col.value" :field="col.value" :header="col.label"
            :sortable="true">
            <template #body="slotProps" v-if="dateColumns.includes(col.value)">
              {{ formatDate(slotProps.data[col.value]) }}
            </template>

            <template #body="slotProps" v-else>
              <span class="block max-w-[240px] truncate" :title="String(slotProps.data[col.value] ?? '')">
                {{ slotProps.data[col.value] }}
              </span>
            </template>
          </Column>

          <!-- default slot -->
          <slot></slot>
        </template>

        <!-- Actions column -->
        <Column :exportable="false" header="Actions" style="width: 1%; white-space: nowrap;">
          <template #body="slotProps">
            <div class="flex items-center justify-end gap-2">
              <Button v-if="showEditButton"
                v-has-permission="{ props: $page.props, permissions: [finalPermissionEdit] }"
                class="p-button-rounded max-sm:text-sm!" severity="primary" icon="pi pi-pencil"
                @click="onEditClick(slotProps.data)" aria-label="Edit" />
              <Button v-if="showDeleteButton"
                v-has-permission="{ props: $page.props, permissions: [finalPermissionDelete] }"
                class="p-button-rounded max-sm:text-sm!" severity="danger" icon="pi pi-trash"
                @click="onDeleteClick(slotProps.data.id)" aria-label="Delete" />
            </div>
          </template>
        </Column>

        <slot name="tableEnd"></slot>
      </DataTable>

      <!-- Jump to page -->
      <div v-if="showJumpToPage" class="mx-4 mb-4 flex flex-wrap items-center gap-2">
        <span class="text-sm text-slate-600 dark:text-slate-300">{{ __('Jump to page') }}</span>
        <InputNumber v-model="currentPage" :max="tableData.last_page" :min="1" mode="decimal"
          @keydown.enter="onPageButton(currentPage)" />
        <Button :label="__('Go')" @click="onPageButton(currentPage)" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useColumnPrefs } from "@/Composables/useColumnPrefs";
import { useCrudOperations } from "@/Composables/useCrudOperations";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router } from "@inertiajs/vue3";
import { FilterMatchMode } from '@primevue/core/api';
import { ref, watch, computed } from "vue";
import { debounce } from 'lodash';
import FormField from "@/Components/Primitives/FormField.vue";
import { useDateFormatter } from "@/Composables/useDateFormatter";

defineOptions({ layout: AuthenticatedLayout, });

const props = defineProps({
  // ---- Permissions props  ----
  routeDestroy: String,
  routeMassDestroy: String,

  permissionCreate: String,
  permissionMassDelete: String,
  permissionEdit: String,
  permissionDelete: String,
  permissionView: String,

  // ---- Resource props  ----
  columns: { type: Object, required: true, },
  tableData: { type: Object, required: true, },
  inertiaKey: { type: String, default: 'tableData', },
  routeResource: { type: String, required: true, },
  emptyMessage: { type: String, default: "No records found.", },
  pageTitle: { type: String, default: "", },
  dateFilterable: { type: Array, default: () => [] },

  // ---- Toggle views props  ----
  showCreateButton: { type: Boolean, default: true, },
  showMassDeleteButton: { type: Boolean, default: true, },
  showEditButton: { type: Boolean, default: true, },
  showDeleteButton: { type: Boolean, default: true, },
  showExportButton: { type: Boolean, default: true, },
  showSearch: { type: Boolean, default: true, },
  showDateFilter: { type: Boolean, default: false, },
  showJumpToPage: { type: Boolean, default: true, },
  showToolbar: { type: Boolean, default: true, },
});


const emit = defineEmits([
  'create',        // user clicked New
  'edit',          // user clicked Edit on a row
  'afterDelete',   // optional: inform parent after delete
  'afterMassDelete'
]);

const { formatDate } = useDateFormatter()

// ---- Permissions & routes ----
const finalRouteDestroy = props.routeDestroy ?? props.routeResource + ".destroy";
const finalRouteMassDestroy = props.routeMassDestroy ?? props.routeResource + ".massDestroy";
const finalPermissionCreate = props.permissionCreate ?? props.routeResource + ".create";
const finalPermissionMassDelete = props.permissionMassDelete ?? props.routeResource + ".delete";
const finalPermissionEdit = props.permissionEdit ?? props.routeResource + ".edit";
const finalPermissionDelete = props.permissionDelete ?? props.routeResource + ".delete";
const finalPermissionView = props.permissionView ?? props.routeResource + ".view";


// ---- Table state ----
const dt = ref();
const selected = ref([]);
const isLoading = ref(false);

const sortField = ref(null)
const sortOrder = ref(null) // PrimeVue gives 1 or -1

const FILTERS_KEY = `show_filters`
const STORAGE_KEY = `selectedColumns_${props.routeResource}`;
const PER_PAGE_KEY = `per_page`;

const perPage = ref(parseInt(localStorage.getItem(PER_PAGE_KEY), 10) || 10);
watch(perPage, (v) => localStorage.setItem(PER_PAGE_KEY, String(v)));

const columnsToShow = computed(() =>
  (props.columns?.items ?? []).map((item) => ({
    label: item.header,
    value: item.field,
  }))
);

const selectedColumns = useColumnPrefs(
  STORAGE_KEY,
  columnsToShow.value.map((c) => c.value)
);

const isColumnVisible = (field) => selectedColumns.value.includes(field);

const selectedColumnDefs = computed(() =>
  columnsToShow.value.filter((c) => selectedColumns.value.includes(c.value))
);

// ---- Filter toggle ----

const showFilters = ref(
  localStorage.getItem(FILTERS_KEY) !== null
    ? localStorage.getItem(FILTERS_KEY) === 'true'
    : true
)

const toggleFilters = () => {
  showFilters.value = !showFilters.value
  localStorage.setItem(FILTERS_KEY, String(showFilters.value))
}


// ---- Date columns ----
const dateColumns = computed(() =>
  (props.columns?.items ?? [])
    .filter((col) => ["Date", "Calendar", "Timestamp"].includes(col.component_type))
    .map((col) => col.field)
);

// ---- Paging ----
const currentPage = ref(props.tableData?.current_page ?? 1);
watch(
  () => props.tableData?.current_page,
  (p) => {
    if (p) currentPage.value = p;
  }
);

// ---- Filters ----
const searchQuery = ref("");
const dateFrom = ref(null);
const dateTo = ref(null);
const selectedDateFields = ref([]);

watch(
  () => props.dateFilterable,
  (v) => {
    if (!Array.isArray(v)) return
    // keep existing selection if still valid
    selectedDateFields.value = selectedDateFields.value.filter(f => v.includes(f))
  },
  { immediate: true }
)

const filters = ref({
  'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// normalize search param for requests
const asSearchParam = (val) => {
  const t = (val ?? '').trim()
  return t === '' ? null : t
}

const toYmd = (d) => {
  if (!d) return null;
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${y}-${m}-${day}`;
};

const activeFiltersCount = computed(() => {
  let n = 0;
  if (asSearchParam(searchQuery.value)) n += 1;

  const anyDateField = selectedDateFields.value?.length > 0;
  const anyDateRange = !!dateFrom.value || !!dateTo.value;
  if (anyDateField) n += 1;
  if (anyDateRange) n += 1;

  return n;
});

const buildQueryParams = (overrides = {}) => ({
  page: props.tableData?.current_page ?? 1,
  per_page: perPage.value,
  search: asSearchParam(searchQuery.value),

  ...(sortField.value ? { sortField: sortField.value } : {}),
  ...(sortOrder.value ? { sortOrder: sortOrder.value === 1 ? "asc" : "desc" } : {}),

  date_fields: selectedDateFields.value?.length ? selectedDateFields.value : null,
  date_from: toYmd(dateFrom.value),
  date_to: toYmd(dateTo.value),
  tz: Intl.DateTimeFormat().resolvedOptions().timeZone,

  ...overrides,
});

// Debounced search (only when showSearch is enabled)
const debouncedSearch = debounce((value) => {
  router.get(
    route(`${props.routeResource}.index`),
    buildQueryParams({ page: 1, search: asSearchParam(value) }),
    { preserveState: true, preserveScroll: true, only: [props.inertiaKey], replace: true }
  );
}, 500);

watch(searchQuery, (newValue) => {
  const t = (newValue ?? "").trim();
  filters.value.global.value = t === "" ? null : t;
  if (props.showSearch) debouncedSearch(newValue);
});

// ---- Loading state ----
router.on("start", () => {
  isLoading.value = true;
});
router.on("finish", () => {
  isLoading.value = false;
});


// ---- Pagination ----
const onPage = (event) => {
  const pageNumber = event.page + 1;
  perPage.value = event.rows;
  currentPage.value = pageNumber;

  router.get(
    route(`${props.routeResource}.index`),
    buildQueryParams({ page: pageNumber }),
    { preserveState: true, preserveScroll: true, only: [props.inertiaKey], replace: true }
  );
};

const onPageButton = (pageNum) => {
  const last = props.tableData?.last_page ?? 1;
  let desired = parseInt(pageNum, 10);
  if (Number.isNaN(desired)) return;

  desired = Math.min(Math.max(desired, 1), last);
  currentPage.value = desired;

  router.get(
    route(`${props.routeResource}.index`),
    buildQueryParams({ page: desired }),
    { preserveState: true, preserveScroll: true, only: [props.inertiaKey], replace: true }
  );
};

// ---- Sorting ----
const onSort = (event) => {
  sortField.value = event.sortField;
  sortOrder.value = event.sortOrder;

  router.get(
    route(`${props.routeResource}.index`),
    buildQueryParams({ page: 1 }),
    { preserveState: true, preserveScroll: true, only: [props.inertiaKey], replace: true }
  );
};

// ---- Filtering actions ----
const applyFilters = () => {
  router.get(
    route(`${props.routeResource}.index`),
    buildQueryParams({ page: 1 }),
    { preserveState: true, preserveScroll: true, only: [props.inertiaKey], replace: true }
  );
};

const clearFilters = () => {
  searchQuery.value = "";
  dateFrom.value = null;
  dateTo.value = null;
  selectedDateFields.value = [];
  applyFilters();
};

// ---- export ----
const exportCSV = () => {
  if (dt.value) {
    dt.value.exportCSV()
  }
}

// ---- Refresh data helper ----
const refreshData = () => {
  router.reload({ only: [props.inertiaKey] })
}

// ---- CRUD helpers ----
const { deleteOne, massDelete } = useCrudOperations(props.routeResource);

const onCreateClick = () => { emit('create'); };
const onEditClick = (row) => { emit('edit', row); };

const onDeleteClick = (id) => {
  deleteOne(id, () => {
    // table reload is internal to the component
    refreshData();
    emit('afterDelete', id);
  });
};

const onMassDeleteClick = () => {
  massDelete(selected.value, () => {
    selected.value = [];
    refreshData();
    emit("afterMassDelete");
  });
};

</script>


<style lang="scss" scoped>
.table-header {
  display: flex;
  align-items: center;
  justify-content: space-between;

  @media screen and (max-width : 960px) {
    align-items: start;
  }
}
</style>