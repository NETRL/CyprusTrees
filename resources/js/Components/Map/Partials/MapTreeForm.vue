<template>
    <div class="w-full max-w-[450px] rounded-xl bg-white dark:bg-gray-900 select-none overflow-y-auto my-2">
        <!-- Header -->
        <div class="sticky flex items-center justify-between px-4 py-3 border-b dark:border-gray-700">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold">Tree Details</h2>

                <!-- Only in CREATE mode -->
                <div v-if="action === 'Create' && markerLatLng" class="flex items-center gap-2">
                    <button type="button" class="px-2.5 py-1 text-xs rounded-md border border-gray-200 hover:bg-gray-50
               dark:border-gray-700 dark:hover:bg-white/5 disabled:opacity-50" 
                        @click="copyLast" title="Copy values from the last saved tree">
                        Copy last
                    </button>
                </div>
            </div>

            <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" @click="closeForm">
                ✕
            </button>
        </div>
        <!-- Body -->
        <div class="p-4 max-h-[80vh] overflow-y-auto">
            <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">

                <!-- Coordinates -->
                <div class="col-span-6">
                    <FormField v-model="formData.lat" :displayErrors="displayErrors" label="Latitude" name="lat" />
                </div>
                <div class="col-span-6">
                    <FormField v-model="formData.lon" :displayErrors="displayErrors" label="Longitude" name="lon" />
                </div>

                <!-- Species -->
                <div class="col-span-12">
                    <FormField component="Dropdown" v-model="formData.species_id" :displayErrors="displayErrors"
                        :options="speciesOptions" optionLabel="label" optionValue="value" label="Species"
                        name="species_id" />
                </div>

                <!-- Tag -->
                <div class="col-span-12">
                    <FormField component="MultiSelect" v-model="formData.tag_ids" :displayErrors="displayErrors"
                        :options="tagOptions" optionLabel="label" optionValue="value" label="Tag" name="tag_ids" />
                </div>

                <!-- Neighborhood -->
                <div class="col-span-12">
                    <FormField component="Dropdown" v-model="formData.neighborhood_id" :displayErrors="displayErrors"
                        :options="neighborhoodOptions" optionLabel="label" optionValue="value" label="Neighborhood"
                        name="neighborhood_id" />
                </div>

                <!-- Address -->
                <div class="col-span-12">
                    <FormField v-model="formData.address" :displayErrors="displayErrors" label="Address"
                        name="address" />
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

                <!-- Status -->
                <div class="col-span-4">
                    <FormField component="Dropdown" v-model="formData.status" :displayErrors="displayErrors"
                        label="Status" name="status" :options="treeStatusOptions" optionLabel="label"
                        optionValue="value" placeholder="Select tree status" />
                </div>
                <div class="col-span-4">
                    <FormField component="Dropdown" v-model="formData.health_status" :displayErrors="displayErrors"
                        label="Health Status" name="health_status" :options="healthStatusOptions" optionLabel="label"
                        optionValue="value" placeholder="Select health status" />
                </div>
                <div class="col-span-4">
                    <FormField component="Dropdown" v-model="formData.sex" :displayErrors="displayErrors" label="Sex"
                        name="sex" :options="treeSexOptions" optionLabel="label" optionValue="value"
                        placeholder="Select sex" />
                </div>

                <!-- Measurements -->
                <div class="col-span-4">
                    <FormField v-model="formData.height_m" :displayErrors="displayErrors" label="Height (m)"
                        name="height_m" />
                </div>
                <div class="col-span-4">
                    <FormField v-model="formData.dbh_cm" :displayErrors="displayErrors" label="DBH (cm)"
                        name="dbh_cm" />
                </div>
                <div class="col-span-4">
                    <FormField v-model="formData.canopy_diameter_m" :displayErrors="displayErrors" label="Canopy (m)"
                        name="canopy_diameter_m" />
                </div>

                <!-- Ownership -->
                <div class="col-span-12">
                    <FormField component="Dropdown" v-model="formData.owner_type" :displayErrors="displayErrors"
                        label="Owner Type" name="owner_type" :options="ownerTypeOptions" optionLabel="label"
                        optionValue="value" placeholder="Select owner type" />
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 px-4 py-3 border-t dark:border-gray-700">
            <button class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                @click="closeForm">
                Cancel
            </button>
            <button class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded hover:bg-brand-700"
                @click="submit">
                {{ action }}
            </button>
        </div>
    </div>
</template>


<script setup>
import { reactive, ref, computed, watch, nextTick, inject } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import FormField from '@/Components/Primitives/FormField.vue'
import { useDateParser } from '@/Composables/useDateParser'
import { safeJsonParse } from '@/Composables/safeJsonParser'

// props 
const props = defineProps({
    visible: { type: Boolean, default: false, },
    dataRow: { type: Object, default: null, },
    action: { type: String, default: '', },
    routeResource: { type: String, required: true },
    markerLatLng: { type: Object, default: null, }
})

const page = usePage()

const mapBus = inject('mapBus', null)
const lastCreatedTree = inject('lastCreatedTree', ref(null))

const speciesData = inject('speciesData')
const neighborhoodData = inject('neighborhoodData')
const tagData = inject('tagData')
const treeSex = inject('treeSex')
const healthStatus = inject('healthStatus')
const treeStatus = inject('treeStatus')
const ownerType = inject('ownerType')


const emit = defineEmits(['update:visible'])

