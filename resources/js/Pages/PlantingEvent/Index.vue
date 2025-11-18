<template>
  <div>
    <ReusableDataTable routeResource="plantingEvents" :columns="columns" :tableData="tableData" inertiaKey="tableData"
      pageTitle="Manage Planting Events" @create="openCreateForm" @edit="openEditForm" @afterDelete="onAfterDelete"
      @afterMassDelete="onAfterMassDelete">
    </ReusableDataTable>

    <PlantingEventForm v-model:visible="formVisible" routeResource="plantingEvents" :action="formAction"
      :trees="treeData" :campaigns="campaignData" :users="userData" :dataRow="formRow" @updated="reloadTable" @created="reloadTable" />
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref, defineOptions, defineProps } from "vue";
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import PlantingEventForm from "@/Pages/PlantingEvent/Partials/PlantingEventForm.vue";

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
  treeData: {
    type: Array,
    default: () => [],
  },
  campaignData: {
    type: Array,
    default: () => [],
  },
  userData: {
    type: Array,
    default: () => [],
  }
});

console.log(props.dataColumns)

const { columns } = useRenamedHeaders(props.dataColumns, {
  Trees_count: 'Tree Count',
})

// --- form state ---
const formVisible = ref(false);
const formAction = ref('');      // 'Create' or 'Edit'
const formRow = ref(null);       // current row

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
</script>