<template>
    <div>
        <ReusableDataTable resourceName="trees" :columns="columns" :tableData="treeData" inertiaKey="treeData"
            pageTitle="Manage Trees" @create="openCreateForm" @edit="openEditForm" @afterDelete="onAfterDelete"
            @afterMassDelete="onAfterMassDelete">
        </ReusableDataTable>

        <TreeForm v-model:visible="formVisible" :action="formAction" :dataRow="formRow" :speciesData="speciesData"
            @updated="reloadTable" @created="reloadTable" />

    </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref, defineOptions, defineProps } from "vue";
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import TreeForm from "@/Pages/Tree/Partials/TreeForm.vue";

defineOptions({
    layout: AuthenticatedLayout,
});

const props = defineProps({
    treeData: {
        type: Object,
        required: true,
    },
    speciesData: {
        type: Array,
        required: true,
    },
    dataColumns: {
        type: Object,
    },
});

console.log(props.dataColumns)

const { columns } = useRenamedHeaders(props.dataColumns, {
      Species_label: 'Species',
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
    router.reload({ only: ['treeData'] });
};

const onAfterDelete = () => {
    reloadTable();
};

const onAfterMassDelete = () => {
    reloadTable();
};
</script>