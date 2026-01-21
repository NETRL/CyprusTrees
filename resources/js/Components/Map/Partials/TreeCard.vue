<template>
    <!-- Desktop Card -->
    <aside :class="[
        'max-lg:hidden absolute right-0 top-0 m-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-2xl transition-all duration-300 ease-in-out z-50',
        'max-h-[calc(100vh-7rem)] overflow-hidden w-[420px] flex flex-col',
        {
            'opacity-100 translate-x-0 pointer-events-auto': shouldShowPanel,
            'opacity-0 translate-x-4 pointer-events-none': !shouldShowPanel,
        },
    ]">
        <MapTreeForm v-if="isCreating || isEditing" v-model:visible="formVisible" routeResource="trees"
            :action="formAction" :markerLatLng="markerLatLng" :dataRow="props.selected" />

        <TreeCardContent v-else :hovered="props.hovered" :selected="props.selected" :isHovered="isHovered"
            :isSelected="isSelected" @editClick="onEditClick" />
    </aside>

    <!-- Mobile Bottom Sheet -->
    <BottomSheet v-model:state="treeSheetState" :showFab="false" :showBackdrop="false" :heightRatio="0.75">
        <div class="flex flex-col h-full px-5 pt-4 pb-6 w-full sm:items-center">
            <MapTreeForm v-if="isCreating || isEditing" v-model:visible="formVisible" routeResource="trees"
                :action="formAction" :markerLatLng="markerLatLng" :dataRow="props.selected" />

            <TreeCardContent v-else :hovered="props.hovered" :selected="props.selected" :isHovered="isHovered"
                :isSelected="isSelected" @editClick="onEditClick" />
        </div>
    </BottomSheet>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import TreeCardContent from '@/Components/Map/Partials/TreeCardContent.vue'
import BottomSheet from '@/Components/Map/Partials/BottomSheet.vue'
import MapTreeForm from '@/Components/Map/Partials/MapTreeForm.vue'

const emit = defineEmits(['cancelCreate'])

const props = defineProps({
    hovered: {
        type: Object,
        default: null
    },
    selected: {
        type: Object,
        default: null
    },
    markerLatLng: {
        type: Object,
        default: null
    },
    pinClickFlag: {
        type: Number,
        default: 0
    },
})

const formVisible = ref(false)

const isHovered = computed(() => props.hovered !== null)
const isSelected = computed(() => props.selected !== null)
const isCreating = computed(() => props.markerLatLng !== null)
const isEditing = ref(false)
const formAction = ref('');      // 'Create' or 'Edit'


// single source of truth for visibility
const shouldShowPanel = computed(() => isHovered.value || isSelected.value || isCreating.value || isEditing.value)

// local state only for this sheet
const treeSheetState = ref('closed')

watch(formVisible, v => {
    if (v === false) {
        emit('cancelCreate')
        isEditing.value = false
    }
})

// when we enter/exit create mode, toggle the form
watch(
    () => props.markerLatLng,
    (value) => {
        formVisible.value = !!value
        formAction.value = 'Create'

        if (value) {
            treeSheetState.value = 'mid'
        }
    }
)

watch(
    () => props.pinClickFlag,
    () => {
        // only reopen if we’re in create mode (pin exists) and sheet is closed
        if (props.markerLatLng && treeSheetState.value === 'closed') {
            treeSheetState.value = 'mid'
        }
    }
)


// open sheet when state exists, otherwise close
watch(
    shouldShowPanel,
    (open) => {
        treeSheetState.value = open ? 'mid' : 'closed'
    },
    { immediate: true }
)

const onEditClick = () => {
    if (!props.selected) return
    if (props.markerLatLng) emit('cancelCreate')
    isEditing.value = true
    formVisible.value = true
    formAction.value = 'Edit'
}
</script>
