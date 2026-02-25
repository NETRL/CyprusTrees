<template>
    <div :class="[
        'absolute top-2 left-3 lg:left-12 z-40! transition-[right] duration-300 ease-in-out',
        // base right spacing when panel is closed
        (isDesktop && isPanelOpen) ? 'right-[calc(420px+0.5rem+0.75rem)]' : 'right-12',
    ]">
        <div v-if="ui.activeMode === MAP_MODES.EVENTS" class="rounded-md border border-gray-200 bg-white/90 backdrop-blur px-4 py-3 shadow
              dark:border-gray-800 dark:bg-slate-900/85">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            My Events
                        </span>

                        <!-- <span v-if="activeEvent?.status" class="rounded-md px-2 py-0.5 text-xs font-medium"
                            :class="statusPill(activeEvent.status)">
                            {{ activeEvent.status }}
                        </span> -->

                        <!-- <span v-if="eventLoading" class="text-xs text-gray-500 dark:text-gray-400">
                            Loading…
                        </span>

                        <span v-if="eventError" class="text-xs text-red-600 dark:text-red-300">
                            {{ eventError }}
                        </span> -->
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Exit event mode -->
                    <button type="button" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium
                 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-white/5" @click="exitEventMode">
                        Exit
                    </button>
                </div>
            </div>
        </div>
        <div v-if="ui.activeMode === MAP_MODES.PLANTING" class="rounded-md border border-gray-200 bg-white/90 backdrop-blur px-4 py-3 shadow
              dark:border-gray-800 dark:bg-slate-900/85">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Planting Event #{{ props.initialEventId }}
                        </span>

                        <span v-if="activeEvent?.status" class="rounded-md px-2 py-0.5 text-xs font-medium"
                            :class="statusPill(activeEvent.status)">
                            {{ activeEvent.status }}
                        </span>

                        <span v-if="eventLoading" class="text-xs text-gray-500 dark:text-gray-400">
                            Loading…
                        </span>

                        <span v-if="eventError" class="text-xs text-red-600 dark:text-red-300">
                            {{ eventError }}
                        </span>
                    </div>

                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                        <span v-if="activeEvent">
                            Trees: {{ activeEvent.event_trees_count ?? 0 }}
                            <template v-if="activeEvent.target_tree_count">/ {{ activeEvent.target_tree_count
                                }}</template>
                        </span>
                        <span v-if="activeEvent?.neighborhood?.name"> • {{ activeEvent.neighborhood.name }}</span>
                        <span v-if="activeEvent?.campaign?.name"> • {{ activeEvent.campaign.name }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Exit event mode -->
                    <button type="button" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium
                 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-white/5" @click="exitEventMode">
                        Exit
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useDevice } from '@/Composables/useDevice'
import { MAP_MODES, useMapUiState } from '@/Lib/Map/useMapUiState'
import { router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const props = defineProps({
    initialEventId: { type: Number, default: null },
    activeEvent: { type: Object, default: null },
    eventLoading: { type: Boolean, default: false },
    eventError: { type: String, default: null },
})

const { ui, setActiveMode, isPanelOpen, closePanel } = useMapUiState()

const { isDesktop } = useDevice()

function statusPill(status) {
    switch (status) {
        case 'draft': return 'bg-stone-100 text-stone-800 dark:bg-white/10 dark:text-stone-200'
        case 'scheduled': return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-200'
        case 'in_progress': return 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200'
        case 'completed': return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200'
        case 'cancelled': return 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200'
        default: return 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-200'
    }
}

function exitEventMode() {
    // just navigate to the map's route.
    router.visit(route('/'), { preserveState: true, preserveScroll: true })
    setActiveMode(MAP_MODES.NONE)
    closePanel()
}


</script>