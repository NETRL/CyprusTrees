<template>
    <h2 v-if="showPageTitle" class="mb-4">{{ pageTitle }}</h2>
    <DataTable ref="dt" v-model:expandedRows="expandedRows" v-model:selection="selected" :filters="filters" :first="from"
               :lazy="true" :loading="loading"
               :paginator="true" :reorderableColumns="true" :resizableColumns="true"
               :rows="perPage" :rowsPerPageOptions="rowsPerPageOptions" :sortField="sortField" :sortOrder="sortOrderInt"
               :totalRecords="total"
               :value="data" columnResizeMode="expand" removableSort
               responsiveLayout="stack"
               showGridlines @page="onPage($event)" @sort="onSort" @update:rows="perPage = $event">

        <template #header>
            <div v-if="showHeader" class="flex flex-column md:justiify-content-between">
                <Toolbar>
                    <template #start>
                        <Button v-if="showCreateButton"
                                v-has-permission="{props: $page.props, permissions: [finalPermissionCreate]}"
                                :label="__('New')" class="p-button-success mr-2" icon="pi pi-plus"
                                @click="createNewResource()"/>
                        <Button v-if="showMassDeleteButton"
                                v-has-permission="{props: $page.props, permissions: [finalPermissionMassDelete]}"
                                :disabled="!selected || !selected.length" :label="__('Delete')"
                                class="p-button-danger"
                                icon="pi pi-trash"
                                @click="massDeleteResource()"/>
                    </template>

                    <template #end>
                        <div class="grid formgrid p-fluid">
                            <div class="m-2">
                                <Button v-if="showExportButton" :label="__('Export')" class="p-button-help mr-2" icon="pi pi-upload"
                                        @click="exportCSV($event)"/>
                            </div>
                            <div class="m-2">
                                <InputText v-if="showSearchInput" v-model="searchData" :placeholder="__('Search...')" class="p-inputtext-sm"
                                           @keydown.enter="searchTerm"/>
                            </div>
                        </div>
                    </template>
                </Toolbar>
            </div>

            <div style="text-align:left">
                <MultiSelect v-show="showSelectColumns" :maxSelectedLabels="1" :modelValue="selectedColumns"
                             :options="columnsToShow" class="w-full" filter
                             optionGroupChildren="items" optionGroupLabel="label" optionLabel="header"
                             placeholder="Select Columns" @update:modelValue="onToggle"/>
            </div>
        </template>

        <template #empty>
            {{ emptyMessage }}
        </template>
        <Column v-if="(showMassDeleteButton && showHeader) || selectMultipleRecords" :exportable="false" selectionMode="multiple" style="width: 3rem"></Column>
        <Column v-if="hasExpander" expander style="width: 3rem"></Column>
        <div v-for="(col, index) in selectedColumns" :key="col.field + '_' + index">
            <Column v-if="col.field.includes('.')" :field="col.field" :header="__(col.header)" :sortable="sortableColumns?.includes(col.field) || sortableColumns === null">
                <template #body="slotProps">
                    {{ displayRelationships(slotProps, col.field) }}
                </template>
            </Column>
            <Column v-else :field="col.field" :header="__(col.header)" :sortable="sortableColumns?.includes(col.field) || sortableColumns === null">
                <template v-if="translateColumnValue" #body="slotProps">
                    {{ __(getDisplayValue(slotProps.data[col.field], col)) }}
                </template>
                <template v-else #body="slotProps">
                    {{ getDisplayValue(slotProps.data[col.field], col) }}
                </template>
            </Column>
        </div>
        <template #expansion="slotProps">
            <slot :data="slotProps.data" name="expansion"></slot>
        </template>

        <slot name="customColumns"></slot>

        <Column v-if="showEditButton || showDeleteButton || showViewButton || showCustomButtons" :exportable="false" :field="primaryKey">
            <template #body="slotProps">
                <slot v-if="showCustomButtons" :slotProps="slotProps" name="customButtons"></slot>
                <Button v-if="showViewButton" v-has-permission="{props: $page.props, permissions: [finalPermissionView]}"
                        class="p-button-rounded mr-2" icon="pi pi-eye" @click="showResource(slotProps.data)"/>
                <Button v-if="showEditButton && showEditButtonItem(slotProps.data)" v-has-permission="{props: $page.props, permissions: [finalPermissionEdit]}"
                        class="p-button-rounded mr-2" icon="pi pi-pencil" @click="editResource(slotProps.data)"/>
                <Button v-if="showDeleteButton" v-has-permission="{props: $page.props, permissions: [finalPermissionDelete]}"
                        class="p-button-rounded p-button-danger" icon="pi pi-trash"
                        @click="deleteResource(slotProps.data[primaryKey])"/>
            </template>
        </Column>

    </DataTable>

    <div v-if="showJumpButton">
        {{ __('Jump to page') }}
        <InputNumber v-model="page" :max="lastPage" :min="1" mode="decimal" @keydown.enter="onPageButton(page)"/>
        <Button :label="__('Go')" @click="onPageButton(page)"/>
    </div>

