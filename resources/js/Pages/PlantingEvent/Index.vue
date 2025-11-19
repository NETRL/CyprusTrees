<template>
  <div>
    <ReusableDataTable routeResource="plantingEvents" :columns="columns" :tableData="tableData" inertiaKey="tableData"
      pageTitle="Manage Planting Events" @create="openCreateForm" @edit="openEditForm" @afterDelete="onAfterDelete"
      @afterMassDelete="onAfterMassDelete">

      <template #columns>
        <!-- Planting-->
        <Column field="planting_id" header="Id" sortable>
          <template #body="{ data }">
            {{ data.planting_id }}
          </template>
        </Column>

        <!-- Tree -->
        <Column field="tree_id" header="Tree" sortable>
          <template #body="{ data }">
            {{ treeLabel(data) }}
          </template>
        </Column>

        <!-- Campaign: 1 - Eat all Trees (Goodies) 2025-11-18 -->
        <Column field="campaign_id" header="Campaign" sortable>
          <template #body="{ data }">
            {{ campaignLabel(data) }}
          </template>
        </Column>

        <!-- Planted By: 2 - Test User -->
        <Column field="planted_by" header="Planted By" sortable>
          <template #body="{ data }">
            {{ plantedByLabel(data) }}
          </template>
        </Column>

        <!-- Planted At -->
        <Column field="planted_at" header="Planted At" sortable>
          <template #body="{ data }">
            {{ formatDate(data.planted_at) }}
          </template>
        </Column>

        <!-- Method -->
        <Column field="method" header="Method" sortable>
          <template #body="{ data }">
            {{ data.method }}
          </template>
        </Column>

        <!-- Notes -->
        <Column field="notes" header="Notes">
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
import { ref, defineOptions, defineProps } from "vue";
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import PlantingEventForm from "@/Pages/PlantingEvent/Partials/PlantingEventForm.vue";
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

const plantedByLabel = (row) => {
  const id = row.planted_by;
  const planter = row.planter;

  if (!id && !planter) return '-';

  if (!planter) return id;

  const firstName = planter.first_name ?? '';
  const lastName = planter.last_name ?? '';
  const fullName = [firstName, lastName].filter(Boolean).join(' ');

  return fullName ? `${id} - ${fullName}` : `${id}`;
};

const campaignLabel = (row) => {
  const id = row.campaign_id;
  const campaign = row.campaign;
  if (!id && !campaign) return '-';
  if (!campaign) return id;

  const parts = [];
  parts.push(`${id} - ${campaign.name}`);         // "1 - Eat all Trees"
  if (campaign.sponsor) {
    parts.push(`(${campaign.sponsor})`);          // "(Goodies)"
  }
  if (campaign.start_date) {
    parts.push(formatDate(campaign.start_date));  // "18/11/2025"
  }

  return parts.join(' ');
};


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