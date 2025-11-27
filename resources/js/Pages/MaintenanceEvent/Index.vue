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
            {{ treeLabel(data) }}
          </template>
        </Column>
        <!-- Type-->
        <Column v-if="isColumnVisible('type_id')" field="type_id" header="Type" sortable>
          <template #body="{ data }">
            {{ typeLabel(data) }}
          </template>
        </Column>
        <!-- Performed By-->
        <Column v-if="isColumnVisible('performed_by')" field="performed_by" header="Performed By" sortable>
          <template #body="{ data }">
            {{ userLabel(data) }}
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
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import MaintenanceEventForm from "@/Pages/MaintenanceEvent/Partials/MaintenanceEventForm.vue";
import { useDateFormatter } from "@/Composables/useDateFormatter";

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

console.log(props.tableData)


const { formatDate } = useDateFormatter();

const treeLabel = (row) => {
  const id = row.tree_id;
  const tree = row.tree;
  if (!id && !tree) return '-';
  if (!tree) return id;

  const species = row.tree.species;
  const parts = [];
  parts.push(`${id} - ${species.common_name} (${species.latin_name}) ${tree.tags_label}`);

  return parts.join(' ');
}

const typeLabel = (row) => {
  const id = row.type_id;
  const type = row.type;

  if (!id && !type) return '-';

  if (!type) return id;

  return `${id} - ${type.name}`;
};

const userLabel = (row) => {
  const id = row.performed_by;
  const performer = row.performer;

  if (!id && !performer) return '-';

  if (!performer) return id;

  const roles = Array.isArray(performer.roles) ? performer.roles : [];
  const roleNames = roles.length ? roles.map(r => r.name).join(', ') : 'No role';

  const firstName = performer.first_name ?? '';
  const lastName = performer.last_name ?? '';
  const fullName = [firstName, lastName].filter(Boolean).join(' ');

  return fullName ? `${id} - ${fullName} (${roleNames})` : `${id}`;
};

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