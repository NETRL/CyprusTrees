<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">

        <Toolbar class="m-4 dark:border-gray-800! dark:bg-transparent! ">
            <template #start>
                <Button v-has-permission="{ props: $page.props, permissions: ['trees.create'] }" severity="success"
                    class="mr-2 mb-2 max-sm:text-sm!" icon="pi pi-plus" label="New" @click="createNewResource()" />
                <Button v-has-permission="{ props: $page.props, permissions: ['trees.delete'] }" severity="danger"
                    class="mb-2 max-sm:text-sm!" :disabled="!selected || !selected.length" icon="pi pi-trash"
                    label="Delete" @click="massDeleteResource()" />
            </template>

            <template #end>
                <Button class="mb-2 max-sm:text-sm!" severity="help" icon="pi pi-upload" label="Export"
                    @click="exportCSV($event)" />
            </template>
        </Toolbar>
        <div>
            <DataTable class="m-4 rounded-xl border border-gray-200  dark:border-gray-800" ref="dt"
                v-model:selection="selected" :filters="filters" :value="treeData.data" :lazy="true" :paginator="true"
                :rows="treeData.per_page" :totalRecords="treeData.total"
                :first="(treeData.current_page - 1) * treeData.per_page" :rowsPerPageOptions="[25, 50, 100]"
                responsiveLayout="scroll" @page="onPage">
                <template #header>
                    <div class="table-header flex flex-column md:flex-row md:justiify-content-between">
                        <h5 class="mb-2 md:m-0 p-as-md-center">Manage Trees</h5>
                        <InputText v-model="filters['global'].value" class="p-inputtext-sm max-md:w-full"
                            placeholder="Search..." />
                    </div>
                </template>
                <template #empty>
                    <div class="flex py-4 lg:text-lg">
                        No trees found.
                    </div>
                </template>
                <Column :exportable="false" selectionMode="multiple" style="width: 3rem"></Column>
                <Column :sortable="true" field="id" header="Id"></Column>
                <Column :sortable="true" field="species_id" header="Species"></Column>
                <Column :sortable="true" field="neighborhood_id" header="Neighborhood"></Column>
                <Column :sortable="true" field="lat" header="Latitude"></Column>
                <Column :sortable="true" field="lon" header="Longitude"></Column>
                <Column :sortable="true" field="address" header="address"></Column>
                <Column :sortable="true" field="planted_at" header="Planted At">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.planted_at) }}
                    </template>
                </Column>
                <Column :sortable="true" field="last_inspected_at" header="Last Inspected At">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.last_inspected_at) }}
                    </template>
                </Column>
                <Column :sortable="true" field="status" header="Status"></Column>
                <Column :sortable="true" field="health_status" header="Health Status"></Column>
                <Column :sortable="true" field="height_m" header="Height (m)"></Column>
                <Column :sortable="true" field="dbh_cm" header="DBH (cm)"></Column>
                <Column :sortable="true" field="canopy_diameter_m" header="Canopy (m)"></Column>
                <Column :sortable="true" field="owner_type" header="Owner Type"></Column>
                <Column :sortable="true" field="source" header="source"></Column>
                <Column :exportable="false">
                    <template #body="slotProps">
                        <Button v-has-permission="{ props: $page.props, permissions: ['trees.edit'] }"
                            class="p-button-rounded mr-2 max-sm:text-sm! my-1" severity="primary"
                            icon="pi pi-pencil" @click="editResource(slotProps.data)" />
                        <Button v-has-permission="{ props: $page.props, permissions: ['trees.delete'] }"
                            class="p-button-rounded max-sm:text-sm!" severity="danger" icon="pi pi-trash" iconPos="left"
                            @click="deleteResource(slotProps.data.id)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <TreeForm v-model:visible="formVisible" :action="action" :dataRow="dataRow" :speciesData="speciesData" class="dark:bg-gray-900!" />
    </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TreeForm from "@/Pages/Tree/Partials/TreeForm.vue";
import { ref, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { FilterMatchMode } from '@primevue/core/api';
import { useConfirm } from "primevue";
import { defineOptions, defineProps } from "vue";



defineOptions({
    layout: AuthenticatedLayout,
});

defineProps({
    treeData: {
        type: Object,
        required: true,
    },
    speciesData: {
        type: Array,
        required: true,
    }
});

const dt = ref();
const selected = ref([]);
const dataRow = ref(null);
const formVisible = ref(false);
const action = ref("");
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});


const confirm = useConfirm();
const page = usePage();

// Format date helper function
const formatDate = (dateString) => {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '-';
    
    return date.toLocaleDateString('el-GR'); 
};

const onPage = (event) => {
    const pageNumber = event.page + 1 // DataTable is 0-based, Laravel is 1-based
    const perPage = event.rows

    router.get(
        route('trees.index'),
        { page: pageNumber, per_page: perPage },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}

const exportCSV = () => {
    if (dt.value) {
        dt.value.exportCSV()
    }
}


const deleteResource = (id) => {
    confirm.require({
        message: 'Are you sure you want to delete this dataRow?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(route('trees.destroy', id))
        },
        reject: () => { }
    });
}
const createNewResource = () => {
    dataRow.value = null
    action.value = "Create"
    formVisible.value = true;
}
const editResource = (row) => {
    dataRow.value = row;
    action.value = "Edit"
    formVisible.value = true;
}

const massDeleteResource = () => {
    confirm.require({
        message: 'Are you sure you want to delete all this trees?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            router.post(route('trees.massDestroy'), {
                trees: selected.value,
            },
                {
                    onSuccess: () => {
                        this.selected = [];
                    },
                })
        },
        reject: () => { }
    });
}
</script>

<style lang="scss" scoped>
.table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    @media screen and (max-width : 960px) {
        align-items: start;
    }
}
</style>