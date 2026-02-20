<template>
    <aside :class="[
        'max-lg:hidden absolute left-2 top-2 z-50',
        'max-h-[calc(100vh-7rem)] overflow-y-auto w-[390px]',
        'rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-lg',
        'transition-all duration-300 ease-in-out',
        'flex flex-col',
        isSidebarOpen
            ? 'opacity-100 px-5 py-5 pointer-events-auto'
            : 'opacity-0 pointer-events-none px-0 py-5',

    ]">
        <SidebarContent v-bind="props" @toggleCategory="payload => emit('toggleCategory', payload)"
            :showCloseButton="true" @close="closeSidebar" />
    </aside>

    <!-- Desktop open button (same anchor as sidebar) -->
    <button :class="[
        'max-lg:hidden absolute left-2 top-2 z-40',
        'grid h-9 w-9 place-items-center rounded-md',
        'bg-white/90 hover:bg-gray-100 shadow ring-1 ring-slate-200 backdrop-blur',
        'dark:bg-slate-900/90 dark:ring-slate-700 transition-opacity',
        !isSidebarOpen
            ? 'opacity-100 pointer-events-auto'
            : 'opacity-0 pointer-events-none',
    ]" @click="openSidebar">
        <i :class="[' group-hover:rotate-25 transition-all duration-300 ease-in-out pi pi-map']"></i>
    </button>

    <!-- Mobile Bottom Sheet -->
    <BottomSheet v-model:state="mobileState">
        <div class="flex flex-col h-full px-5 pt-4 pb-6 w-full sm:items-center">
            <SidebarContent v-bind="props" @toggleCategory="payload => emit('toggleCategory', payload)" />
        </div>
    </BottomSheet>
</template>

<script setup>
import { inject, ref, watch } from 'vue'
import SidebarContent from '@/Components/Map/Partials/SidebarContent.vue'
import BottomSheet from '@/Components/Map/Partials/BottomSheet.vue'
import { useMapUiState } from '@/Lib/Map/useMapUiState'

const props = defineProps({
    treeData: { type: Object, default: () => null, },
    neighborhoodData: { type: Object, default: () => null, }
})


const { isSidebarOpen, toggleSidebar, closeSidebar, openSidebar } = useMapUiState()

const emit = defineEmits(['toggleCategory'])

// local state for this BottomSheet instance
const mobileState = ref('closed')

// keep isSidebarOpen and mobileState in sync
watch(
    () => isSidebarOpen.value,
    (val) => {
        mobileState.value = val ? 'mid' : 'closed'
    }
)

watch(
    () => mobileState.value,
    (val) => {
        // if the sheet is not closed, then mobile sidebar is "open"
        if (val !== 'closed') {
            openSidebar()
        } else {
            closeSidebar()
        }
    }
    , { immediate: true })
</script>
