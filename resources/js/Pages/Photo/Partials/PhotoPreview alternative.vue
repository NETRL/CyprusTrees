<template>
    <Dialog
        :breakpoints="{ '960px': '90vw', '640px': '100vw' }"
        :modal="true"
        :style="{ width: '800px' }"
        :visible="visible"
        :header="photo?.caption || `Photo #${photo?.id}`"
        @update:visible="emit('update:visible', $event)"
        class="dark:bg-gray-900! photo-preview-dialog"
        :dismissableMask="true"
    >
        <div v-if="photo" class="space-y-6">
            <!-- Image Container with Zoom -->
            <div class="relative">
                <div 
                    ref="imageContainer"
                    class="rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 
                        border-2 border-gray-200 dark:border-gray-700 relative group select-none"
                    :class="{ 'cursor-zoom-in': !zoomed, 'cursor-zoom-out': zoomed, 'overflow-auto': zoomed }"
                    @click="toggleZoom"
                    @mousedown="startDrag"
                    @mousemove="onDrag"
                    @mouseup="stopDrag"
                    @mouseleave="stopDrag"
                >
                    <img
                        :src="photo.url"
                        :alt="photo.caption || `Photo #${photo.id}`"
                        class="w-full transition-transform duration-300 select-none pointer-events-none"
                        :class="zoomed ? 'scale-150 cursor-move' : 'max-h-[480px] object-contain'"
                        @load="imageLoaded = true"
                        @error="imageError = true"
                        draggable="false"
                    />
                    
                    <!-- Loading State -->
                    <div 
                        v-if="!imageLoaded && !imageError" 
                        class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800"
                    >
                        <i class="pi pi-spin pi-spinner text-3xl text-gray-400"></i>
                    </div>

                    <!-- Error State -->
                    <div 
                        v-if="imageError" 
                        class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-800"
                    >
                        <i class="pi pi-exclamation-triangle text-4xl text-error-400 mb-2"></i>
                        <p class="text-sm text-gray-500">Failed to load image</p>
                    </div>

                    <!-- Zoom hint -->
                    <div 
                        class="absolute bottom-3 right-3 bg-black/70 text-white px-3 py-1.5 rounded-full 
                            text-xs opacity-0 group-hover:opacity-100 transition-opacity duration-200
                            flex items-center gap-1"
                    >
                        <i :class="zoomed ? 'pi pi-search-minus' : 'pi pi-search-plus'"></i>
                        {{ zoomed ? 'Click to zoom out' : 'Click to zoom in' }}
                    </div>
                </div>

                <!-- Quick Actions Overlay -->
                <div class="absolute top-3 right-3 flex gap-2">
                    <Button
                        icon="pi pi-download"
                        rounded
                        severity="secondary"
                        class="!w-10 !h-10 !bg-white/90 dark:!bg-gray-800/90 shadow-lg"
                        @click="downloadPhoto"
                        v-tooltip.left="'Download'"
                    />
                    <Button
                        icon="pi pi-external-link"
                        rounded
                        severity="secondary"
                        class="!w-10 !h-10 !bg-white/90 dark:!bg-gray-800/90 shadow-lg"
                        @click="openInNewTab"
                        v-tooltip.left="'Open in new tab'"
                    />
                </div>

                <!-- Status Badge -->
                <div 
                    v-if="photo.status === 'processing'"
                    class="absolute top-3 left-3 bg-yellow-500 text-white px-3 py-1.5 rounded-full 
                        text-xs font-medium flex items-center gap-2 shadow-lg"
                >
                    <i class="pi pi-spin pi-spinner"></i>
                    Processing
                </div>
                <div 
                    v-else-if="photo.status === 'failed'"
                    class="absolute top-3 left-3 bg-error-500 text-white px-3 py-1.5 rounded-full 
                        text-xs font-medium flex items-center gap-2 shadow-lg"
                >
                    <i class="pi pi-exclamation-triangle"></i>
                    Processing Failed
                </div>
            </div>

            <!-- Photo Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Left Column -->
                <div class="space-y-3">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <i class="pi pi-hashtag text-brand-500 mt-0.5"></i>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Photo ID</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ photo.id }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <i class="pi pi-sitemap text-brand-500 mt-0.5"></i>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Tree ID</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">#{{ photo.tree_id }}</p>
                        </div>
                    </div>

                    <div 
                        v-if="photo.source" 
                        class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                    >
                        <i :class="photo.source === 'camera' ? 'pi pi-camera' : 'pi pi-upload'" 
                            class="text-brand-500 mt-0.5"></i>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Source</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 capitalize">
                                {{ photo.source }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-3">
                    <div 
                        v-if="photo.captured_at" 
                        class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                    >
                        <i class="pi pi-calendar text-brand-500 mt-0.5"></i>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Captured</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ formattedCapturedAt }}
                            </p>
                        </div>
                    </div>

                    <div 
                        v-if="photo.created_at" 
                        class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                    >
                        <i class="pi pi-cloud-upload text-brand-500 mt-0.5"></i>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Uploaded</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ formattedCreatedAt }}
                            </p>
                        </div>
                    </div>

                    <div 
                        v-if="photo.status" 
                        class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                    >
                        <i class="pi pi-info-circle text-brand-500 mt-0.5"></i>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Status</p>
                            <div class="flex items-center gap-2">
                                <span 
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': photo.status === 'ready',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': photo.status === 'processing',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': photo.status === 'failed'
                                    }"
                                >
                                    {{ photo.status === 'ready' ? 'Ready' : photo.status === 'processing' ? 'Processing' : 'Failed' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Caption (if exists) -->
            <div 
                v-if="photo.caption" 
                class="p-4 bg-gradient-to-br from-brand-50 to-brand-100 dark:from-brand-900/20 dark:to-brand-800/20 
                    rounded-xl border border-brand-200 dark:border-brand-800"
            >
                <div class="flex items-start gap-3">
                    <i class="pi pi-comment text-brand-500 mt-1"></i>
                    <div class="flex-1">
                        <p class="text-xs font-medium text-brand-700 dark:text-brand-400 mb-1">Caption</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ photo.caption }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Error Message (if failed) -->
            <div 
                v-if="photo.status === 'failed' && photo.error_message" 
                class="p-4 bg-error-50 dark:bg-error-900/20 rounded-xl border border-error-200 dark:border-error-800"
            >
                <div class="flex items-start gap-3">
                    <i class="pi pi-exclamation-circle text-error-500 mt-1"></i>
                    <div class="flex-1">
                        <p class="text-xs font-medium text-error-700 dark:text-error-400 mb-1">Error</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ photo.error_message }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-between items-center w-full">
                <Button 
                    text
                    icon="pi pi-times" 
                    label="Close" 
                    severity="secondary"
                    @click="close" 
                />
                <div class="flex gap-2">
                    <Button
                        v-if="photo?.status === 'ready'"
                        icon="pi pi-download"
                        label="Download"
                        severity="secondary"
                        @click="downloadPhoto"
                    />
                    <Button
                        icon="pi pi-external-link"
                        label="Open Original"
                        @click="openInNewTab"
                    />
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    visible: Boolean,
    photo: Object,
});

