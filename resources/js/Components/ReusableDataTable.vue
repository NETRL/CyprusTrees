<template>
  <div>
    <Toolbar class="m-4 dark:border-gray-800! dark:bg-transparent! ">
      <template #start>
        <Button v-if="showCreateButton" v-has-permission="{ props: $page.props, permissions: [finalPermissionCreate] }"
          severity="success" class="mr-2 mb-2 max-sm:text-sm!" icon="pi pi-plus" label="New" @click="onCreateClick" />
        <Button v-if="showMassDeleteButton"
          v-has-permission="{ props: $page.props, permissions: [finalPermissionDelete] }" severity="danger"
          class="mb-2 max-sm:text-sm!" :disabled="!selected || !selected.length" icon="pi pi-trash" label="Delete"
          @click="onMassDeleteClick" />
        <!-- Toobar start slot -->
        <slot name="toolbarStart"></slot>
      </template>

      <template #end>
        <!-- Toobar end slot -->
        <slot name="toolbarEnd"></slot>
        <Button v-if="showExportButton" class="mb-2 max-sm:text-sm!" severity="help" icon="pi pi-upload" label="Export"
          @click="exportCSV($event)" />
      </template>
    </Toolbar>
    <div class=" m-4 text-left ">

      <FormField component="MultiSelect" v-model="selectedColumns" :options="columnsToShow" optionLabel="label"
        optionValue="value" :maxSelectedLabels="1" filter placeholder="Select columns" />
    </div>

    <div>
      <DataTable class="m-4 rounded-xl border border-gray-200  dark:border-gray-800" ref="dt"
        v-model:selection="selected" :filters="filters" :value="tableData.data" :lazy="true" :paginator="true"
        :rows="perPage" :totalRecords="tableData.total" :first="(tableData.current_page - 1) * tableData.per_page"
        :rowsPerPageOptions="[5, 10, 25, 50, 100]" responsiveLayout="scroll" :loading="isLoading" @page="onPage"
        @sort="onSort">
        <template #header>
          <div class="table-header flex flex-column md:flex-row">
            <h5 class="mb-2 md:m-0 p-as-md-center">{{ pageTitle }}</h5>
            <InputText v-if="showSearch" v-model="searchQuery" class="p-inputtext-sm max-md:w-full"
              placeholder="Search..." />
          </div>
        </template>
        <template #empty>
          {{ emptyMessage }}
        </template>

        <Column :exportable="false" selectionMode="multiple" style="width: 3rem"></Column>

        <!-- Dynamic visible columns -->
        <Column v-for="col in selectedColumnDefs" :key="col.value" :field="col.value" :header="col.label"
          :sortable="true">

          <!-- for date columns -->
          <template #body="slotProps" v-if="dateColumns.includes(col.value)">
            {{ formatDate(slotProps.data[col.value]) }}
          </template>

          <!-- default (non-date columns) -->
          <template #body="slotProps" v-else>
            {{ slotProps.data[col.value] }}
          </template>
        </Column>
        
        <slot></slot>
        <!-- default slot -->
        <Column :exportable="false">
          <template #body="slotProps">
            <Button v-if="showEditButton" v-has-permission="{ props: $page.props, permissions: [finalPermissionEdit] }"
              class="p-button-rounded mr-2 max-sm:text-sm! my-1" severity="primary" icon="pi pi-pencil"
              @click="onEditClick(slotProps.data)" />
            <Button v-if="showDeleteButton"
              v-has-permission="{ props: $page.props, permissions: [finalPermissionDelete] }"
              class="p-button-rounded max-sm:text-sm!" severity="danger" icon="pi pi-trash" iconPos="left"
              @click="onDeleteClick(slotProps.data.id)" />
          </template>
        </Column>
        <slot name="tableEnd"></slot>
      </DataTable>
      <div v-if="showJumpToPage" class="m-4">
        {{ __('Jump to page') }}
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

