<template>
    <aside :class="[
        'max-lg:hidden absolute left-0 top-0 m-4 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-lg transition-all duration-300 ease-in-out z-50',
        'max-h-[calc(100vh-7rem)] overflow-hidden w-[390px] flex flex-col',
        {
            'opacity-100 px-5 py-5 pointer-events-auto': isExpanded || isHovered,
            'opacity-0 pointer-events-none px-0 py-5': !isExpanded && !isHovered,
        },
    ]">
        <SidebarContent v-bind="props" />
    </aside>
    
    <!-- Mobile Bottom Sheet -->
    <BottomSheet @update:state="(s) => (mobileState = s)">
        <div class="flex flex-col h-full px-5 pt-4 pb-6 w-full sm:items-center">
            <SidebarContent v-bind="props" />
        </div>
    </BottomSheet>
</template>

<script setup>
import { ref } from 'vue'
import { useSidebar } from '@/Composables/useSidebar'
import SidebarContent from '@/Components/Map/Partials/SidebarContent.vue'
import BottomSheet from '@/Components/Map/Partials/BottomSheet.vue'

const props = defineProps({
    selectedData: {
        type: Object,
        default: null
    }
})

const { isExpanded, isHovered } = useSidebar()
const mobileState = ref('closed')
</script>