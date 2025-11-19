<template>
  <div>
    <ReusableDataTable routeResource="healthAssessments" :columns="columns" :tableData="tableData"
      inertiaKey="tableData" pageTitle="Health Assessments" @create="openCreateForm" @edit="openEditForm"
      @afterDelete="onAfterDelete" @afterMassDelete="onAfterMassDelete">
    </ReusableDataTable>

    <HealthAssessmentForm v-model:visible="formVisible" routeResource="healthAssessments" :action="formAction"
      :dataRow="formRow" @updated="reloadTable" @created="reloadTable" :trees="treeData" :users="userData" :healthStatuses="healthStatuses" />
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref, defineOptions, defineProps } from "vue";
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import HealthAssessmentForm from "@/Pages/HealthAssessment/Partials/HealthAssessmentForm.vue";

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
    default: () => []
  },
  userData: {
    type: Array,
    default: () => []
  },
  healthStatuses: {
    type: Array,
    default: () => []
  },

});


const { columns } = useRenamedHeaders(props.dataColumns, {
  // Events_count: 'Event Count',
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