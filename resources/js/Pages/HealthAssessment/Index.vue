<template>
  <div>
    <ReusableDataTable routeResource="healthAssessments" :columns="dataColumns" :tableData="tableData"
      inertiaKey="tableData" pageTitle="Health Assessments" @create="openCreateForm" @edit="openEditForm"
      @afterDelete="onAfterDelete" @afterMassDelete="onAfterMassDelete">

      <template #columns="{ isColumnVisible }">
        <!-- ID -->
        <Column v-if="isColumnVisible('assessment_id')" field="assessment_id" header="Id" sortable>
          <template #body="{ data }">
            {{ data.assessment_id }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('tree_id')" field="tree_id" header="Tree" sortable>
          <template #body="{ data }">
            <Link :href="route('/', { tree_id: data.tree_id })"
              class="flex items-center spece-x-2 hover:cursor-pointer hover:text-brand-600">
            {{ data.tree_label }}
            <ExternalLink class="w-3.5 h-3.5 mx-1" />
            </Link>
          </template>
        </Column>

        <Column v-if="isColumnVisible('assessed_by')" field="assessed_by" header="Assessed By" sortable>
          <template #body="{ data }">
            {{ data.assessor_label }}
          </template>
        </Column>


        <Column v-if="isColumnVisible('health_status')" field="health_status" header="Health Status" sortable>
          <template #body="{ data }">
            {{ data.health_label }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('pests_diseases')" field="pests_diseases" header="Pests & Diseases" sortable>
          <template #body="{ data }">
            {{ data.pests_diseases ?? '-' }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('risk_score')" field="risk_score" header="Risk Score" sortable>
          <template #body="{ data }">
            {{ data.risk_score ?? '-' }}
          </template>
        </Column>

        <Column v-if="isColumnVisible('actions_recommended')" field="actions_recommended" header="Actions Recommended"
          sortable>
          <template #body="{ data }">
            {{ data.actions_recommended ?? '-' }}
          </template>
        </Column>


      </template>

    </ReusableDataTable>

    <HealthAssessmentForm v-model:visible="formVisible" routeResource="healthAssessments" :action="formAction"
      :dataRow="formRow" @updated="reloadTable" @created="reloadTable" :trees="treeData" :users="userData"
      :healthStatus="healthStatus" />
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref, defineOptions, defineProps } from "vue";
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import HealthAssessmentForm from "@/Pages/HealthAssessment/Partials/HealthAssessmentForm.vue";
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
    default: () => []
  },
  userData: {
    type: Array,
    default: () => []
  },
  healthStatus: {
    type: Array,
    default: () => []
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