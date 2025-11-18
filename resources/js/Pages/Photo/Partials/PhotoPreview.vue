<template>
    <Dialog :breakpoints="{ '960px': '90vw', '640px': '100vw' }" :modal="true" :style="{ width: '800px' }"
        :visible="visible" :header="photo?.caption || `Photo #${photo?.id}`" @update:visible="close"
        class="dark:bg-gray-900 photo-preview-dialog" :dismissableMask="true">
        <div v-if="photo" class="space-y-6">
            <div class="relative bg-gray-100 dark:bg-gray-800 rounded-2xl overflow-hidden border-2 border-gray-200 dark:border-gray-700 h-[500px] flex items-center justify-center group select-none"
                @wheel.prevent="handleWheel">
                <div ref="imageContainer" class="w-full h-full flex items-center justify-center touch-none"
                    @mousedown="startDrag" @touchstart="startTouch" @dblclick="handleDoubleClick">
                    <img v-if="!imageError" :src="photo.url" :alt="photo.caption"
                        class="max-w-full max-h-full object-contain transition-transform duration-75 ease-linear will-change-transform"
                        :style="imageStyle" @load="onImageLoad" @error="imageError = true" draggable="false" />
                </div>

                <div v-if="!imageLoaded && !imageError"
                    class="absolute inset-0 flex items-center justify-center z-10 bg-gray-100 dark:bg-gray-800">
                    <i class="pi pi-spin pi-spinner text-3xl text-gray-400"></i>
                </div>

                <div v-if="imageError"
                    class="absolute inset-0 flex flex-col items-center justify-center z-10 text-gray-400">
                    <i class="pi pi-exclamation-triangle text-4xl mb-2 text-red-400"></i>
                    <p>Failed to load image</p>
                </div>

                <div
                    class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 bg-black/70 text-white px-3 py-1.5 rounded-full backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-20">
                    <button @click="adjustZoom(-0.5)" class="hover:text-brand-400 transition-colors">
                        <i class="pi pi-minus-circle"></i>
                    </button>
                    <span class="text-xs font-mono w-12 text-center">{{ Math.round(transform.scale * 100) }}%</span>
                    <button @click="adjustZoom(0.5)" class="hover:text-brand-400 transition-colors">
                        <i class="pi pi-plus-circle"></i>
                    </button>
                    <div class="w-px h-3 bg-white/20 mx-1"></div>
                    <button @click="resetZoom" class="text-xs hover:text-brand-400 transition-colors"
                        v-tooltip.top="'Reset'">
                        Reset
                    </button>
                </div>

                <div class="absolute top-3 right-3 flex gap-2 z-20">
                    <Button icon="pi pi-download" rounded severity="secondary"
                        class="w-10! h-10! bg-white/90! dark:bg-gray-800/90! shadow-lg border-none"
                        @click="downloadPhoto" v-tooltip.left="'Download'" />
                    <Button icon="pi pi-external-link" rounded severity="secondary"
                        class="w-10! h-10! bg-white/90! dark:bg-gray-800/90! shadow-lg border-none"
                        @click="openInNewTab" v-tooltip.left="'Open in new tab'" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <i class="pi pi-hashtag text-brand-500 mt-0.5"></i>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Photo ID</p>
                            <p class="text-sm font-medium dark:text-gray-200">{{ photo.id }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <i class="pi pi-calendar text-brand-500 mt-0.5"></i>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Captured</p>
                            <p class="text-sm font-medium dark:text-gray-200">{{ formattedCapturedAt }}</p>
                        </div>
                    </div>
                </div>
                <div v-if="photo.source" class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
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

            <!-- Caption (if exists) -->
            <div v-if="photo.caption" class="p-4 bg-linear-to-br from-brand-50 to-brand-100 dark:from-brand-900/20 dark:to-brand-800/20 
                    rounded-xl border border-brand-200 dark:border-brand-800">
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
            <div v-if="photo.status === 'failed' && photo.error_message"
                class="p-4 bg-error-50 dark:bg-error-900/20 rounded-xl border border-error-200 dark:border-error-800">
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
                <Button text icon="pi pi-times" label="Close" severity="secondary" @click="close" />
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    visible: Boolean,
    photo: Object,
});

const emit = defineEmits(['update:visible']);

// State
const imageLoaded = ref(false);
const imageError = ref(false);
const imageContainer = ref(null);

// Transform State (The core of the smooth zoom)
const transform = ref({ scale: 1, x: 0, y: 0 });
const isDragging = ref(false);
const startPos = ref({ x: 0, y: 0 });

// Configuration
const MIN_SCALE = 1;
const MAX_SCALE = 5;
const ZOOM_STEP = 0.5;

