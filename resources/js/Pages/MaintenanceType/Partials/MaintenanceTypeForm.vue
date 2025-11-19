<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Maintenance Type Details" @show="initForm" @update:visible="emit('update:visible', $event)"
        class="dark:bg-gray-900! select-none">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <!-- Name -->
            <div class="col-span-12">
                <FormField v-model="formData.name" :displayErrors="displayErrors" label="Name" name="name" />
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
    type_id: null,
    name: '',
})

const displayErrors = ref(false)

// methods
const closeForm = () => {
    emit('update:visible', false)
}

const initForm = () => {
    const row = props.dataRow

    displayErrors.value = false

    formData.type_id = row?.type_id ?? null
    formData.name = row?.name ?? ''
}

const submit = () => {
    if (props.action === 'Create') {
        router.post(route(props.routeResource+'.store'), { ...formData }, {
            preserveScroll: true,
            onSuccess: () => {
                closeForm()
            },
            onFinish: () => {
                displayErrors.value = true
            },
        })
    } else if (props.action === 'Edit') {
        router.patch(route(props.routeResource+'.update', formData.type_id), { ...formData }, {
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
