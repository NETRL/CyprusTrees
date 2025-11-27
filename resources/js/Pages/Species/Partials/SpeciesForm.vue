<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Species Details" @show="initForm" @update:visible="emit('update:visible', $event)"
        class="dark:bg-gray-900! select-none">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <!-- Latin Name -->
            <div class="col-span-12">
                <FormField v-model="formData.latin_name" :displayErrors="displayErrors" label="Latin Name"
                    name="latin_name" />
            </div>

            <!-- Common Name -->
            <div class="col-span-12">
                <FormField v-model="formData.common_name" :displayErrors="displayErrors" label="Common Name"
                    name="common_name" />
            </div>

            <!-- Family -->
            <div class="col-span-12">
                <FormField v-model="formData.family" :displayErrors="displayErrors" label="Family" name="family" />
            </div>

             <div class="col-span-6">
                <FormField component="Number" v-model="formData.opals_score" :displayErrors="displayErrors"  @input="formData.opals_score = $event.value" 
                    label="OPALS Score (0–10)" name="opals_score" inputId="minmax-buttons" mode="decimal" showButtons :min="0" :max="10" :step="1" fluid/>
            </div>

            <!-- Drought Tolerance (ENUM: Low / Moderate / High) -->
            <div class="col-span-6">
                <FormField component="Dropdown" v-model="formData.drought_tolerance" :displayErrors="displayErrors"
                    :options="droughtToleranceOptions" label="Drought Tolerance" name="drought_tolerance"
                    optionLabel="label" optionValue="value" placeholder="Select drought tolerance" />
            </div>

            <!-- Canopy Class (e.g. S / M / L) -->
            <div class="col-span-12">
                <FormField component="Dropdown" v-model="formData.canopy_class" :displayErrors="displayErrors"
                    :options="canopyClassOptions" label="Canopy Class" name="canopy_class" optionLabel="label"
                    optionValue="value" placeholder="Select canopy class" />
            </div>

            <!-- Notes -->
            <div class="col-span-12">
                <FormField v-model="formData.notes" :displayErrors="displayErrors" label="Notes" name="notes" />
            </div>
        </form>

        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="closeForm" />
            <Button :label="action" class="p-button-text" icon="pi pi-check" @click="submit" />
        </template>
    </Dialog>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
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
    action: {
        type: String,
        default: '',
    },
    routeResource: {
        type: String,
        required: true,
    },
    droughtOptions: {
        type: Array,
        default: () => [],
    },
    canopyOptions: {
        type: Array,
        default: () => [],
    }
})

const emit = defineEmits(['update:visible'])

// state
const formData = reactive({
    id: null,
    latin_name: '',
    common_name: '',
    family: '',
    opals_score: null,
    drought_tolerance: null,
    canopy_class: null,
    notes: '',
})

const displayErrors = ref(false)

// static options
const droughtToleranceOptions = computed(() => props.droughtOptions)

const canopyClassOptions = computed(() => props.canopyOptions)

// methods
const closeForm = () => {
    emit('update:visible', false)
}

const initForm = () => {
    const row = props.dataRow

    displayErrors.value = false

    formData.id = row?.id ?? null
    formData.latin_name = row?.latin_name ?? ''
    formData.common_name = row?.common_name ?? ''
    formData.family = row?.family ?? ''
    formData.opals_score = row?.opals_score ?? null
    formData.drought_tolerance = row?.drought_tolerance ?? null
    formData.canopy_class = row?.canopy_class ?? null
    formData.notes = row?.notes ?? ''
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