// Watchers
watch([() => props.visible, () => props.photo], () => {
    resetZoom();
    imageLoaded.value = false;
    imageError.value = false;
});

// Computed Style for the Image
const imageStyle = computed(() => {
    const { scale, x, y } = transform.value;
    return {
        transform: `translate(${x}px, ${y}px) scale(${scale})`,
        cursor: isDragging.value ? 'grabbing' : scale > 1 ? 'grab' : 'default'
    };
});

// -------------------
// Zoom Logic
// -------------------

const clamp = (num, min, max) => Math.min(Math.max(num, min), max);

const handleWheel = (e) => {
    const delta = e.deltaY > 0 ? -0.2 : 0.2;
    const newScale = clamp(transform.value.scale + delta, MIN_SCALE, MAX_SCALE);

    if (newScale === transform.value.scale) return; // Limit reached

    // Simple zoom (center focused) - logic can be upgraded to "zoom to mouse" if needed
    // but center zoom is usually stable enough for simple previews
    transform.value.scale = newScale;

    // If zooming out, we might need to clamp positions to prevent "flying away"
    if (newScale === MIN_SCALE) {
        transform.value.x = 0;
        transform.value.y = 0;
    }
};

const adjustZoom = (delta) => {
    transform.value.scale = clamp(transform.value.scale + delta, MIN_SCALE, MAX_SCALE);
    if (transform.value.scale === MIN_SCALE) {
        transform.value.x = 0;
        transform.value.y = 0;
    }
};

const resetZoom = () => {
    transform.value = { scale: 1, x: 0, y: 0 };
};

const handleDoubleClick = () => {
    if (transform.value.scale > 1) {
        resetZoom();
    } else {
        transform.value.scale = 2; // Quick zoom to 2x
    }
};

// -------------------
// Pan/Drag Logic
// -------------------

const startDrag = (e) => {
    if (transform.value.scale <= 1) return; // Only allow drag if zoomed
    e.preventDefault(); // Prevent native drag

    isDragging.value = true;
    startPos.value = {
        x: e.clientX - transform.value.x,
        y: e.clientY - transform.value.y
    };

    window.addEventListener('mousemove', onDrag);
    window.addEventListener('mouseup', stopDrag);
};

const onDrag = (e) => {
    if (!isDragging.value) return;
    e.preventDefault();

    let newX = e.clientX - startPos.value.x;
    let newY = e.clientY - startPos.value.y;

    // Add boundaries here if we want to strictly prevent dragging off screen

    transform.value.x = newX;
    transform.value.y = newY;
};

const stopDrag = () => {
    isDragging.value = false;
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', stopDrag);
};

// Touch support (basic)
const startTouch = (e) => {
    if (transform.value.scale <= 1 || e.touches.length > 1) return;

    isDragging.value = true;
    const touch = e.touches[0];
    startPos.value = {
        x: touch.clientX - transform.value.x,
        y: touch.clientY - transform.value.y
    };

    window.addEventListener('touchmove', onTouchMove, { passive: false });
    window.addEventListener('touchend', stopTouch);
};

const onTouchMove = (e) => {
    if (!isDragging.value) return;
    e.preventDefault(); // Stop page scroll
    const touch = e.touches[0];
    transform.value.x = touch.clientX - startPos.value.x;
    transform.value.y = touch.clientY - startPos.value.y;
};

const stopTouch = () => {
    isDragging.value = false;
    window.removeEventListener('touchmove', onTouchMove);
    window.removeEventListener('touchend', stopTouch);
};

// -------------------
// Utils
// -------------------

const onImageLoad = () => {
    imageLoaded.value = true;
};

const formattedCapturedAt = computed(() => {
    if (!props.photo?.captured_at) return '';
    return new Date(props.photo.captured_at).toLocaleString();
});

const downloadPhoto = async () => {
    if (!props.photo?.url) return;
    const link = document.createElement('a');
    link.href = props.photo.url;
    link.target = '_blank';
    link.download = `tree-${props.photo.tree_id}-photo-${props.photo.id}.jpg`;
    link.click();
};

const openInNewTab = () => {
    if (props.photo?.url) window.open(props.photo.url, '_blank');
};

const close = () => {
    emit('update:visible', false);
};

// Cleanup
onUnmounted(() => {
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', stopDrag);
    window.removeEventListener('touchmove', onTouchMove);
    window.removeEventListener('touchend', stopTouch);
});
</script>

<style scoped>
.photo-preview-dialog :deep(.p-dialog-content) {
    padding: 1.5rem;
}

/* Optimizes drag performance */
.will-change-transform {
    will-change: transform;
}
</style>