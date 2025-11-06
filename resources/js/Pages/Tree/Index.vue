<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
        <Toolbar class="m-4  dark:border-gray-800! dark:bg-transparent! ">
            <template #start>
                <!-- <Button v-has-permission="{ props: $page.props, permissions: ['trees.create'] }" severity="success"
                    class="mr-2 max-sm:text-sm!" icon="pi pi-plus" label="New" @click="createNewResource()" />
                <Button v-has-permission="{ props: $page.props, permissions: ['trees.delete'] }" severity="danger"
                    :disabled="!selected || !selected.length" icon="pi pi-trash" label="Delete" class="max-sm:text-sm!"
                    @click="massDeleteResource()" /> -->
            </template>

            <template #end>
                <Button class="max-sm:text-sm!" severity="help" icon="pi pi-upload" label="Export"
                    @click="exportCSV($event)" />
            </template>
        </Toolbar>

        <div>
            <DataTable class="m-4 rounded-xl border border-gray-200  dark:border-gray-800" ref="dt"
                v-model:selection="selected" :filters="filters" :value="trees.data" :lazy="true" :paginator="true"
                :rows="trees.per_page" :totalRecords="trees.total" :first="(trees.current_page - 1) * trees.per_page"
                :rowsPerPageOptions="[25, 50, 100]" responsiveLayout="scroll" @page="onPage">
                <template #header>
                    <div class="table-header flex flex-col md:flex-row md:justify-content-between">
                        <h5 class="mb-2 md:m-0 p-as-md-center">Manage Trees</h5>
                        <InputText v-model="filters['global'].value" class="p-inputtext-sm max-md:w-full"
                            placeholder="Search..." />
                    </div>
                </template>
                <template #empty>
                    No trees found.
                </template>
                <Column :exportable="false" selectionMode="multiple" style="width: 3rem"></Column>
                <Column :sortable="true" field="id" header="Id"></Column>
                <Column :sortable="true" field="species_id" header="Species"></Column>
                <Column :sortable="true" field="neighborhood_id" header="Neighborhood"></Column>
                <Column :sortable="true" field="lat" header="Latitude"></Column>
                <Column :sortable="true" field="lon" header="Longitude"></Column>
                <Column :sortable="true" field="address" header="address"></Column>
                <Column :sortable="true" field="planted_at" header="Planted At"></Column>
                <Column :sortable="true" field="status" header="Status"></Column>
                <Column :sortable="true" field="health_status" header="Health Status"></Column>
                <Column :sortable="true" field="height_m" header="Height (m)"></Column>
                <Column :sortable="true" field="dbh_cm" header="DBH (cm)"></Column>
                <Column :sortable="true" field="canopy_diameter_m" header="Canopy Diameter (m)"></Column>
                <Column :sortable="true" field="last_inspected_at" header="Last Inspected At"></Column>
                <Column :sortable="true" field="owner_type" header="Owner Type"></Column>
                <Column :sortable="true" field="source" header="source"></Column>
                <Column :exportable="false">
                    <template #body="slotProps">
                        <!-- <Button v-has-permission="{ props: $page.props, permissions: ['trees.edit'] }"
                            class="p-button-rounded mr-2 max-sm:text-sm!" severity="primary" icon="pi pi-pencil"
                            @click="editResource(slotProps.data)" />
                        <Button v-has-permission="{ props: $page.props, permissions: ['trees.delete'] }"
                            class="p-button-rounded max-sm:text-sm!" severity="danger" icon="pi pi-trash" iconPos="left"
                            @click="deleteResource(slotProps.data.id)" /> -->
                    </template>
                </Column>
            </DataTable>
        </div>
        <!-- <TreeForm v-model:visible="formVisible" :action="action" :tree="tree"
            class="dark:bg-gray-900!" /> -->
    </div>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
// import TreeForm from "./Partials/TreeForm.vue"
import { FilterMatchMode } from '@primevue/core/api';
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'

// const page = usePage()


// watch(
//     () => page.props.flash?.message,
//     (flash) => {
//         if (flash && flash.type && flash.message) {
//             const severity =
//                 flash.type === 'success'
//                     ? 'success'
//                     : flash.type === 'error'
//                         ? 'error'
//                         : 'info'

//             window.$toast?.add({
//                 severity,
//                 summary: flash.type.charAt(0).toUpperCase() + flash.type.slice(1),
//                 detail: flash.message,
//                 life: 3000,
//             })
//         }
//     },
//     { immediate: true }
// )

export default {
    layout: AuthenticatedLayout,
    components: {
        // TreeForm
    },
    props: {
        trees: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            selected: null,
            tree: null,
            formVisible: false,
            action: "",
            filters: {}
        }
    },
    created() {
        this.initFilters();
    },
    methods: {
        exportCSV() {
            this.$refs.dt.exportCSV();
        },
        deleteResource(id) {
            this.$confirm.require({
                message: 'Are you sure you want to delete this tree?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: () => {
                    this.$inertia.delete(route('trees.destroy', { tree: id }), {
                        // onSuccess: () => {
                        //     this.$toast.add({
                        //         severity: 'success',
                        //         summary: 'Success',
                        //         detail: 'Tree deleted successfuly!',
                        //         life: 3000
                        //     });
                        // }
                    })
                },
                reject: () => {
                }
            });
        },
        onPage(event) {
            // event.page is 0-based, Laravel expects 1-based
            const page = event.page + 1;
            const perPage = event.rows;

            this.$inertia.get(
                route('trees.index'),
                { page, per_page: perPage },
                {
                    preserveState: true,
                    preserveScroll: true,
                }
            );
        },
        createNewResource() {
            this.tree = null
            this.action = "Create"
            this.formVisible = true;
        },
        editResource(tree) {
            this.tree = tree;
            this.action = "Edit"
            this.formVisible = true;
        },
        massDeleteResource() {
            this.$confirm.require({
                message: 'Are you sure you want to delete all this trees?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: () => {
                    this.$inertia.post(route('trees.massDestroy'), {
                        trees: this.selected,
                    },
                        {
                            // onSuccess: () => {
                            //     this.selected = [];
                            //     this.$toast.add({
                            //         severity: 'success',
                            //         summary: 'Success',
                            //         detail: ' Trees deleted successfuly!',
                            //         life: 3000
                            //     });
                            // },
                        })
                },
                reject: () => {
                }
            });
        },
        initFilters() {
            this.filters = {
                'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
            }
        }
    }
};
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
