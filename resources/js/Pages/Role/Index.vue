<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">

        <Toolbar class="m-4 dark:border-gray-800! dark:bg-transparent! ">
            <template #start>
                <Button v-has-permission="{ props: $page.props, permissions: ['roles.create'] }" severity="success"
                    class="mr-2 mb-2 max-sm:text-sm!" icon="pi pi-plus" label="New" @click="createNewResource()" />
                <Button v-has-permission="{ props: $page.props, permissions: ['roles.delete'] }" severity="danger"
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
                v-model:selection="selected" :filters="filters" :paginator="true" :rows="10"
                :rowsPerPageOptions="[10, 25, 50]" :value="roles" responsiveLayout="scroll">
                <template #header>
                    <div class="table-header flex flex-column md:flex-row md:justiify-content-between">
                        <h5 class="mb-2 md:m-0 p-as-md-center">Manage Roles</h5>
                        <InputText v-model="filters['global'].value" class="p-inputtext-sm max-md:w-full"
                            placeholder="Search..." />
                    </div>
                </template>
                <template #empty>
                    No roles found.
                </template>
                <Column :exportable="false" selectionMode="multiple" style="width: 3rem"></Column>
                <Column :sortable="true" field="id" header="Id"></Column>
                <Column :sortable="true" field="name" header="Name"></Column>
                <Column :exportable="false">
                    <template #body="slotProps">
                        <Button v-has-permission="{ props: $page.props, permissions: ['roles.edit'] }"
                            class="p-button-rounded mr-2 max-sm:text-sm! my-1" severity="primary"
                            icon="pi pi-pencil" @click="editResource(slotProps.data)" />
                        <Button v-has-permission="{ props: $page.props, permissions: ['roles.delete'] }"
                            class="p-button-rounded max-sm:text-sm!" severity="danger" icon="pi pi-trash" iconPos="left"
                            @click="deleteResource(slotProps.data.id)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <RoleForm v-model:visible="formVisible" :action="action" :permissions="permissions" :role="role"
            class="dark:bg-gray-900!" />
    </div>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import RoleForm from "./Partials/RoleForm.vue"
import { FilterMatchMode } from '@primevue/core/api';

export default {
    layout: AuthenticatedLayout,
    components: {
        RoleForm
    },
    props: {
        roles: Object,
        permissions: Object
    },
    data() {
        return {
            selected: null,
            role: null,
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
                message: 'Are you sure you want to delete this role?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: () => {
                    this.$inertia.delete(route('roles.destroy', id))
                },
                reject: () => {
                }
            });
        },
        createNewResource() {
            this.role = null
            this.action = "Create"
            this.formVisible = true;
        },
        editResource(role) {
            this.role = role;
            this.action = "Edit"
            this.formVisible = true;
        },
        massDeleteResource() {
            this.$confirm.require({
                message: 'Are you sure you want to delete all this roles?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: () => {
                    this.$inertia.post(route('roles.massDestroy'), {
                        roles: this.selected,
                    },
                        {
                            onSuccess: () => {
                                this.selected = [];
                            },
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
        },
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
