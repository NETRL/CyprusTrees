<template>
    <Dialog :breakpoints="dialogBreakpoints" :modal="true" :style="{ width: dialogWidth }" :visible="visible"
            :header="__(dialogHeader)" @show="initForm" @update:visible="$emit('update:visible', event)">
        <form class="grid formgrid p-fluid" @submit.prevent="submit">
            <div v-for="(value, key) in localItem" v-show="showField(key)" class="col-12 md:col-6" :key="key">
                <div v-if="showField(key)" class="field mb-4 col-12">
                    <FormField v-if="componentType(key) === 'Boolean'"
                               v-model="localItem[key+'_flag']" :displayErrors="displayErrors" :label="__(label(key))" :isRequired="isRequired(key)" :name="key"
                               :readonly="action === 'Show'" :component="componentType(key)" :hasInfo="hasInfo(key)" :infoMessage="__(infoMessage(key))"/>
                    <FormField v-if="componentType(key) !== 'Boolean'"
                               v-model="localItem[key]" :displayErrors="displayErrors" :autocomplete="key" :label="__(label(key))" :isRequired="isRequired(key)" :name="key"
                               :readonly="action === 'Show'" :component="componentType(key)"
                               :options="componentType(key) === 'Dropdown' || componentType(key) === 'MultiSelect' ? options(key) : null"
                               optionLabel="label" optionValue="value" :hasInfo="hasInfo(key)" :infoMessage="__(infoMessage(key))"/>
                </div>
            </div>

            <p v-if="hasRequiredFields" style="color: red;">* {{ __('The field is required') }}</p>
        </form>
        <template #footer>
            <Button v-if="action !== 'Show'" class="p-button-text" icon="pi pi-times" :label="__('Cancel')" @click="closeForm"/>
            <Button v-else class="p-button-text" icon="pi pi-times" :label="__('Close')" @click="closeForm"/>
            <Button v-if="action !== 'Show'" :label="__('Save')" class="p-button-text" icon="pi pi-check" @click="submit"/>
        </template>
    </Dialog>
</template>

<script>
import FormField from "@/Components/Primitives/FormField.vue";
import DataTableMixins from '@/Components/Mixins/DataTableMixins.vue';
import login from '@/Pages/Auth/Login.vue';

export default {
    emits      : ['update:visible'],
    components : {
        FormField
    },
    mixins     : [DataTableMixins],
    props      : {
        visible           : Boolean,
        item              : Object,
        action            : String,
        fields            : Object,
        dialogWidth       : {
            type    : String,
            default : '600px'
        },
        dialogHeader      : String,
        dialogBreakpoints : {
            type    : Object,
            default : () => ({'960px' : '75vw', '640px' : '100vw'})
        },
        routeStore        : String,
        routeUpdate       : String,
        routeIndex        : String,
        resourceName      : String,
        primaryKey        : String
    },
    data() {
        return {
            showForm         : this.visible,
            localItem        : {},
            displayErrors    : false,
            finalRouteIndex  : this.routeIndex ?? this.resourceName + ".index",
            finalRouteStore  : this.routeStore ?? this.resourceName + ".store",
            finalRouteUpdate : this.routeUpdate ?? this.resourceName + ".update",
        }
    },
    computed : {
        hasRequiredFields() {
            return this.fields.some(field => field.required && field.showForm);
        },
    },
    methods  : {
        submit() {
            //for boolean fields
            this.fields.forEach(field => {
                if (field.component_type === 'Boolean') {
                    const flagKey               = `${field.field}_flag`;
                    this.localItem[field.field] = this.localItem[flagKey] ? 1 : 0;
                }
            });

            if (this.action === 'Create') {
                this.$inertia.post(
                    route(this.finalRouteStore),
                    this.localItem,
                    {
                        preserveScroll : true,
                        onSuccess      : () => {
                            this.closeForm();
                            this.$inertia.get(route(this.finalRouteIndex));
                        },
                        onFinish       : () => this.displayErrors = true,
                    }
                );
            } else
                if (this.action === 'Edit') {
                    this.$inertia.patch(
                        route(this.finalRouteUpdate, this.localItem[this.primaryKey]),
                        this.localItem,
                        {
                            preserveScroll : true,
                            onSuccess      : () => {
                                this.closeForm();
                                this.$inertia.get(route(this.finalRouteIndex));
                            },
                            onFinish       : () => this.displayErrors = true
                        }
                    );
                }
        },
        initForm() {
            console.log(this.item)
            console.log(this.fields)
            this.displayErrors = false;

            if (this.item) {
                this.localItem[this.primaryKey] = this.item[this.primaryKey];
            }

            this.fields.forEach(key => {
                if (key.showForm) {
                    if (this.item) {
                        if (key.component_type === 'MultiSelect') {
                            //convert key.field from camel case to snake case
                            let keyField = this.camelToSnakeCase(key.field);

                            this.localItem[key.field] = [];
                            this.item[keyField]?.forEach(j => {
                                this.localItem[key.field].push(j[key['valueColumn']]);
                            });
                        } else {
                            this.localItem[key.field] = this.item[key.field];
                        }
                    } else {
                        this.localItem[key.field] = null;
                    }
                }
            });

            // for boolean fields
            this.fields.forEach(field => {
                if (field.component_type === 'Boolean') {
                    const flagKey           = `${field.field}_flag`;
                    this.localItem[flagKey] = this.item && this.item[field.field] ? !!this.item[field.field] : false;
                }
            });
        },
        closeForm() {
            this.$emit('update:visible', false)
        },
        showField(key) {
            return this.fields.find(i => i.field === key)?.showForm;
        },
        componentType(key) {
            return this.fields.find(i => i.field === key).component_type;
        },
        label(key) {
            return this.fields.find(i => i.field === key).header;
        },
        options(key) {
            return this.fields.find(i => i.field === key).options;
        },
        isRequired(key) {
            return this.fields.find(i => i.field === key).required;
        },
        infoMessage(key) {
            return this.fields.find(i => i.field === key).info;
        },
        hasInfo(key) {
            return this.infoMessage(key) !== null && this.infoMessage(key) !== undefined && this.infoMessage(key) !== '';
        },
        camelToSnakeCase(str) {
            return str.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
        }
    }
}
</script>
