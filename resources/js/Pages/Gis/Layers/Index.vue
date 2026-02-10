<template>
  <div>
    <ReusableDataTable routeResource="gisLayers" :columns="dataColumns" :tableData="tableData"
      inertiaKey="tableData" pageTitle="Manage Layers" :showMassDeleteButton="false"  @create="openCreateForm" @edit="openEditForm" 
      @afterDelete="onAfterDelete">
    </ReusableDataTable>

    <GisLayerForm v-model:visible="formVisible" routeResource="gisLayers" :action="formAction"
      :dataRow="formRow"/>
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import GisLayerForm from "@/Pages/Gis/Layers/Partials/GisLayerForm.vue";

defineOptions({
  layout: AuthenticatedLayout,
});

const props = defineProps({
  tableData: { type: Object, required: true, },
  dataColumns: { type: Object, },
});

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

</script>