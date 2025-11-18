<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '550px' }"
        :visible="visible" :header="action === 'Create' ? 'Add Photos' : 'Edit Photo'" @show="initForm"
        @update:visible="emit('update:visible', $event)" class="dark:bg-gray-900!">

        <form class="grid grid-cols-12 w-full gap-4" @submit.prevent="submit">
            <!-- Caption -->
            <div class="col-span-12">
                <FormField v-model="formData.caption" :displayErrors="displayErrors" label="Caption" name="caption"
                    placeholder="Enter a description for the photo(s)" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ action === 'Create' ? 'This caption will be applied to all uploaded photos' : 'Update the caption for this photo' }}
                </p>
            </div>

            <!-- Photo Upload Section -->
            <div class="col-span-12">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ action === 'Create' ? 'Select Photos' : 'Replace Photo' }}
                    <span v-if="action === 'Create'" class="text-error-500">*</span>
                </label>

                <!-- Upload Buttons -->
                <div class="flex flex-col sm:flex-row gap-2 mb-3">
                    <Button type="button" icon="pi pi-upload" label="Upload from Device" class="flex-1 justify-center"
                        @click="uploadInput?.click()" />

                    <Button type="button" icon="pi pi-camera" label="Use Camera" severity="secondary"
                        class="flex-1 justify-center" @click="cameraInput?.click()" />

                    <!-- Hidden file inputs -->
                    <input ref="uploadInput" type="file" accept="image/jpeg,image/jpg,image/png,image/webp"
                        class="hidden" :multiple="action === 'Create'" @change="(e) => onFileChange(e, 'upload')" />
                    <input ref="cameraInput" type="file" accept="image/*" capture="environment" class="hidden"
                        :multiple="action === 'Create'" @change="(e) => onFileChange(e, 'camera')" />
                </div>
                <!-- File Info -->
                <div v-if="formData.photos.length > 0" class="flex items-center gap-2 p-3 bg-brand-50 dark:bg-brand-900/20 
                        border border-brand-200 dark:border-brand-800 rounded-lg">
                    <i class="pi pi-check-circle text-brand-500"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ formData.photos.length }} {{ formData.photos.length === 1 ? 'photo' : 'photos' }} selected
                    </span>
                </div>
                <!-- Requirements -->
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                    <p><i class="pi pi-info-circle"></i> Accepted formats: JPEG, JPG, PNG, WebP</p>
                    <p><i class="pi pi-info-circle"></i> Maximum file size: 15MB per photo</p>
                    <p><i class="pi pi-info-circle"></i> Maximum dimensions: 6000x6000 pixels</p>
                    <p v-if="action === 'Create'"><i class="pi pi-info-circle"></i> You can select up to 20 photos at
                        once</p>
                </div>

                <!-- Validation Error -->
                <InputError id="photos-help" class="mt-2"
                    :message="$page.props.errors['photos'] || $page.props.errors['photos.0'] || $page.props.errors['photos.*']" />
            </div>

            <!-- Preview Section -->
            <div v-if="previewUrls.length > 0 || formData.url" class="col-span-12">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Preview
                </label>

                <!-- Single Photo Preview (Edit mode, no new files) -->
                <div v-if="!previewUrls.length && formData.url"
                    class="relative rounded-xl overflow-hidden border-2 border-gray-200 dark:border-gray-700">
                    <img :src="formData.url" class="w-full max-h-80 object-contain bg-gray-50 dark:bg-gray-800"
                        alt="Current photo" />
                    <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/60 to-transparent">
                        <p class="text-white text-xs">Current photo</p>
                    </div>
                </div>

                <!-- Multiple Photos Preview Grid -->
                <div v-else class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                    <div v-for="(src, idx) in previewUrls" :key="idx" class="relative aspect-square rounded-lg overflow-hidden group
                            border-2 border-gray-200 dark:border-gray-700 hover:border-brand-500
                            transition-all duration-200">
                        <img :src="src" class="w-full h-full object-cover" alt="Selected photo preview" />

                        <!-- Remove button overlay -->
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100
                                transition-opacity duration-200 flex items-center justify-center cursor-pointer"
                            @click="removePhoto(idx)">
                            <Button icon="pi pi-times" severity="danger" rounded class="!w-10 !h-10 !p-0"
                                @click.stop="removePhoto(idx)" />
                        </div>

                        <!-- Photo number badge -->
                        <div class="absolute top-1 left-1 bg-black/70 text-white px-2 py-0.5 rounded text-xs">
                            {{ idx + 1 }}
                        </div>
                    </div>
                </div>
                <!-- Helpful hint -->
                <p v-if="previewUrls.length > 1" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    <i class="pi pi-info-circle"></i> Hover over a photo to remove it
                </p>
            </div>
            <!-- Processing Note -->
            <div v-if="formData.photos.length > 0" class="col-span-12 p-3 bg-blue-50 dark:bg-blue-900/20 
                    border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex gap-2">
                    <i class="pi pi-info-circle text-blue-500 mt-0.5"></i>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <p class="font-medium mb-1">Photos will be processed automatically:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-xs">
                            <li>Converted to JPEG format</li>
                            <li>Optimized for web display</li>
                            <li>Rotated to correct orientation</li>
                            <li>Resized if larger than 2560px</li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>

        <template #footer>
            <div class="flex justify-between items-center w-full">
                <Button text icon="pi pi-times" label="Cancel" severity="secondary" @click="closeForm"
                    :disabled="isSubmitting" />
                <Button
                    :label="isSubmitting ? 'Uploading...' : (action === 'Create' ? 'Upload Photos' : 'Save Changes')"
                    icon="pi pi-check" :loading="isSubmitting" @click="submit"
                    :disabled="isSubmitting || (action === 'Create' && formData.photos.length === 0)" />
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import FormField from '@/Components/Primitives/FormField.vue'
import InputError from '@/Components/InputError.vue'

