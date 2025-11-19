<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Species Details" @show="initForm" @update:visible="emit('update:visible', $event)"
        class="dark:bg-gray-900! select-none">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <!-- Name -->
            <div class="col-span-12">
                <FormField v-model="formData.name" :displayErrors="displayErrors" label="Name" name="name" />
            </div>

            <!-- City -->
            <div class="col-span-12">
                <FormField v-model="formData.city" :displayErrors="displayErrors" label="City" name="city" />
            </div>

            <!-- District -->
            <div class="col-span-12">
                <FormField v-model="formData.district" :displayErrors="displayErrors" label="District"
                    name="district" />
            </div>

            <!-- Geom Ref -->
            <div class="col-span-12">
                <FormField v-model="formData.geom_ref" :displayErrors="displayErrors" label="Geometric Reference"
                    name="geom_ref" />

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
    city: '',
    district: '',
    geom_ref: null,
    canopy_class: null,
    notes: '',
})

const displayErrors = ref(false)

// methods
const closeForm = () => {
    emit('update:visible', false)
}

const initForm = () => {
    const row = props.dataRow

    displayErrors.value = false

    formData.id = row?.id ?? null
    formData.name = row?.name ?? ''
    formData.city = row?.city ?? ''
    formData.district = row?.district ?? ''
    formData.geom_ref = row?.geom_ref ?? null
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
