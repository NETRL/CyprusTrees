<template>
  <div>
    <ReusableDataTable routeResource="plantingEvents" :columns="columns" :tableData="tableData" inertiaKey="tableData"
      pageTitle="Manage Planting Events" @create="openCreateForm" @edit="openEditForm" @afterDelete="onAfterDelete"
      @afterMassDelete="onAfterMassDelete">

      <template #columns="{ isColumnVisible }">
        <!-- Planting-->
        <Column v-if="isColumnVisible('planting_id')" field="planting_id" header="Id" sortable>
          <template #body="{ data }">
            {{ data.planting_id }}
          </template>
        </Column>

        <!-- Tree -->
        <Column v-if="isColumnVisible('tree_id')" field="tree_id" header="Tree" sortable>
          <template #body="{ data }">
            <Link :href="route('/', { tree_id: data.tree_id })"
              class="flex items-center spece-x-2 hover:cursor-pointer hover:text-brand-600">
            {{ data.tree_label }}
            <ExternalLink class="w-3.5 h-3.5 mx-1" />
            </Link>
          </template>
        </Column>

        <!-- Campaign: 1 - Eat all Trees (Goodies) 2025-11-18 -->
        <Column v-if="isColumnVisible('campaign_id')" field="campaign_id" header="Campaign" sortable>
          <template #body="{ data }">
            {{ data.campaign_label ?? '-' }}
          </template>
        </Column>

        <!-- Planted By: 2 - Test User -->
        <Column v-if="isColumnVisible('planted_by')" field="planted_by" header="Planted By" sortable>
          <template #body="{ data }">
            {{ data.planter_label }}
          </template>
        </Column>

        <!-- Planted At -->
        <Column v-if="isColumnVisible('planted_at')" field="planted_at" header="Planted At" sortable>
          <template #body="{ data }">
            {{ formatDate(data.planted_at) }}
          </template>
        </Column>

        <!-- Method -->
        <Column v-if="isColumnVisible('method')" field="method" header="Method" sortable>
          <template #body="{ data }">
            {{ data.method }}
          </template>
        </Column>

        <!-- Notes -->
        <Column v-if="isColumnVisible('notes')" field="notes" header="Notes">
          <template #body="{ data }">
            {{ data.notes }}
          </template>
        </Column>
      </template>

    </ReusableDataTable>

    <PlantingEventForm v-model:visible="formVisible" routeResource="plantingEvents" :action="formAction"
      :trees="treeData" :campaigns="campaignData" :users="userData" :dataRow="formRow" @updated="reloadTable"
      @created="reloadTable" />
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import PlantingEventForm from "@/Pages/PlantingEvent/Partials/PlantingEventForm.vue";
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
  campaignData: {
    type: Array,
    default: () => [],
  },
  userData: {
    type: Array,
    default: () => [],
  }
});

const { formatDate } = useDateFormatter();

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