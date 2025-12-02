<template>
    <!-- Desktop Card -->
    <aside :class="[
        'max-lg:hidden absolute right-0 top-0 m-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-2xl transition-all duration-300 ease-in-out z-50',
        'max-h-[calc(100vh-7rem)] overflow-hidden w-[420px] flex flex-col',
        {
            'opacity-100 translate-x-0 pointer-events-auto': isHovered || isSelected,
            'opacity-0 translate-x-4 pointer-events-none': !isHovered && !isSelected,
        },
    ]">
        <TreeCardContent :hovered="props.hovered" :selected="props.selected" :isHovered="isHovered"
            :isSelected="isSelected" />
    </aside>

    <!-- Mobile Bottom Sheet -->
    <BottomSheet v-model:state="treeSheetState" :showFab="false" :showBackdrop="false" :heightRatio="0.75">
        <div class="flex flex-col h-full px-5 pt-4 pb-6 w-full sm:items-center">
            <TreeCardContent :hovered="props.hovered" :selected="props.selected" :isHovered="isHovered"
                :isSelected="isSelected" />
        </div>
    </BottomSheet>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import TreeCardContent from '@/Components/Map/Partials/TreeCardContent.vue'
import BottomSheet from '@/Components/Map/Partials/BottomSheet.vue'

const props = defineProps({
    hovered: {
        type: Object,
        default: null,
    },
    selected: {
        type: Object,
        default: null,
    },
})

const isHovered = computed(() => props.hovered !== null)
const isSelected = computed(() => props.selected !== null)

// local state only for this sheet
const treeSheetState = ref('closed')

// open sheet when we have hovered or selected tree, otherwise close
watch(
    [isHovered, isSelected],
    ([hovered, selected]) => {
        treeSheetState.value = hovered || selected ? 'mid' : 'closed'
    },
    { immediate: true }
)
</script>
