<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Species Details" @update:visible="emit('update:visible', $event)"
        class="dark:bg-gray-900!">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <!-- Species -->
            <div class="col-span-12">
                <FormField component="Dropdown" v-model="formData.species_id" :displayErrors="displayErrors"
                    :options="speciesOptions" optionLabel="label" optionValue="value" label="Species"
                    name="species_id" />
            </div>

            <!-- Neighborhood -->
            <div class="col-span-12">
                <FormField component="Dropdown" v-model="formData.neighborhood_id" :displayErrors="displayErrors"
                    :options="neighborhoodOptions" optionLabel="label" optionValue="value" label="Neighborhood"
                    name="neighborhood_id" />
            </div>

            <!-- Coordinates -->
            <div class="col-span-6">
                <FormField v-model="formData.lat" :displayErrors="displayErrors" label="Latitude" name="lat" />
            </div>
            <div class="col-span-6">
                <FormField v-model="formData.lon" :displayErrors="displayErrors" label="Longitude" name="lon" />
            </div>

            <!-- Address -->
            <div class="col-span-12">
                <FormField v-model="formData.address" :displayErrors="displayErrors" label="Address" name="address" />
            </div>

            <!-- Dates -->
            <div class="col-span-6">
                <FormField component="Calendar" v-model="formData.planted_at" :displayErrors="displayErrors"
                    label="Planted At" name="planted_at" />
            </div>
            <div class="col-span-6">
                <FormField component="Calendar" v-model="formData.last_inspected_at" :displayErrors="displayErrors"
                    label="Last Inspected At" name="last_inspected_at" />
            </div>

            <!-- Status fields -->
            <div class="col-span-6">
                <FormField v-model="formData.status" :displayErrors="displayErrors" label="Status" name="status" />
            </div>
            <div class="col-span-6">
                <FormField v-model="formData.health_status" :displayErrors="displayErrors" label="Health Status"
                    name="health_status" />
            </div>

            <!-- Measurements -->
            <div class="col-span-4">
                <FormField v-model="formData.height_m" :displayErrors="displayErrors" label="Height (m)"
                    name="height_m" />
            </div>
            <div class="col-span-4">
                <FormField v-model="formData.dbh_cm" :displayErrors="displayErrors" label="DBH (cm)" name="dbh_cm" />
            </div>
            <div class="col-span-4">
                <FormField v-model="formData.canopy_diameter_m" :displayErrors="displayErrors" label="Canopy (m)"
                    name="canopy_diameter_m" />
            </div>

            <!-- Ownership / source -->
            <div class="col-span-6">
                <FormField v-model="formData.owner_type" :displayErrors="displayErrors" label="Owner Type"
                    name="owner_type" />
            </div>
            <div class="col-span-6">
                <FormField v-model="formData.source" :displayErrors="displayErrors" label="Source" name="source" />
            </div>
        </form>

        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="closeForm" />
            <Button :label="action" class="p-button-text" icon="pi pi-check" @click="submit" />
        </template>
    </Dialog>
</template>

<script setup>
import { reactive, ref, computed, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import FormField from '@/Components/Primitives/FormField.vue'

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
    speciesData: {
        type: Array,
        default: () => [],
    },
    neighborhoodData: {
        type: Array,
        default: () => [],
    },
    action: {
        type: String,
        default: '',
    },
    routeResource: {
        type: String,
        required: true
    }
})


const emit = defineEmits(['update:visible'])

// state
const formData = reactive({
    id: null,
    species_id: null,
    neighborhood_id: null,
    lat: null,
    lon: null,
    address: '',
    planted_at: null,
    status: '',
    health_status: '',
    height_m: null,
    dbh_cm: null,
    canopy_diameter_m: null,
    last_inspected_at: null,
    owner_type: '',
    source: '',
})

const displayErrors = ref(false)

// Helper function to convert date string to Date object
const parseDate = (dateString) => {
    if (!dateString) return null
    const date = new Date(dateString)
    return isNaN(date.getTime()) ? null : date
}

// methods
const closeForm = () => {
    emit('update:visible', false)
    // Reset form after dialog closes
    nextTick(() => {
        resetForm()
    })
}

const resetForm = () => {
    formData.id = null
    formData.species_id = null
    formData.neighborhood_id = null
    formData.lat = null
    formData.lon = null
    formData.address = ''
    formData.planted_at = null
    formData.status = ''
    formData.health_status = ''
    formData.height_m = null
    formData.dbh_cm = null
    formData.canopy_diameter_m = null
    formData.last_inspected_at = null
    formData.owner_type = ''
    formData.source = ''
}

const speciesOptions = computed(() =>
    props.speciesData.map(index => ({
        label: `${index.common_name} (${index.latin_name})`,
        value: index.id,
    }))
)

const neighborhoodOptions = computed(() =>
    props.neighborhoodData.map(index => ({
        label: `${index.name} (${index.city})`,
        value: index.id,
    }))
)



watch(neighborhoodOptions, v => console.log(v))

const initForm = () => {
    displayErrors.value = false

    if (!props.dataRow) {
        resetForm()
        return
    }

    formData.id = props.dataRow.id ?? null
    formData.species_id = props.dataRow.species_id ?? null
    formData.neighborhood_id = props.dataRow.neighborhood_id ?? null
    formData.lat = props.dataRow.lat ?? null
    formData.lon = props.dataRow.lon ?? null
    formData.address = props.dataRow.address ?? ''
    formData.planted_at = parseDate(props.dataRow.planted_at)
    formData.status = props.dataRow.status ?? ''
    formData.health_status = props.dataRow.health_status ?? ''
    formData.height_m = props.dataRow.height_m ?? null
    formData.dbh_cm = props.dataRow.dbh_cm ?? null
    formData.canopy_diameter_m = props.dataRow.canopy_diameter_m ?? null
    formData.last_inspected_at = parseDate(props.dataRow.last_inspected_at)
    formData.owner_type = props.dataRow.owner_type ?? ''
    formData.source = props.dataRow.source ?? ''
}

// Watch for visibility changes
watch(() => props.visible, (newVal) => {
    if (newVal) {
        nextTick(() => {
            initForm()
        })
    }
})

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
        router.patch(route(props.routeResource + '.update', formData.id), { ...formData }, {
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