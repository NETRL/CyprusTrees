<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" :header="action === 'Create' ? 'Create GIS Layer' : 'Edit GIS Layer'" @show="initForm"
        @update:visible="emit('update:visible', $event)" class="dark:bg-gray-900! select-none">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <div class="col-span-4">
                <FormField v-model="formData.key" :displayErrors="displayErrors" label="Key"
                    placeholder="irrigation_lines" name="key" />
            </div>
            <div class="col-span-4">
                <FormField v-model="formData.name" :displayErrors="displayErrors" label="Internal Name" placeholder=""
                    name="name" />
            </div>
            <div class="col-span-4">
                <FormField v-model="formData.display_name" :displayErrors="displayErrors" label="Display Name"
                    placeholder="" name="display_name" />
            </div>
            <div class="col-span-4">
                <FormField v-model="formData.category" :displayErrors="displayErrors" label="Category" placeholder=""
                    name="category" />
            </div>
            <div class="col-span-4">
                <FormField v-model="formData.source" :displayErrors="displayErrors" label="Source" placeholder=""
                    name="source" />
            </div>
            <div class="col-span-4">
                <FormField component="Dropdown" filter v-model="formData.default_import_mode"
                    :displayErrors="displayErrors" label="Import Mode" name="default_import_mode"
                    :options="importOptions" optionLabel="label" optionValue="value" />
            </div>
            <div class="col-span-3">
                <FormField component="Checkbox" v-model="formData.is_active" binary :displayErrors="displayErrors"
                    label="Active" name="is_active" />
            </div>
            <div class="col-span-3">
                <FormField component="Checkbox" v-model="formData.is_editable" binary :displayErrors="displayErrors"
                    label="Editable" name="is_editable" />
            </div>

            <div class="col-span-12">
                <FormField component="Textarea" v-model="metadataText" :displayErrors="displayErrors"
                    label="Metadata (JSON)" name="metadata" @blur="syncMetadata" rows="10"
                    class="w-full rounded-lg border px-3 py-2 font-mono text-xs"
                    placeholder='{"style":{"type":"line","paint":{"line-width":2}}}' />
                <div v-if="formData.errors.metadata" class="text-xs text-red-500">
                    {{ formData.errors.metadata }}
                </div>
            </div>

        </form>

        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="closeForm" />
            <Button :label="action" class="p-button-text" icon="pi pi-check" @click="submit"
                :disabled="formData.processing" />
        </template>
    </Dialog>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import FormField from '@/Components/Primitives/FormField.vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    visible: { type: Boolean, default: false, },
    dataRow: { type: Object, default: null, },
    action: { type: String, default: '', },
    routeResource: { type: String, required: true, },
})


const emit = defineEmits(['update:visible'])

const importOptions = [
    { value: 'append', label: 'Append' },
    { value: 'replace', label: 'Replace' },
]

const formData = useForm({
    key: '',
    name: '',
    display_name: '',
    category: '',
    source: '',
    default_import_mode: 'append',
    is_active: true,
    is_editable: false,
    metadata: {},
})

const displayErrors = ref(false)

const closeForm = () => {
    emit('update:visible', false)
}

const initForm = () => {
    const row = props.dataRow

    displayErrors.value = false
    formData.clearErrors()

    formData.key = row?.key ?? null
    formData.name = row?.name ?? null
    formData.display_name = row?.display_name ?? null
    formData.category = row?.category ?? null
    formData.source = row?.source ?? null
    formData.default_import_mode = row?.default_import_mode ?? 'append'
    formData.is_active = row?.is_active ?? true
    formData.is_editable = row?.is_editable ?? false
    formData.metadata = row?.metadata ?? {}
}


const metadataText = ref(JSON.stringify(formData.metadata ?? {}, null, 2))


watch(
    () => formData.metadata,
    v => {
        metadataText.value = JSON.stringify(v ?? {}, null, 2)
    },
    { immediate: true }
)


const syncMetadata = () => {
    try {
        formData.metadata = metadataText.value ? JSON.parse(metadataText.value) : null
        formData.clearErrors()
    } catch (e) {
        console.log(e)
        formData.setError('metadata', 'Metadata must be valid JSON.')
    }
}

const submit = () => {
    syncMetadata()
    if (formData.hasErrors) return
    displayErrors.value = true;

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
    } else {
        router.patch(route(props.routeResource + '.update', props.dataRow.id), { ...formData }, {
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
