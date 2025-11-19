<template>
    <div>
        <ReusableDataTable routeResource="trees" :columns="columns" :tableData="tableData" inertiaKey="tableData"
            pageTitle="Manage Trees" @create="openCreateForm" @edit="openEditForm" @afterDelete="onAfterDelete"
            @afterMassDelete="onAfterMassDelete">

            <template>
                <Column header="Photos">
                    <template #body="slotProps">
                        <NavLinkButton class="text-nowrap" title="Manage photos"
                            :href="route('photos.index', { tree_id: slotProps.data.id })">Manage {{
                                slotProps.data.photos_count
                            ?? 0 }} photos</NavLinkButton>
                    </template>
                </Column>
            </template>
        </ReusableDataTable>
        <TreeForm v-model:visible="formVisible" routeResource="trees" :action="formAction" :dataRow="formRow"
            :speciesData="speciesData" :neighborhoodData="neighborhoodData" :tagData="tagData" @updated="reloadTable"
            @created="reloadTable" />

    </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import { useRenamedHeaders } from "@/Composables/useRenamedHeaders";
import TreeForm from "@/Pages/Tree/Partials/TreeForm.vue";
import NavLinkButton from "@/Components/NavLinkButton.vue";

defineOptions({
    layout: AuthenticatedLayout,
});

const props = defineProps({
    tableData: {
        type: Object,
        required: true,
    },
    speciesData: {
        type: Array,
        required: true,
    },
    neighborhoodData: {
        type: Object,
        required: true,
    },
    tagData: {
        type: Object,
        required: true,
    },
    dataColumns: {
        type: Object,
        required: true,
    },
});

const { columns } = useRenamedHeaders(props.dataColumns, {
    Species_label: 'Species',
    Tags_label: 'Tags',
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