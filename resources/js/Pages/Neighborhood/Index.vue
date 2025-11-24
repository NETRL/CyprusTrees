<template>
  <div>
    <ReusableDataTable routeResource="neighborhoods" :columns="columns" :tableData="tableData" inertiaKey="tableData"
      pageTitle="Manage Neigborhoods" @create="openCreateForm" @edit="openEditForm" @afterDelete="onAfterDelete"
      @afterMassDelete="onAfterMassDelete">

      <!-- default slot for extra columns -->
      <template>
        <Column header="GeoJSON File">
          <template #body="slotProps">
            <!-- if file exists: show remove button -->
            <Button v-if="slotProps.data.has_geojson" class="text-nowrap! bg-white! text-gray-500! transition-colors!
           hover:bg-red-100! hover:text-red-700! border-gray-200!
           dark:border-gray-800! dark:bg-gray-900! dark:text-gray-400! dark:hover:bg-red-900! dark:hover:text-white!"
              severity="danger" @click="removeFile(slotProps.data)">
              Remove File
            </Button>
            <!-- if file doesn't exist: show upload button -->
            <Button v-else class="text-nowrap! bg-white! text-gray-500! transition-colors!
           hover:bg-brand-100! hover:text-brand-700! border-gray-200!
           dark:border-gray-800! dark:bg-gray-900! dark:text-gray-400! dark:hover:bg-brand-800! dark:hover:text-white!"
              @click="uploadFile(slotProps.data)">
              Upload File
            </Button>
          </template>
        </Column>

      </template>
    </ReusableDataTable>

    <!-- hidden file input used for all rows -->
    <input ref="fileInput" type="file" class="hidden" accept=".json,application/geo+json" @change="onFileSelected" />

    <NeighborhoodForm v-model:visible="formVisible" routeResource="neighborhoods" :action="formAction"
      :dataRow="formRow" @updated="reloadTable" @created="reloadTable" />
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref, defineOptions, defineProps, computed, watch, reactive } from "vue";
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import NeighborhoodForm from "@/Pages/Neighborhood/Partials/NeighborhoodForm.vue";



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
});

const { columns } = useRenamedHeaders(props.dataColumns, {
  Trees_count: 'Tree Count',
})

// --- form state ---
const formVisible = ref(false);
const formAction = ref('');      // 'Create' or 'Edit'
const formRow = ref(null);       // current row

// --- file upload state ---
const fileInput = ref(null);
const selectedNeighborhood = ref(null);

const openCreateForm = () => {
  formRow.value = null;
  formAction.value = 'Create';
  formVisible.value = true;
};

const openEditForm = (row) => {
  formRow.value = row;
  formAction.value = 'Edit';
  formVisible.value = true;
};

// optional: reload table when form finishes
const reloadTable = () => {
  router.reload({ only: ['tableData'] });
};

const onAfterDelete = () => {
  reloadTable();
};

const onAfterMassDelete = () => {
  reloadTable();
};

// Trigger file input for a given row
const uploadFile = (row) => {
  selectedNeighborhood.value = row; // row is slotProps.data
  if (fileInput.value) {
    fileInput.value.click();
  }
};


// Handle selected file
const onFileSelected = (event) => {
  const file = event.target.files[0];
  const neighborhood = selectedNeighborhood.value;

  if (!file || !neighborhood) {
    event.target.value = null; // reset
    return;
  }

  const formData = new FormData();
  formData.append("neighborhood_id", neighborhood.id);
  formData.append("geojson_file", file);

  router.post(route("neighborhoods.uploadFile"), formData, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      reloadTable();
    },
    onFinish: () => {
      // reset input + state
      event.target.value = null;
      selectedNeighborhood.value = null;
    },
  });
};

// Remove file for a given row
const removeFile = (row) => {
  router.post(
    route("neighborhoods.removeFile"),
    { neighborhood_id: row.id },
    {
      preserveScroll: true,
      onSuccess: () => {
        reloadTable();
      },
    }
  );
};

</script>