const emit = defineEmits(['update:visible']);

const zoomed = ref(false);
const imageLoaded = ref(false);
const imageError = ref(false);
const imageContainer = ref(null);

// Dragging state
const isDragging = ref(false);
const dragStart = ref({ x: 0, y: 0 });
const scrollStart = ref({ x: 0, y: 0 });

// Reset states when dialog opens/closes or photo changes
watch([() => props.visible, () => props.photo], () => {
    zoomed.value = false;
    imageLoaded.value = false;
    imageError.value = false;
    isDragging.value = false;
});

const formattedCapturedAt = computed(() => {
    if (!props.photo?.captured_at) return '';
    const date = new Date(props.photo.captured_at);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
});

const formattedCreatedAt = computed(() => {
    if (!props.photo?.created_at) return '';
    const date = new Date(props.photo.created_at);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
});

const toggleZoom = (e) => {
    // Don't toggle if we just finished dragging
    if (isDragging.value) {
        return;
    }
    
    if (imageLoaded.value && !imageError.value) {
        zoomed.value = !zoomed.value;
        
        // Reset scroll position when zooming out
        if (!zoomed.value && imageContainer.value) {
            imageContainer.value.scrollLeft = 0;
            imageContainer.value.scrollTop = 0;
        }
    }
};

const startDrag = (e) => {
    if (!zoomed.value) return;
    
    e.preventDefault();
    isDragging.value = true;
    dragStart.value = { x: e.clientX, y: e.clientY };
    
    if (imageContainer.value) {
        scrollStart.value = {
            x: imageContainer.value.scrollLeft,
            y: imageContainer.value.scrollTop
        };
    }
};

const onDrag = (e) => {
    if (!isDragging.value || !zoomed.value || !imageContainer.value) return;
    
    e.preventDefault();
    
    const deltaX = e.clientX - dragStart.value.x;
    const deltaY = e.clientY - dragStart.value.y;
    
    imageContainer.value.scrollLeft = scrollStart.value.x - deltaX;
    imageContainer.value.scrollTop = scrollStart.value.y - deltaY;
};

const stopDrag = () => {
    // Add a small delay before resetting to prevent toggle on drag end
    if (isDragging.value) {
        setTimeout(() => {
            isDragging.value = false;
        }, 100);
    }
};

const downloadPhoto = async () => {
    if (!props.photo?.url) return;
    
    try {
        const response = await fetch(props.photo.url);
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `tree-${props.photo.tree_id}-photo-${props.photo.id}.jpg`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Download failed:', error);
        // Fallback: open in new tab
        window.open(props.photo.url, '_blank');
    }
};

const openInNewTab = () => {
    if (props.photo?.url) {
        window.open(props.photo.url, '_blank');
    }
};

const close = () => {
    emit('update:visible', false);
};
</script>

<style scoped>
.photo-preview-dialog :deep(.p-dialog-content) {
    padding: 1.5rem;
}

.cursor-zoom-in {
    cursor: zoom-in;
}

.cursor-zoom-out {
    cursor: zoom-out;
}

.cursor-move {
    cursor: move;
}

.select-none {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}
</style>