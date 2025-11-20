<template>
    <div>
        <ReusableDataTable routeResource="trees" :columns="dataColumns" :tableData="tableData" inertiaKey="tableData"
            pageTitle="Manage Trees" @create="openCreateForm" @edit="openEditForm" @afterDelete="onAfterDelete"
            @afterMassDelete="onAfterMassDelete">

            <template #columns>

                <Column field="id" header="Id" sortable>
                    <template #body="{ data }">
                        {{ data.id }}
                    </template>
                </Column>

                <Column field="species_id" header="Species" sortable>
                    <template #body="{ data }">
                        {{ speciesLabel(data) }}
                    </template>
                </Column>

                <Column field="neighborhood_id" header="Neighborhood" sortable>
                    <template #body="{ data }">
                        {{ neighborhoodLabel(data) }}
                    </template>
                </Column>

                <Column field="location" header="Location" sortable>
                    <template #body="{ data }">
                        {{ locationLabel(data) }}
                    </template>
                </Column>

                <Column field="address" header="Address" sortable>
                    <template #body="{ data }">
                        {{ data.address }}
                    </template>
                </Column>

                <Column field="planted_at" header="Planted At" sortable>
                    <template #body="{ data }">
                        {{ formatDate(data.planted_at) }}
                    </template>
                </Column>

                <Column field="status" header="Status" sortable>
                    <template #body="{ data }">
                        {{ data.status }}
                    </template>
                </Column>

                <Column field="health_status" header="Health Status" sortable>
                    <template #body="{ data }">
                        {{ data.health_status }}
                    </template>
                </Column>

                <Column field="height_m" header="Height (m)" sortable>
                    <template #body="{ data }">
                        {{ data.height_m }}
                    </template>
                </Column>

                <Column field="dbh_cm" header="DBH (cm)" sortable>
                    <template #body="{ data }">
                        {{ data.dbh_cm }}
                    </template>
                </Column>

                <Column field="canopy_diameter_m" header="Canopy Diameter (m)" sortable>
                    <template #body="{ data }">
                        {{ data.canopy_diameter_m }}
                    </template>
                </Column>


                <Column field="owner_type" header="Owner Type" sortable>
                    <template #body="{ data }">
                        {{ data.owner_type }}
                    </template>
                </Column>

                <Column field="last_inspected_at" header="Last Inspected At" sortable>
                    <template #body="{ data }">
                        {{ formatDate(data.last_inspected_at) }}
                    </template>
                </Column>

                <Column field="source" header="Source" sortable>
                    <template #body="{ data }">
                        {{ data.source }}
                    </template>
                </Column>

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
import TreeForm from "@/Pages/Tree/Partials/TreeForm.vue";
import NavLinkButton from "@/Components/NavLinkButton.vue";
import { useDateFormatter } from "@/Composables/useDateFormatter";

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

const { formatDate } = useDateFormatter();
const speciesLabel = (row) => {
    const id = row.species_id;
    const species = row.species;
    if (!id && !species) return '-';
    if (!species) return id;

    const parts = [];
    parts.push(`${species.common_name} (${species.latin_name})`);

    return parts.join(' ');
}

const neighborhoodLabel = (row) => {
    const id = row.neighborhood_id;
    const neighborhood = row.neighborhood;
    if (!id && !neighborhood) return '-';
    if (!neighborhood) return id;

    const parts = [];
    parts.push(`${neighborhood.name}, ${neighborhood.city} (${neighborhood.district})`);

    return parts.join(' ');
}

const locationLabel = (row) => {
    const lat = row.lat ?? '-';
    const lon = row.lon ?? '-';

    const parts = [];
    parts.push(`${lat}, ${lon}`);

    return parts.join(' ');
}

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