<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
        <Toolbar class="m-4  dark:border-gray-800! dark:bg-transparent! ">
            <template #start>
                <Button v-has-permission="{ props: $page.props, permissions: ['users.create'] }" severity="success"
                    class="mr-2 max-sm:text-sm!" icon="pi pi-plus" label="New" @click="createNewResource()" />
                <Button v-has-permission="{ props: $page.props, permissions: ['users.delete'] }" severity="danger"
                    :disabled="!selected || !selected.length" icon="pi pi-trash" label="Delete" class="max-sm:text-sm!"
                    @click="massDeleteResource()" />
            </template>

            <template #end>
                <Button class="max-sm:text-sm!" severity="help" icon="pi pi-upload" label="Export"
                    @click="exportCSV($event)" />
            </template>
        </Toolbar>

        <div>
            <DataTable class="m-4 rounded-xl border border-gray-200  dark:border-gray-800" ref="dt"
                v-model:selection="selected" :filters="filters" :paginator="true" :rows="10"
                :rowsPerPageOptions="[10, 25, 50]" :value="users" responsiveLayout="scroll">
                <template #header>
                    <div class="table-header flex flex-col md:flex-row md:justify-content-between">
                        <h5 class="mb-2 md:m-0 p-as-md-center">Manage Users</h5>
                        <InputText v-model="filters['global'].value" class="p-inputtext-sm max-md:w-full"
                            placeholder="Search..." />
                    </div>
                </template>
                <template #empty>
                    No users found.
                </template>
                <Column :exportable="false" selectionMode="multiple" style="width: 3rem"></Column>
                <Column :sortable="true" field="id" header="Id"></Column>
                <Column :sortable="true" field="first_name" header="First Name"></Column>
                <Column :sortable="true" field="last_name" header="Last Name"></Column>
                <Column :sortable="true" field="email" header="Email"></Column>
                <Column :sortable="true" field="phone" header="Phone"></Column>
                <Column :exportable="false">
                    <template #body="slotProps">
                        <Button v-has-permission="{ props: $page.props, permissions: ['users.edit'] }"
                            class="p-button-rounded mr-2 max-sm:text-sm!" severity="primary" icon="pi pi-pencil"
                            @click="editResource(slotProps.data)" />
                        <Button v-has-permission="{ props: $page.props, permissions: ['users.delete'] }"
                            class="p-button-rounded max-sm:text-sm!" severity="danger" icon="pi pi-trash" iconPos="left"
                            @click="deleteResource(slotProps.data.id)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <UserForm v-model:visible="formVisible" :action="action" :roles="roles" :user="user"
            class="dark:bg-gray-900!" />
    </div>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import UserForm from "./Partials/UserForm.vue"
import { FilterMatchMode } from '@primevue/core/api';

export default {
    layout: AuthenticatedLayout,
    components: {
        UserForm
    },
    props: {
        users: Object,
        roles: Object
    },
    data() {
        return {
            selected: null,
            user: null,
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
                message: 'Are you sure you want to delete this user?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: () => {
                    this.$inertia.delete(route('users.destroy', id), {
                        onSuccess: () => {
                            this.$toast.add({
                                severity: 'success',
                                summary: 'Success',
                                detail: 'User deleted successfuly!',
                                life: 3000
                            });
                        }
                    })
                },
                reject: () => {
                }
            });
        },
        createNewResource() {
            this.user = null
            this.action = "Create"
            this.formVisible = true;
        },
        editResource(user) {
            this.user = user;
            this.action = "Edit"
            this.formVisible = true;
        },
        massDeleteResource() {
            this.$confirm.require({
                message: 'Are you sure you want to delete all this users?',
                header: 'Confirmation',
                icon: 'pi pi-exclamation-triangle',
                accept: () => {
                    this.$inertia.post(route('users.massDestroy'), {
                        users: this.selected,
                    },
                        {
                            onSuccess: () => {
                                this.selected = [];
                                this.$toast.add({
                                    severity: 'success',
                                    summary: 'Success',
                                    detail: ' Users deleted successfuly!',
                                    life: 3000
                                });
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