</template>

<script>
import Dashboard from "@/Pages/Dashboard.vue";
import DataTableMixins from "@/Components/Mixins/DataTableMixins.vue";

export default {
    layout : Dashboard,
    emits  : ['createNewResource', 'editResource', 'showResource'],

    props  : {
        searchFilters : {
            type    : Object,
            default : () => {
                return {};
            },
        },
        dataObject    : Object,
        primaryKey    : String,
        resourceName  : String,

        routeDestroy     : String,
        routeMassDestroy : String,

        permissionCreate     : String,
        permissionMassDelete : String,
        permissionEdit       : String,
        permissionDelete     : String,
        permissionView       : String,

        hasExpander : {
            type    : Boolean,
            default : false,
        },

        showCreateButton      : {
            type    : Boolean,
            default : true,
        },
        showViewButton        : {
            type    : Boolean,
            default : false,
        },
        showEditButton        : {
            type    : Boolean,
            default : true,
        },
        showCustomButtons     : {
            type    : Boolean,
            default : false,
        },
        showDeleteButton      : {
            type    : Boolean,
            default : true,
        },
        showMassDeleteButton  : {
            type    : Boolean,
            default : true,
        },
        showExportButton      : {
            type    : Boolean,
            default : true,
        },
        showSearchInput       : {
            type    : Boolean,
            default : true,
        },
        showJumpButton        : {
            type    : Boolean,
            default : true,
        },
        showHeader            : {
            type    : Boolean,
            default : true,
        },
        showPageTitle         : {
            type    : Boolean,
            default : false,
        },
        selectMultipleRecords : {
            type    : Boolean,
            default : false,
        },
        pageTitle             : {
            type    : String,
            default : "",
        },
        emptyMessage          : {
            type    : String,
            default : "No records found.",
        },
        showSelectColumns     : {
            type    : Boolean,
            default : true,
        },
        columns               : {
            type     : Object,
            required : true,
            default  : () => {
                return {};
            }
        },
        sortableColumns       : {
            type    : Array,
            default : null,
        },
        defaultColumns        : {
            type    : Array,
            default : () => [],
        },
        key                   : {
            type     : String,
            default  : "",
            required : true,
        },
        showEditButtonItem    : {
            type    : Function,
            default : () => {
                return () => {
                    return true;
                };
            },
        },
        translateColumnValue  : {
            type    : Boolean,
            default : true,
        }
    },
    mixins : [DataTableMixins],
    data() {
        return {
            routeOriginal   : null,
            from            : null,
            data            : null,
            page            : null,
            perPage         : null,
            total           : null,
            lastPage        : null,
            selectedColumns : [],

            finalRouteDestroy     : this.routeDestroy ?? this.resourceName + ".destroy",
            finalRouteMassDestroy : this.routeMassDestroy ?? this.resourceName + ".massDestroy",

            finalPermissionCreate     : this.permissionCreate ?? this.resourceName + ".create",
            finalPermissionMassDelete : this.permissionMassDelete ?? this.resourceName + ".delete",
            finalPermissionEdit       : this.permissionEdit ?? this.resourceName + ".edit",
            finalPermissionDelete     : this.permissionDelete ?? this.resourceName + ".delete",
            finalPermissionView       : this.permissionView ?? this.resourceName + ".view",

            columnsToShow : [this.columns],
            expandedRows  : null,
        };
    },
    mounted() {
        this.init();
    },
    methods : {
        saveSelectedColumns() {
            const datatableKey = `selectedColumns_${this.resourceName}_${this.key}`;
            localStorage.setItem(datatableKey, JSON.stringify(this.selectedColumns));
        },
        init() {
            this.routeOriginal = this.dataObject.path
            this.from          = this.dataObject.from
            this.data          = this.dataObject.data
            this.page          = this.dataObject.current_page
            this.perPage       = this.dataObject.per_page
            this.total         = this.dataObject.total
            this.lastPage      = this.dataObject.last_page
        },
        async deleteResource(id) {
            this.$confirm.require({
                message     : this.__('Are you sure you want to delete this item?'),
                header      : this.__('Confirmation'),
                icon        : 'pi pi-exclamation-triangle',
                acceptLabel : this.__('Yes'),
                rejectLabel : this.__('No'),
                accept      : () => {
                    this.$inertia.delete(route(this.finalRouteDestroy, id));
                }
            });
        },
        async massDeleteResource() {
            this.$confirm.require({
                message     : this.__('Are you sure you want to delete all these items?'),
                header      : this.__('Confirmation'),
                icon        : 'pi pi-exclamation-triangle',
                acceptLabel : this.__('Yes'),
                rejectLabel : this.__('No'),
                accept      : () => {
                    this.$inertia.post(route(this.finalRouteMassDestroy), {
                        selected : this.selected,
                    });
                },
            });
        },
        onToggle(value) {
            this.selectedColumns = this.columnsToShow.flatMap(group => group.items).filter(col => value.includes(col));
            this.saveSelectedColumns();
        },
        createNewResource() {
            this.$emit("createNewResource");
        },
        editResource(item) {
            this.$emit("editResource", item);
        },
        showResource(item) {
            this.$emit("showResource", item);
        },
        getDisplayValue(value, column) {
            if (column.component_type === 'Boolean') {
                return value === 1 ? 'Yes' : 'No';
            } else
                if (column.component_type === 'MultiSelect') {
                    let selectedLabels = '';
                    column.options?.forEach(option => {
                        value.forEach(val => {
                            if (val.label === option.label) {
                                selectedLabels += option.label + ', ';
                            }
                        });
                    });
                    //remove last comma
                    selectedLabels = selectedLabels.slice(0, -2);
                    return selectedLabels;
                }

            return value;
        },
        displayRelationships(row, field) {
            const myArray = field.split('.')
            const arg     = myArray[0].split(/(?=[A-Z])/).join('_').toLowerCase();

            if (Array.isArray(row['data'][arg])) {
                return row['data'][arg].map((item) => {
                    return item[myArray[1]];
                }).join(', ');
            }

            return row['data'][arg]?.[myArray[1]];
        }
    },
    created() {
        //convert this.columns from object to array
        let columnsArray = [this.columns];

        //copy this.columns to this.columnsToShow
        this.columnsToShow = JSON.parse(JSON.stringify(columnsArray));

        this.columnsToShow.forEach((group, index) => {
            group.items = group.items.filter(col => col.showTable);
        });

        if (this.defaultColumns.length === 0) {
            this.selectedColumns = this.columnsToShow.flatMap(group => group.items);
        } else {
            this.defaultColumns.forEach((field) => {
                const column = this.columnsToShow.flatMap(group => group.items).find(item => item.field === field);

                if (column) {
                    this.selectedColumns.push(column);
                }
            });
        }

        const datatableKey  = `selectedColumns_${this.resourceName}_${this.key}`;
        const storedColumns = localStorage.getItem(datatableKey);
        if (storedColumns) {
            this.selectedColumns = JSON.parse(storedColumns).filter(col => this.columnsToShow.flatMap(group => group.items).map(item => item.field).includes(col.field));
        }
    },
    watch : {
        'dataObject' : function () {
            this.init();
        }
    },
};
</script>