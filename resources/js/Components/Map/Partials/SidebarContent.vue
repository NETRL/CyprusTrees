<template>
    <div
        class="flex flex-col h-full w-full overflow-y-auto">
        <!-- Header -->
        <div class="sticky flex justify-between shrink-0 pb-5 px-5">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl lg:text-2xl font-bold text-gray-900 dark:text-white">
                        Nicosia Trees
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Explore urban forestry data
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Total trees mapped: <span class="font-semibold">{{ totalTrees }}</span>
                </p>
            </div>
            <div v-if="showCloseButton">
                <button type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300 sm:h-9 sm:w-9 sm:rounded-xl"
                    @click="emit('close')" title="Close" aria-label="Close">
                    <X />
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 w-full text-center border-y border-gray-200 dark:border-gray-800">
            <div v-for="tab in tabs" :key="tab.value" :class="tabClass(tab.value)" class="hover:cursor-pointer"
                @click="setSelectedTab(tab.value)">
                {{ tab.label }}
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex flex-col gap-5 min-h-0 flex-1 pt-5 px-5">
            <SidebarFiltersContent v-if="selectedTab == tabs[0].value" :treeData="treeData"
                @toggleCategory="emit('toggleCategory', $event)" />

            <SidebarPlantingsContent v-else />
        </div>

    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { X } from 'lucide-vue-next';
import SidebarFiltersContent from '@/Components/Map/Partials/SidebarFiltersContent.vue';
import SidebarPlantingsContent from '@/Components/Map/Partials/SidebarPlantingsContent.vue';
import { MAP_MODES, useMapUiState } from '@/Lib/Map/useMapUiState';

const props = defineProps({
    treeData: { type: Object, default: () => null },
    showCloseButton: { type: Boolean, default: false, }
})

const emit = defineEmits(['toggleCategory', 'close']);

const { ui } = useMapUiState()

// --- Tab selection --- 
const tabs = [
    { label: 'Filters', value: 'filters' },
    { label: 'Plantings', value: 'plantings' }
]

const selectedTab = ref('filters')
const tabClass = (value) => {
    const active = selectedTab.value === value
    return [
        'py-2.5 text-sm font-semibold select-none',
        'transition-colors',
        active
            ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50/60 dark:bg-emerald-900/10 border-b-2 border-emerald-500'
            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/40 border-b-2 border-transparent'
    ]
}

function setSelectedTab(value) {
    selectedTab.value = value
}

const treeFeatures = computed(() =>
    props.treeData?.features ?? []
);

const totalTrees = computed(() => treeFeatures.value.length);


watch(() => ui.activeMode, (mode) => {
if(mode !== MAP_MODES.PLANTING_SUMMARY) selectedTab.value = tabs[0].value
})
</script>