<template>
    <Dialog
        :breakpoints="{ '960px': '75vw', '640px': '100vw' }"
        :modal="true"
        :style="{ width: '600px' }"
        :visible="visible"
        header="Photo Preview"
        @update:visible="emit('update:visible', $event)"
        class="dark:bg-gray-900!"
    >
        <div v-if="photo" class="grid grid-cols-12 gap-4">
            <!-- Image -->
            <div class="col-span-12">
                <div class="rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <img
                        :src="photo.url"
                        :alt="photo.caption || `Photo #${photo.id}`"
                        class="w-full max-h-[480px] object-contain"
                    />
                </div>
            </div>

            <!-- Meta info -->
            <div class="col-span-12 space-y-1 text-sm text-gray-700 dark:text-gray-200">
                <div>
                    <span class="font-semibold">Tree ID:</span>
                    <span class="ml-1">#{{ photo.tree_id }}</span>
                </div>
                <div v-if="photo.caption">
                    <span class="font-semibold">Caption:</span>
                    <span class="ml-1">{{ photo.caption }}</span>
                </div>
                <div v-if="photo.source">
                    <span class="font-semibold">Source:</span>
                    <span class="ml-1 capitalize">{{ photo.source }}</span>
                </div>
                <div v-if="photo.captured_at">
                    <span class="font-semibold">Captured at:</span>
                    <span class="ml-1">{{ formattedCapturedAt }}</span>
                </div>
                <div v-if="photo.created_at">
                    <span class="font-semibold">Uploaded at:</span>
                    <span class="ml-1">{{ formattedCreatedAt }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Close" @click="close" />
        </template>
    </Dialog>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    photo: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['update:visible'])

const close = () => {
    emit('update:visible', false)
}

// simple formatting; you can replace with dayjs/moment if you already use one
const formattedCapturedAt = computed(() => {
    if (!props.photo?.captured_at) return ''
    return new Date(props.photo.captured_at).toLocaleString()
})

const formattedCreatedAt = computed(() => {
    if (!props.photo?.created_at) return ''
    return new Date(props.photo.created_at).toLocaleString()
})
</script>