const role = page.props?.auth?.user?.roles[0].name
let source = null
if (role === "staff" || role === 'admin') {
    source = 'field_survey'
} else if (role === "citizen") {
    source = 'citizen_report'
}

// state
const formData = reactive({
    id: null,
    species_id: null,
    tag_ids: null,
    neighborhood_id: null,
    lat: null,
    lon: null,
    address: '',
    planted_at: null,
    status: null,
    health_status: null,
    sex: null,
    height_m: null,
    dbh_cm: null,
    canopy_diameter_m: null,
    last_inspected_at: null,
    owner_type: null,
    source: source,
})


const displayErrors = ref(false)

const healthStatusOptions = computed(() => healthStatus?.value ?? healthStatus ?? [])
const treeStatusOptions = computed(() => treeStatus)
const treeSexOptions = computed(() => treeSex)
const ownerTypeOptions = computed(() => ownerType)


// Helper function to convert date string to Date object
const { parseDate } = useDateParser();

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
    formData.tag_ids = null
    formData.neighborhood_id = null
    formData.lat = null
    formData.lon = null
    formData.address = ''
    formData.planted_at = null
    formData.status = null
    formData.health_status = null
    formData.sex = null
    formData.height_m = null
    formData.dbh_cm = null
    formData.canopy_diameter_m = null
    formData.last_inspected_at = null
    formData.owner_type = null
    formData.source = source
}

watch(
    () => props.markerLatLng,
    (v) => {
        if (!v) return
        resetForm()
        formData.lat = v.lat
        formData.lon = v.lng
    },
    { immediate: true }
)

const speciesOptions = computed(() =>
    speciesData.map(index => ({
        label: `${index.common_name} (${index.latin_name})`,
        value: index.id,
    }))
)

const neighborhoodOptions = computed(() =>
    neighborhoodData.map(index => ({
        label: `${index.name} (${index.city})`,
        value: index.id,
    }))
)
const tagOptions = computed(() =>
    tagData.map(index => ({
        label: index.name,
        value: index.id,
    }))
)

function initForm() {
    displayErrors.value = false
    // EDIT
    if (props.dataRow) {
        const row = props.dataRow
        const tags = safeJsonParse(row.tags)

        formData.id = row.id ?? null
        formData.species_id = row.species_id ?? null
        formData.tag_ids = tags ? tags.map(t => t.id) : []
        formData.neighborhood_id = row.neighborhood_id ?? null
        formData.lat = row.lat ?? null
        formData.lon = row.lon ?? null
        formData.address = row.address ?? ''
        formData.planted_at = parseDate(row.planted_at)
        formData.status = row.status ?? null
        formData.health_status = row.health_status ?? null
        formData.sex = row.sex ?? null
        formData.height_m = row.height_m ?? null
        formData.dbh_cm = row.dbh_cm ?? null
        formData.canopy_diameter_m = row.canopy_diameter_m ?? null
        formData.last_inspected_at = parseDate(row.last_inspected_at)
        formData.owner_type = row.owner_type ?? null
        formData.source = row.source ?? source
        return
    }

    // CREATE
    resetForm()
    if (props.markerLatLng) {
        formData.lat = props.markerLatLng.lat
        formData.lon = props.markerLatLng.lng
    }
}

// event functions
function normalizeTagIds(row) {
    // row.tags can be: JSON string, array, null
    const tags = typeof row?.tags === 'string' ? safeJsonParse(row.tags) : row?.tags
    if (!Array.isArray(tags)) return []
    return tags.map(t => t.id).filter(Boolean)
}


function applyTemplate(row) {
    if (!row) return

    // keep current coords (from marker)
    const currentLat = formData.lat
    const currentLon = formData.lon

    formData.species_id = row.species_id ?? null
    formData.tag_ids = normalizeTagIds(row)
    formData.neighborhood_id = row.neighborhood_id ?? null
    formData.address = row.address ?? ''

    formData.planted_at = new Date()
    formData.last_inspected_at = new Date()

    formData.status = row.status ?? null
    formData.health_status = row.health_status ?? null
    formData.sex = row.sex ?? null

    formData.height_m = row.height_m ?? null
    formData.dbh_cm = row.dbh_cm ?? null
    formData.canopy_diameter_m = row.canopy_diameter_m ?? null

    formData.owner_type = row.owner_type ?? null
    formData.source = row.source ?? source

    // restore coords
    formData.lat = currentLat
    formData.lon = currentLon

    displayErrors.value = false
}

function copyLast() {
    const row = lastCreatedTree?.value
    if (!row) return
    applyTemplate(row)
}

// Watch for visibility changes
watch(
    () => [props.visible, props.action, props.dataRow],
    ([visible, action, row]) => {
        if (!visible) return
        if (action === 'Edit' && row) nextTick(initForm)
    },
    { immediate: true, deep: false }
)

const submit = () => {
    if (props.action === 'Create') {
        router.post(route(props.routeResource + '.store'), { ...formData }, {
            preserveScroll: true,
            onSuccess: (page) => {
                closeForm()
                const event = page?.props?.flash?.event
                if (event?.type === 'tree.saved') {
                    mapBus?.emit('tree:saved', { id: event.payload.id })
                }
            },
            onError: () => { displayErrors.value = true },
        })

    } else if (props.action === 'Edit') {
        router.patch(route(props.routeResource + '.update', formData.id), { ...formData }, {
            preserveScroll: true,
            onSuccess: () => {
                closeForm()
                mapBus?.emit('tree:saved', { id: formData.id })
            },
            onError: () => {
                displayErrors.value = true
            },
        })
    }
}
</script>