<template>
  <div>
    <ReusableDataTable routeResource="maintenanceEvents" :columns="dataColumns" :tableData="tableData"
      inertiaKey="tableData" pageTitle="Manage Maintenance Events" @create="openCreateForm" @edit="openEditForm"
      @afterDelete="onAfterDelete" @afterMassDelete="onAfterMassDelete">


      <template #columns="{ isColumnVisible }">
        <!-- Event-->
        <Column v-if="isColumnVisible('event_id')" field="event_id" header="Id" sortable>
          <template #body="{ data }">
            {{ data.event_id }}
          </template>
        </Column>
        <!-- Tree-->
        <Column v-if="isColumnVisible('tree_id')" field="tree_id" header="Tree" sortable>
          <template #body="{ data }">
            <Link :href="route('/', { tree_id: data.tree_id })"
              class="flex items-center spece-x-2 hover:cursor-pointer hover:text-brand-600">
            {{ data.tree_label }}
            <ExternalLink class="w-3.5 h-3.5 mx-1" />
            </Link>
          </template>
        </Column>
        <!-- Type-->
        <Column v-if="isColumnVisible('type_id')" field="type_id" header="Type" sortable>
          <template #body="{ data }">
            {{ data.type_label }}
          </template>
        </Column>
        <!-- Performed By-->
        <Column v-if="isColumnVisible('performed_by')" field="performed_by" header="Performed By" sortable>
          <template #body="{ data }">
            {{ data.performer_label }}
          </template>
        </Column>
        <!-- Performed At-->
        <Column v-if="isColumnVisible('performed_at')" field="performed_at" header="Performed At" sortable>
          <template #body="{ data }">
            {{ formatDate(data.performed_at) }}
          </template>
        </Column>
        <!-- Quantity-->
        <Column v-if="isColumnVisible('quantity')" field="quantity" header="Quantity" sortable>
          <template #body="{ data }">
            {{ data.quantity }}
          </template>
        </Column>
        <!-- Cost-->
        <Column v-if="isColumnVisible('cost')" field="cost" header="Cost" sortable>
          <template #body="{ data }">
            {{ data.cost }}
          </template>
        </Column>
        <!-- Notes-->
        <Column v-if="isColumnVisible('notes')" field="notes" header="Notes" sortable>
          <template #body="{ data }">
            {{ data.notes }}
          </template>
        </Column>
      </template>

    </ReusableDataTable>

    <MaintenanceEventForm v-model:visible="formVisible" routeResource="maintenanceEvents" :action="formAction"
      :dataRow="formRow" @updated="reloadTable" @created="reloadTable" :trees="treeData" :types="typeData"
      :users="userData" />
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref, defineOptions, defineProps } from "vue";
import MaintenanceEventForm from "@/Pages/MaintenanceEvent/Partials/MaintenanceEventForm.vue";
import { useDateFormatter } from "@/Composables/useDateFormatter";
import { ExternalLink } from "lucide-vue-next";

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
  typeData: {
    type: Array,
    default: () => [],
  },
  userData: {
    type: Array,
    default: () => [],
  },

});

const { formatDate } = useDateFormatter();

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