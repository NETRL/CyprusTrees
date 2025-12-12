<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Citizen Report Details" @show="initForm"
        @update:visible="emit('update:visible', $event)" class="dark:bg-gray-900! select-none">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <!-- Type -->
            <div class="col-span-12">
                <FormField component="Dropdown" filter v-model="formData.report_type_id" :displayErrors="displayErrors"
                    label="Report Type" name="report_type_id" :options="typeOptions" optionLabel="label"
                    optionValue="value" />
            </div>

            <div class="col-span-12">
                <FormField component="Dropdown" filter v-model="formData.created_by" :displayErrors="displayErrors"
                    label="Created By" name="created_by" :options="userOptions" optionLabel="label"
                    optionValue="value" />
            </div>

            <div class="col-span-12">
                <FormField component="Dropdown" filter v-model="formData.tree_id" :displayErrors="displayErrors"
                    label="Tree" name="tree_id" :options="treeOptions" optionLabel="label" optionValue="value" />
            </div>

            <div class="col-span-6">
                <FormField v-model="formData.lat" :displayErrors="displayErrors" label="Latitude" name="lat" />
            </div>

            <div class="col-span-6">
                <FormField v-model="formData.lon" :displayErrors="displayErrors" label="Longitude" name="lon" />
            </div>

            <div class="col-span-12">
                <FormField v-model="formData.description" :displayErrors="displayErrors" label="Description"
                    name="description" />
            </div>

            <div class="col-span-12">
                <FormField component="Dropdown" v-model="formData.status" :displayErrors="displayErrors" label="Status"
                    name="status" :options="reportStatusOptions" optionLabel="label" optionValue="value" />
            </div>

            <div class="col-span-6">
                <FormField component="Calendar" v-model="formData.created_at" :displayErrors="displayErrors"
                    label="Created At" name="created_at" />
            </div>
            <div class="col-span-6">
                <FormField component="Calendar" v-model="formData.resolved_at" :displayErrors="displayErrors"
                    label="Created At" name="resolved_at" />
            </div>
        </form>

        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="closeForm" />
            <Button :label="action" class="p-button-text" icon="pi pi-check" @click="submit" />
        </template>
    </Dialog>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import FormField from '@/Components/Primitives/FormField.vue'
import { useDateParser } from '@/Composables/useDateParser'

// props & emits
const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    dataRow: {
        type: Object,
        default: null,
    },
    action: {
        type: String,
        default: '',
    },
    routeResource: {
        type: String,
        required: true,
    },
    trees: {
        type: Array,
        default: () => [],
    },
    types: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
    reportStatus: {
        type: Array,
        default: () => [],
    }
})

const emit = defineEmits(['update:visible'])

const { parseDate } = useDateParser();

const reportStatusOptions = computed(() => props.reportStatus)

const treeOptions = computed(() =>
    props.trees.map(index => ({
        value: index.id,
        label:
            index.address
                ? `#${index.id} – ${index.address}`
                : index.species?.common_name
                    ? `#${index.id} – ${index.species.common_name} (${index.lat}, ${index.lon})`
                    : `#${index.id} – (${index.lat}, ${index.lon})`,
    }))
)

const typeOptions = computed(() =>
    (props.types ?? []).map(index => {
        const name = index.name ?? '';
        const label = name

        return {
            value: index.id,
            label,
        };
    })
);


const userOptions = computed(() => (props.users ?? []).map(index => {
    const firstName = index.first_name ?? '';
    const lastName = index.last_name ?? '';
    const roles = Array.isArray(index.roles) ? index.roles : [];

    const roleNames = roles.length ? roles.map(r => r.name).join(', ') : 'No role';

    const label = `${firstName} ${lastName} (${roleNames})`

    return {
        value: index.id,
        label,
    };
}))


// state
const formData = reactive({
    report_id: null,
    tree_id: null,
    report_type_id: null,
    created_by: null,
    lat: '',
    lon: '',
    description: '',
    status: null,
    created_at: null,
    resolved_at: null,
})

watch(() => formData.status,
    (v) => {
        if(formData.status === 'resolved'){
            formData.resolved_at = new Date();
        }else{
            formData.resolved_at = null;
        }
    })

const displayErrors = ref(false)

// methods
const closeForm = () => {
    emit('update:visible', false)
}

const initForm = () => {
    const row = props.dataRow

    displayErrors.value = false

    formData.report_id = row?.report_id ?? null
    formData.tree_id = row?.tree_id ?? null
    formData.report_type_id = row?.report_type_id ?? null
    formData.created_by = row?.created_by ?? null
    formData.lat = row?.lat ?? ''
    formData.lon = row?.lon ?? ''
    formData.description = row?.description ?? ''
    formData.status = row?.status ?? null
    formData.created_at = parseDate(row?.created_at)
    formData.resolved_at = parseDate(row?.resolved_at)
}

const submit = () => {
    if (props.action === 'Create') {
        router.post(route(props.routeResource + '.store'), { ...formData }, {
            preserveScroll: true,
            onSuccess: () => {
                closeForm()
            },
            onFinish: () => {
                displayErrors.value = true
            },
        })
    } else if (props.action === 'Edit') {
        router.patch(route(props.routeResource + '.update', formData.report_id), { ...formData }, {
            preserveScroll: true,
            onSuccess: () => {
                closeForm()
            },
            onFinish: () => {
                displayErrors.value = true
            },
        })
    }
}
</script>
