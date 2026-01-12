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
        <TreeForm v-if="isCreating" v-model:visible="formVisible" routeResource="trees" action="Create" :markerLatLng="markerLatLng" />
        <TreeCardContent v-else :hovered="props.hovered" :selected="props.selected" :isHovered="isHovered"
            :isSelected="isSelected" />
    </aside>

    <!-- Mobile Bottom Sheet -->
    <BottomSheet v-model:state="treeSheetState" :showFab="false" :showBackdrop="false" :heightRatio="0.75">
        <div class="flex flex-col h-full px-5 pt-4 pb-6 w-full sm:items-center">
            <TreeForm v-if="isCreating" v-model:visible="formVisible" routeResource="trees" action="Create" :markerLatLng="markerLatLng" />
            <TreeCardContent v-else :hovered="props.hovered" :selected="props.selected" :isHovered="isHovered"
                :isSelected="isSelected" />
        </div>
    </BottomSheet>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import TreeCardContent from '@/Components/Map/Partials/TreeCardContent.vue'
import BottomSheet from '@/Components/Map/Partials/BottomSheet.vue'
import TreeForm from '@/Components/Map/Partials/TreeForm.vue'

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
})

const formVisible = ref(false)

watch(formVisible, visible => {
    console.log(visible)
    if(visible === false){
        emit('cancelCreate')
    }
})

const isHovered = computed(() => props.hovered !== null)
const isSelected = computed(() => props.selected !== null)
const isCreating = computed(() => props.markerLatLng !== null)

// single source of truth for visibility
const shouldShowPanel = computed(() => isHovered.value || isSelected.value || isCreating.value)

// local state only for this sheet
const treeSheetState = ref('closed')

// when we enter/exit create mode, toggle the form
watch(
    () => props.markerLatLng,
    (value) => {
        if (value) {
            formVisible.value = true
            treeSheetState.value = 'mid'
        } else {
            formVisible.value = false
            // if nothing else is active, close the sheet
            if (!isHovered.value && !isSelected.value) {
                treeSheetState.value = 'closed'
            }
        }
    }
)

// open sheet when we have hovered/selected/creating, otherwise close
watch(
    shouldShowPanel,
    (open) => {
        treeSheetState.value = open ? 'mid' : 'closed'
    },
    { immediate: true }
)
</script>
