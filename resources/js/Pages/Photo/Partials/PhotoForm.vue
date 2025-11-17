<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Photo Details" @show="initForm" @update:visible="emit('update:visible', $event)"
        class="dark:bg-gray-900!">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <!-- Caption (applied to all in Create, or to single in Edit) -->
            <div class="col-span-12">
                <FormField v-model="formData.caption" :displayErrors="displayErrors" label="Caption" name="caption" />
            </div>

            <!-- Photo upload / camera -->
            <div class="col-span-12">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Photo <span class="text-error-500">*</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    <!-- Upload from device -->
                    <Button type="button" icon="pi pi-upload" label="Upload from device"
                        @click="uploadInput?.click()" />

                    <!-- Use camera (mobile-friendly) -->
                    <Button type="button" icon="pi pi-camera" label="Use camera" class="p-button-secondary"
                        @click="cameraInput?.click()" />

                    <!-- Hidden file inputs -->
                    <input ref="uploadInput" type="file" accept="image/*" class="hidden" multiple
                        @change="(e) => onFileChange(e, 'upload')" />
                    <input ref="cameraInput" type="file" accept="image/*" capture="environment" class="hidden"
                        @change="(e) => onFileChange(e, 'camera')" />
                </div>

                <p v-if="formData.photos.length" class="mt-2 text-xs text-gray-500">
                    Selected {{ formData.photos.length }} photo{{ formData.photos.length > 1 ? 's' : '' }}
                </p>
            </div>

            <!-- Preview (existing or newly selected) -->
            <div v-if="previewUrls.length || formData.url" class="col-span-12">
                <!-- Existing photo (Edit, no new file yet) -->
                <img v-if="!previewUrls.length && formData.url" :src="formData.url"
                    class="w-full max-h-64 rounded-xl object-cover" alt="Photo preview" />

                <!-- New selection (Create or Edit with new file[s]) -->
                <div v-else class="grid grid-cols-3 gap-2">
                    <div v-for="(src, idx) in previewUrls" :key="idx"
                        class="w-full h-24 relative rounded-lg overflow-hidden group">
                        <img :src="src" class="w-full h-full object-cover" alt="Selected photo preview" />
                        <!-- Hover overlay with remove button -->
                        <div class="absolute inset-0 bg-white/70 dark:bg-black/70 rounded-lg opacity-0
                   transition-opacity duration-200 group-hover:opacity-100
                   flex items-center justify-center" @click="removePhoto(idx)">
                            <Button icon="pi pi-times"
                                class="border-transparent! bg-brand-600! w-8! h-8! p-0! min-w-0! rounded-full!"
                                @click.stop="removePhoto(idx)" />
                        </div>
                    </div>
                </div>
            </div>

            <InputError id="photos-help" class="mt-2 col-span-12"
                :message="$page.props.errors['photos'] || $page.props.errors['photos.0']" />

        </form>

        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="closeForm" />
            <Button :label="action" class="p-button-text" icon="pi pi-check" @click="submit" />
        </template>
    </Dialog>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import FormField from '@/Components/Primitives/FormField.vue'
import InputError from '@/Components/InputError.vue'

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
    treeId: {
        type: Number,
        default: null,
    },
})

const emit = defineEmits(['update:visible'])

// state
const formData = reactive({
    id: null,
    tree_id: null,
    caption: '',
    url: '',
    captured_at: null,
    source: '',
    photos: [], // File object for upload
})


const displayErrors = ref(false)

// refs to hidden inputs
const uploadInput = ref(null)
const cameraInput = ref(null)

// preview URL for selected File or existing URL
const previewUrls = computed(() => {
    if (!formData.photos.length) return []
    return formData.photos.map(f => URL.createObjectURL(f))
})

// methods
const closeForm = () => {
    emit('update:visible', false)
}

const initForm = () => {
    displayErrors.value = false

    formData.id = props.dataRow?.id ?? null
    formData.tree_id = props.dataRow?.tree_id ?? props.treeId ?? null
    formData.caption = props.dataRow?.caption ?? ''
    formData.url = props.dataRow?.url ?? ''
    formData.captured_at = props.dataRow?.captured_at ?? null
    formData.source = props.dataRow?.source ?? ''
    formData.photos = [] // reset file on open
}

const removePhoto = (index) => {
    if (index < 0 || index >= formData.photos.length) return

    // revoke object URL to be super clean
    const file = formData.photos[index]
    const url = URL.createObjectURL(file)
    URL.revokeObjectURL(url)

    formData.photos.splice(index, 1)
}


const onFileChange = (event, source) => {
    const files = event.target.files ? Array.from(event.target.files) : []
    if (!files.length) return

    formData.source = source // 'upload' | 'camera'

    if (props.action === 'Edit') {
        // For Edit: only allow one replacement; take first file
        formData.photos = [files[0]]
    } else {
        // For Create: allow multi-upload
        formData.photos = files
    }

    // reset input so selecting the same file again works
    event.target.value = ''
}

const submit = () => {
    displayErrors.value = false

    if (!formData.tree_id) {
        displayErrors.value = true
        return
    }

    if (props.action === 'Create') {
        // Mass import: multiple photos
        const payload = {
            tree_id: formData.tree_id,
            caption: formData.caption,
            source: formData.source,
            photos: formData.photos, // array of Files
        }

        router.post(
            route(props.routeResource + '.store'),
            payload,
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm()
                },
                onFinish: () => {
                    displayErrors.value = true
                },
            }
        )
    } else if (props.action === 'Edit') {
        // Single replace + caption update
        const payload = {
            id: formData.id,
            tree_id: formData.tree_id,
            caption: formData.caption,
            source: formData.source,
            captured_at: formData.captured_at,
            photo: formData.photos[0] ?? null, // single file or null
        }

        router.post(
            route(props.routeResource + '.update', formData.id),
            { ...payload, _method: 'PATCH' },
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeForm()
                },
                onFinish: () => {
                    displayErrors.value = true
                },
            }
        )
    }
}
</script>