defineOptions({
  layout: AuthenticatedLayout,
});

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
  columns: {
    type: Object,
    required: true,
  },
  tableData: {
    type: Object,
    required: true,
  },
  inertiaKey: {
    type: String,
    default: 'tableData',
  },
  routeResource: {
    type: String,
    required: true,
  },
  emptyMessage: {
    type: String,
    default: "No records found.",
  },
  pageTitle: {
    type: String,
    default: "",
  },

  // ---- Toggle views props  ----
  showCreateButton: {
    type: Boolean,
    default: true,
  },
  showMassDeleteButton: {
    type: Boolean,
    default: true,
  },
  showEditButton: {
    type: Boolean,
    default: true,
  },
  showDeleteButton: {
    type: Boolean,
    default: true,
  },
  showExportButton: {
    type: Boolean,
    default: true,
  },
  showSearch: {
    type: Boolean,
    default: true,
  },
  showJumpToPage: {
    type: Boolean,
    default: true,
  },
});

const { formatDate } = useDateFormatter()

const emit = defineEmits([
  'create',        // user clicked New
  'edit',          // user clicked Edit on a row
  'afterDelete',   // optional: inform parent after delete
  'afterMassDelete'
]);

const finalRouteDestroy = props.routeDestroy ?? props.routeResource + ".destroy";
const finalRouteMassDestroy = props.routeMassDestroy ?? props.routeResource + ".massDestroy";
const finalPermissionCreate = props.permissionCreate ?? props.routeResource + ".create";
const finalPermissionMassDelete = props.permissionMassDelete ?? props.routeResource + ".delete";
const finalPermissionEdit = props.permissionEdit ?? props.routeResource + ".edit";
const finalPermissionDelete = props.permissionDelete ?? props.routeResource + ".delete";
const finalPermissionView = props.permissionView ?? props.routeResource + ".view";

const STORAGE_KEY = `selectedColumns_` + props.routeResource
const PER_PAGE_KEY = props.routeResource + `_per_page`

const columnsToShow = computed(() =>
  props.columns.items.map(item => ({
    label: item.header,
    value: item.field,
  }))
);

const selectedColumns = useColumnPrefs(
  STORAGE_KEY,
  columnsToShow.value.map(c => c.value)
);

const selectedColumnDefs = computed(() =>
  columnsToShow.value.filter(c =>
    selectedColumns.value.includes(c.value)
  )
);

const currentPage = ref(props.tableData?.current_page ?? 1)
watch(
  () => props.tableData?.current_page,
  (p) => { if (p) currentPage.value = p }
)

const dt = ref();
const selected = ref([]);
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

// ---- Dedcide if the column is a Date/Calendar/Timestamp.
const dateColumns = computed(() =>
  props.columns.items
    .filter(col => ['Date', 'Calendar', 'Timestamp'].includes(col.component_type))
    .map(col => col.field)
);


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
    route(props.routeResource + '.index'),
    {
      search: asSearchParam(value),
      page: 1,
      per_page: perPage.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      only: [props.inertiaKey],
      replace: true,
    }
  )
}, 500)


const onPageButton = (pageNum) => {
  const last = props.tableData?.last_page ?? 1
  let desired = parseInt(pageNum, 10)
  if (isNaN(desired)) return
  desired = Math.min(Math.max(desired, 1), last)
  if (desired === props.tableData?.current_page) return

  currentPage.value = desired
  router.get(
    route(props.routeResource + '.index'),
    {
      page: desired,
      per_page: perPage.value,
      search: asSearchParam(searchQuery.value),
    },
    { preserveState: true, preserveScroll: true, only: [props.inertiaKey] }
  )
}


// ---- pagination ----
const onPage = (event) => {
  const pageNumber = event.page + 1
  perPage.value = event.rows // update the ref so localStorage watcher runs
  currentPage.value = pageNumber

  router.get(
    route(props.routeResource + '.index'),
    {
      page: pageNumber,
      per_page: perPage.value,
      search: asSearchParam(searchQuery.value),
    },
    {
      preserveState: true,
      preserveScroll: true,
      only: [props.inertiaKey],
    }
  )
}

// ---- Sorting ----
const onSort = (event) => {
  router.get(
    route(props.routeResource + '.index'),
    {
      sortField: event.sortField,
      sortOrder: event.sortOrder === 1 ? 'asc' : 'desc',
      page: 1,
      per_page: perPage.value,
      search: asSearchParam(searchQuery.value),
    },
    { preserveState: true, preserveScroll: true, only: [props.inertiaKey] }
  )
}

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

const { deleteOne, massDelete } = useCrudOperations(props.routeResource);

// ---- CRUD helpers ----

const onCreateClick = () => {
  emit('create');
};

const onEditClick = (row) => {
  emit('edit', row);
};

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
    emit('afterMassDelete');
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