const MAX_BATCH_BYTES = 60 * 1024 * 1024; // 50MB, below nginx 64MB

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

const emit = defineEmits(['update:visible', 'created', 'updated']);

const uploadInput = ref(null);
const cameraInput = ref(null);
const isSubmitting = ref(false);
const displayErrors = ref(false);
const previewUrls = ref([]);

// state
const formData = ref({
    caption: '',
    photos: [],
    url: null,
    source: '',
});

const initForm = () => {
    if (props.action === 'Edit' && props.dataRow) {
        formData.value = {
            caption: props.dataRow.caption || '',
            photos: [],
            url: props.dataRow.url,
        };
    } else {
        formData.value = {
            caption: '',
            photos: [],
            url: null,
        };
    }
    previewUrls.value = [];
    displayErrors.value = false;
};

const onFileChange = (event, source) => {
    const files = Array.from(event.target.files || []);

    formData.value.source = source

    if (props.action === 'Edit') {
        // In edit mode, only allow one file
        if (files.length > 0) {
            formData.value.photos = [files[0]];
            generatePreviews([files[0]]);
        }
    } else {
        // In create mode, allow multiple (up to 20)
        const totalFiles = formData.value.photos.length + files.length;
        if (totalFiles > 20) {
            alert('You can only upload up to 20 photos at once.');
            return;
        }
        formData.value.photos.push(...files);
        generatePreviews(formData.value.photos);
    }

    // Clear input
    event.target.value = '';
};

const generatePreviews = (files) => {
    previewUrls.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrls.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removePhoto = (index) => {
    formData.value.photos.splice(index, 1);
    previewUrls.value.splice(index, 1);
};

const chunkFilesBySize = (files, maxBytes) => {
    const chunks = [];
    let currentChunk = [];
    let currentSize = 0;

    for (const file of files) {
        // If single file itself is > maxBytes, just put it alone in its own chunk
        if (file.size > maxBytes) {
            if (currentChunk.length) {
                chunks.push(currentChunk);
                currentChunk = [];
                currentSize = 0;
            }
            chunks.push([file]);
            continue;
        }

        if (currentSize + file.size > maxBytes && currentChunk.length) {
            chunks.push(currentChunk);
            currentChunk = [];
            currentSize = 0;
        }

        currentChunk.push(file);
        currentSize += file.size;
    }

    if (currentChunk.length) {
        chunks.push(currentChunk);
    }

    return chunks;
};

const submit = async () => {
    if (isSubmitting.value) return;

    if (props.action === 'Create' && formData.value.photos.length === 0) {
        displayErrors.value = true;
        return;
    }

    isSubmitting.value = true;
    displayErrors.value = true;

    try {
        if (props.action === 'Create') {
            const files = formData.value.photos;

            // Split into size-aware batches
            const batches = chunkFilesBySize(files, MAX_BATCH_BYTES);

            for (const batch of batches) {
                const formDataObj = new FormData();
                formDataObj.append('tree_id', props.treeId);
                formDataObj.append('caption', formData.value.caption || '');
                formDataObj.append('source', formData.value.source || '');

                batch.forEach((file, index) => {
                    formDataObj.append(`photos[${index}]`, file);
                });

                await axios.post(route(`${props.routeResource}.store`), formDataObj, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                    // you can also add onUploadProgress here if you want progress UI
                });
            }

            emit('created');
        } else {
            // EDIT: still single file update as before
            const formDataObj = new FormData();
            formDataObj.append('tree_id', props.treeId);
            formDataObj.append('caption', formData.value.caption || '');
            formDataObj.append('source', formData.value.source || '');

            if (formData.value.photos[0]) {
                formDataObj.append('photo', formData.value.photos[0]);
            }

            formDataObj.append('_method', 'PUT');
            await axios.post(route(`${props.routeResource}.update`, props.dataRow.id), formDataObj, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            emit('updated');
        }

        closeForm();
    } catch (error) {
        console.error('Upload error:', error);
        // optional: show toast
    } finally {
        isSubmitting.value = false;
    }
};

const closeForm = () => {
    emit('update:visible', false);
    formData.value = { caption: '', photos: [], url: null };
    previewUrls.value = [];
};

</script>
