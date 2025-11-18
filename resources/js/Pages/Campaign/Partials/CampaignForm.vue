<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Campaigns Details" @show="initForm" @update:visible="emit('update:visible', $event)"
        class="dark:bg-gray-900!">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <!-- Name -->
            <div class="col-span-12">
                <FormField v-model="formData.name" :displayErrors="displayErrors" label="Name" name="name" />
            </div>
            <!-- Sponsor -->
            <div class="col-span-12">
                <FormField v-model="formData.sponsor" :displayErrors="displayErrors" label="Sponsor" name="sponsor" />
            </div>
            <!-- Start Date -->
            <div class="col-span-6">
                <FormField component="Calendar" v-model="formData.start_date" :displayErrors="displayErrors" label="Start Date"
                    name="start_date" />
            </div>
            <!-- End Date -->
            <div class="col-span-6">
                <FormField component="Calendar" v-model="formData.end_date" :displayErrors="displayErrors" label="End Date"
                    name="end_date" />
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
import { reactive, ref } from 'vue'
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
})

const emit = defineEmits(['update:visible'])

// state
const formData = reactive({
    id: null,
    name: '',
    sponsor: '',
    start_date: null,
    end_date: null,
    notes: '',
})

const displayErrors = ref(false)

// methods
const closeForm = () => {
    emit('update:visible', false)
}

const initForm = () => {
    displayErrors.value = false

    formData.id = props.dataRow?.id ?? null
    formData.name = props.dataRow?.name ?? ''
    formData.sponsor = props.dataRow?.sponsor ?? ''
    formData.start_date = props.dataRow?.start_date ?? null
    formData.end_date = props.dataRow?.end_date ?? null
    formData.notes = props.dataRow?.notes ?? ''
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
