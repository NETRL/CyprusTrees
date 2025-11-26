<template>
    <aside :class="[
        'max-lg:hidden absolute left-0 top-0 m-4 rounded-lg bg-white/90 backdrop-blur-xs dark:bg-gray-900/90 border border-gray-200 dark:border-gray-800 shadow-lg transition-all duration-300 ease-in-out z-50',
        'max-h-[calc(100vh-7rem)] overflow-y-auto w-[390px]',
        {
            'opacity-100 px-5 py-5 pointer-events-auto': isExpanded || isHovered,
            'opacity-0 pointer-events-none px-0 py-5': !isExpanded && !isHovered,
        },
    ]">
        <div class="flex flex-col gap-4 select-none">
            <SidebarContent v-bind="props" />
        </div>
    </aside>

    <!-- Mobile Bottom Sheet -->
    <BottomSheet @update:state="(s) => (mobileState = s)">
        <!-- Main content -->
        <SidebarContent v-bind="props" />
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

console.log(props.selectedData)

const { isExpanded, isHovered } = useSidebar()

const mobileState = ref('closed')
</script>
