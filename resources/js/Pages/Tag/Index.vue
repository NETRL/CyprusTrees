<template>
  <div>
    <ReusableDataTable routeResource="tags" :columns="columns" :tableData="tableData" inertiaKey="tableData" pageTitle="Manage Tree Tags"
      @create="openCreateForm" @edit="openEditForm" @afterDelete="onAfterDelete" @afterMassDelete="onAfterMassDelete"
      >
    </ReusableDataTable>

      <TagForm v-model:visible="formVisible" routeResource="tags" :action="formAction" :dataRow="formRow" @updated="reloadTable"
      @created="reloadTable"/>
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import TagForm from "@/Pages/Tag/Partials/TagForm.vue";

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