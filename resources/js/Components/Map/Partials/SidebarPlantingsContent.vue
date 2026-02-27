<template>
        <div v-if="!selectedEvent" class="flex flex-col gap-6">
            <div
                class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-5 text-center border border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Plantings in {{ year }}</h3>
                <div class="text-5xl font-extrabold text-emerald-600 dark:text-emerald-400 mb-2">
                    {{ totalPlanted.toLocaleString() }}
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Trees planted this year</p>

                <button @click="showStatsModal = true"
                    class="px-5 py-2 bg-slate-800 hover:bg-slate-700 dark:bg-slate-200 dark:hover:bg-white dark:text-slate-900 text-white text-sm font-medium rounded-lg transition-colors w-full">
                    View detailed stats
                </button>
            </div>

            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                    <TreePine class="w-5 h-5 text-emerald-600" />
                    Latest Planting Events
                </h3>
                <div class="space-y-3">
                    <div v-for="event in plantingEvents" :key="event.id" @click="onEventClicked(event)"
                        class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-lg p-4 shadow-sm hover:border-emerald-500 transition-all cursor-pointer relative group">
                        <div
                            class="absolute top-3 max-sm:bottom-3 max-sm:inset-y-0 right-3 flex items-center max-sm:items-end gap-1 text-[10px] font-bold text-gray-400 group-hover:text-emerald-500">
                            <Clock class="w-3 h-3" /> {{ formatDate(event.completed_at) }}
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="bg-emerald-50 dark:bg-emerald-900/30 p-2 rounded-full">
                                <TreePine class="w-5 h-5 text-emerald-600" />
                            </div>
                            <div>
                                <h4
                                    class="text-sm font-bold text-gray-900 dark:text-white uppercase truncate max-w-[180px]">
                                    {{ event.campaign?.name }}
                                </h4>
                                <p class="text-xs text-emerald-600 font-semibold">{{ event.event_trees?.length }} Trees
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col h-full animate-in slide-in-from-right duration-300">
            <button @click="removePlanting"
                class="flex items-center gap-2 text-sm text-emerald-600 font-semibold mb-4 hover:underline">
                <ChevronLeft class="w-4 h-4" /> Back to list
            </button>

            <div class="space-y-6">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ selectedEvent.campaign?.name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ selectedEvent.campaign?.sponsor }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                        <span class="text-[10px] uppercase text-gray-400 font-bold block">Status</span>
                        <span class="text-sm font-semibold text-emerald-600 capitalize">{{ selectedEvent.status
                            }}</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                        <span class="text-[10px] uppercase text-gray-400 font-bold block">Date</span>
                        <span class="text-sm font-semibold dark:text-white">{{ formatDate(selectedEvent.completed_at)
                            }}</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase text-gray-400 mb-3">Trees in this event</h4>
                    <div class="space-y-2">
                        <div v-for="tree in selectedEvent.event_trees" :key="tree.id"
                            class="flex justify-between items-center p-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded text-sm hover:cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                            @click="onTreeClicked(tree.tree_id)">
                            <span class="dark:text-gray-300">{{ tree.species_label }}</span>
                            <span class="text-xs italic text-gray-500">ID: #{{ tree.tree_id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <StatsModal v-if="showStatsModal" @close="showStatsModal = false" :events="plantingEvents" />
</template>

<script setup>
import { ref, computed, inject, watch } from 'vue';
import { TreePine, Clock, ChevronLeft } from 'lucide-vue-next';
import StatsModal from '@/Components/Map/Partials/StatsModal.vue';
import { MAP_MODES, MAP_PANELS, useMapUiState } from '@/Lib/Map/useMapUiState';

const mapBus = inject('mapBus')
const plantingEvents = inject('plantingEvents', []);

const { ui, closePanel } = useMapUiState()
const year = new Date().getFullYear()

const selectedEvent = ref(null);
const showStatsModal = ref(false);

const totalPlanted = computed(() => {
    return plantingEvents.reduce((sum, e) => sum + (e.event_trees?.length || 0), 0);
});

const onEventClicked = (ev) => {
    selectedEvent.value = ev
    const eventTreeIds = new Set(ev.event_trees.map(t => Number(t.tree_id)))
    mapBus?.emit('planting_summary:selected', { treeIds: eventTreeIds, coords: [ev.lon, ev.lat] })
}

watch(() => ui.activeMode, (mode) => {
    if (mode !== MAP_MODES.PLANTING_SUMMARY) removePlanting()
    if (mode === MAP_MODES.PLANTING_SUMMARY && ui.activePanel === MAP_PANELS.EVENTS) closePanel()
})

function onTreeClicked(treeId) {
    mapBus?.emit('planting_summary:tree-clicked', treeId)
}

function removePlanting() {
    mapBus?.emit('planting_summary:removed')
    selectedEvent.value = null
}

const formatDate = (d) => new Date(d).toLocaleDateString('en-GB');
</script>