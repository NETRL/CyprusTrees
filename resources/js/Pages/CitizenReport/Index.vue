<template>
  <div>
    <ReusableDataTable routeResource="citizenReports" :columns="dataColumns" :tableData="tableData"
      inertiaKey="tableData" pageTitle="Manage Citizen Reports" @create="openCreateForm" @edit="openEditForm"
      @afterDelete="onAfterDelete" @afterMassDelete="onAfterMassDelete">


      <template #columns="{ isColumnVisible}">
        <!-- ID-->
        <Column v-if="isColumnVisible('report_id')" field="report_id" header="Id" sortable>
          <template #body="{ data }">
            {{ data.report_id }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('type_id')" field="type_id" header="Report Type" sortable>
          <template #body="{ data }">
            {{ typeLabel(data) }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('created_by')" field="created_by" header="Created By" sortable>
          <template #body="{ data }">
            {{ userLabel(data) }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('tree_id')" field="tree_id" header="Tree" sortable>
          <template #body="{ data }">
            {{ treeLabel(data) }}
          </template>
        </Column>


        <Column v-if="isColumnVisible('lat')" field="lat" header="Lat" sortable>
          <template #body="{ data }">
            {{ data.lat }}
          </template>
        </Column>


        <Column v-if="isColumnVisible('lon')" field="lon" header="Lon" sortable>
          <template #body="{ data }">
            {{ data.lon }}
          </template>
        </Column>


        <Column v-if="isColumnVisible('description')" field="description" header="Description" sortable>
          <template #body="{ data }">
            {{ data.description }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('status')" field="status" header="Status" sortable>
          <template #body="{ data }">
            {{ data.status }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('photo_url')" field="photo_url" header="Photo Url" sortable>
          <template #body="{ data }">
            {{ data.photo_url }}
          </template>
        </Column>


        <Column v-if="isColumnVisible('created_at')" field="created_at" header="Created At" sortable>
          <template #body="{ data }">
            {{ formatDate(data.created_at) }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('resolved_at')" field="resolved_at" header="Resolved At" sortable>
          <template #body="{ data }">
            {{ formatDate(data.resolved_at) }}
          </template>
        </Column>

      </template>

    </ReusableDataTable>

    <CitizenReportForm v-model:visible="formVisible" routeResource="citizenReports" :action="formAction"
      :dataRow="formRow" @updated="reloadTable" @created="reloadTable" :trees="treeData" :types="typeData"
      :users="userData" :reportStatus="reportStatus"/>
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref, defineOptions, defineProps } from "vue";
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import CitizenReportForm from "@/Pages/CitizenReport/Partials/CitizenReportForm.vue";
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
  reportStatus: {
    type: Array,
    default: () => [],
  }

});

console.log(props.tableData.data)


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