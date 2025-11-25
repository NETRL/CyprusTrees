<template>
    <aside
        :class="[
            'max-lg:hidden absolute left-0 top-0 m-4 rounded-lg bg-white/90 backdrop-blur-xs dark:bg-gray-900/90 border border-gray-200 dark:border-gray-800 shadow-lg transition-all duration-300 ease-in-out z-50',
            'max-h-[calc(100vh-7rem)] overflow-y-auto w-[390px]',
            {
                'opacity-100 px-5 py-5 pointer-events-auto': isExpanded || isHovered,
                'opacity-0 pointer-events-none px-0 py-5': !isExpanded && !isHovered,
            },
        ]"
    >
        <div class="flex flex-col gap-4 select-none">
            <SidebarContent />
            <span class="lg:text-xl font-bold">Explore Trees in Nicosia</span>
        </div>
    </aside>

    <!-- Mobile Bottom Sheet -->
    <BottomSheet
        :height-ratio="0.8"
        fab-icon="pi pi-map"
        initial-state="closed"
        @update:state="(s) => (mobileState = s)"
    >
        <!-- Optional header above handle -->
        <template #header>
            <!-- leave empty or add title, etc. -->
        </template>

        <!-- Main content -->
        <SidebarContent />
    </BottomSheet>
</template>

<script setup>
import { ref } from 'vue'
import { useSidebar } from '@/Composables/useSidebar'
import SidebarContent from '@/Components/Map/Partials/SidebarContent.vue'
import BottomSheet from '@/Components/Layout/BottomSheet.vue'

const { isExpanded, isHovered } = useSidebar()

const mobileState = ref('closed')
</script>
