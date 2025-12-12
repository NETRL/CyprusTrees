<template>
    <div>
        <ReusableDataTable routeResource="trees" :columns="dataColumns" :tableData="tableData" inertiaKey="tableData"
            pageTitle="Manage Trees" @create="openCreateForm" @edit="openEditForm" @afterDelete="onAfterDelete"
            @afterMassDelete="onAfterMassDelete">
            <template #columns="{ isColumnVisible }">
                <Column v-if="isColumnVisible('id')" field="id" header="Id" sortable>
                    <template #body="{ data }">
                        {{ data.id }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('species')" field="species_id" header="Species" sortable>
                    <template #body="{ data }">
                        {{ data.species_label }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('neighborhood')" field="neighborhood_id" header="Neighborhood" sortable>
                    <template #body="{ data }">
                        {{ data.neighborhood_label }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('location')" field="location" header="Location" sortable>
                    <template #body="{ data }">
                        <Link :href="route('/', { tree_id: data.id })"
                            class="flex justify-start items-center spece-x-2 hover:cursor-pointer hover:text-brand-600">
                        {{ data.location_label }}
                        <ExternalLink class="w-3.5 h-3.5 mx-1" />
                        </Link>
                    </template>
                </Column>

                <Column v-if="isColumnVisible('address')" field="address" header="Address" sortable>
                    <template #body="{ data }">
                        {{ data.address }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('planted_at')" field="planted_at" header="Planted At" sortable>
                    <template #body="{ data }">
                        {{ formatDate(data.planted_at) }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('status')" field="status" header="Status" sortable>
                    <template #body="{ data }">
                        {{ data.status_label }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('health_status')" field="health_status" header="Health Status" sortable>
                    <template #body="{ data }">
                        {{ data.health_label }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('sex')" field="sex" header="Sex" sortable>
                    <template #body="{ data }">
                        {{ data.sex_label }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('height_m')" field="height_m" header="Height (m)" sortable>
                    <template #body="{ data }">
                        {{ data.height_m }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('dbh_cm')" field="dbh_cm" header="DBH (cm)" sortable>
                    <template #body="{ data }">
                        {{ data.dbh_cm }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('canopy_diameter_m')" field="canopy_diameter_m"
                    header="Canopy Diameter (m)" sortable>
                    <template #body="{ data }">
                        {{ data.canopy_diameter_m }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('owner_type')" field="owner_type" header="Owner Type" sortable>
                    <template #body="{ data }">
                        {{ data.owner_type_label }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('last_inspected_at')" field="last_inspected_at" header="Last Inspected At"
                    sortable>
                    <template #body="{ data }">
                        {{ formatDate(data.last_inspected_at) }}
                    </template>
                </Column>

                <Column v-if="isColumnVisible('source')" field="source" header="Source" sortable>
                    <template #body="{ data }">
                        {{ data.source }}
                    </template>
                </Column>

                <!-- Photos column: likely always visible & not in MultiSelect -->
                <Column header="Photos">
                    <template #body="slotProps">
                        <NavLinkButton class="text-nowrap" title="Manage photos"
                            :href="route('photos.index', { tree_id: slotProps.data.id })">
                            Manage {{ slotProps.data.photos_count ?? 0 }} photos
                        </NavLinkButton>
                    </template>
                </Column>
            </template>
        </ReusableDataTable>

        <TreeForm v-model:visible="formVisible" routeResource="trees" :action="formAction" :dataRow="formRow"
            :speciesData="speciesData" :neighborhoodData="neighborhoodData" :tagData="tagData" @updated="reloadTable"
            @created="reloadTable" :treeSex="treeSex" :healthStatus="healthStatus" :treeStatus="treeStatus"
            :ownerType="ownerType" />

    </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ReusableDataTable from "@/Components/ReusableDataTable.vue";
import { router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import TreeForm from "@/Pages/Tree/Partials/TreeForm.vue";
import NavLinkButton from "@/Components/NavLinkButton.vue";
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
    treeSex: {
        type: Array,
        default: () => [],
    },
    healthStatus: {
        type: Array,
        default: () => [],
    },
    treeStatus: {
        type: Array,
        default: () => [],
    },
    ownerType: {
        type: Array,
        default: () => [],
    }
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