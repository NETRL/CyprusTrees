<template>
    <div v-if="showEventDetails" class="w-full overflow-y-auto">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="border-b border-gray-200 p-4 dark:border-gray-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold"
                                :class="typeBadgeClass(selectedEvent?.type)">
                                {{ selectedEvent?.typeLabel ?? (selectedEvent?.type === 'planting' ? 'Planting' :
                                    'Maintenance') }}
                            </span>

                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold"
                                :class="statusBadgeClass(selectedEvent?.status)">
                                {{ selectedEvent?.statusLabel ?? selectedEvent?.status }}
                            </span>

                            <span v-if="selectedEvent?.isOverdue"
                                class="inline-flex items-center rounded-md bg-red-100 px-2 py-0.5 text-[11px] font-bold text-red-800 dark:bg-red-500/15 dark:text-red-200">
                                Overdue
                            </span>

                            <span v-if="selectedEvent?.isToday"
                                class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200">
                                Today
                            </span>
                        </div>

                        <div class="mt-2 truncate text-base font-extrabold text-gray-900 dark:text-gray-100">
                            {{ selectedEvent?.title }}
                        </div>

                        <div class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-400">
                            <div v-if="selectedEvent?.whenText">
                                <span class="font-semibold text-gray-700 dark:text-gray-300">When:</span>
                                {{ selectedEvent?.whenText }}
                            </div>

                            <div v-if="selectedEvent?.locationText">
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Location:</span>
                                {{ selectedEvent?.locationText }}
                            </div>

                            <div v-if="selectedEvent?.progressText">
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Progress:</span>
                                {{ selectedEvent?.progressText }}
                            </div>
                        </div>
                    </div>

                    <button
                        class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900/40"
                        @click="clearSelection" title="Clear selection">
                        Clear
                    </button>
                </div>

                <!-- Primary actions for field officers -->
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900/40"
                        @click="mapBus?.emit('event:focus', selectedEvent)">
                        Focus on map
                    </button>

                    <button v-if="selectedEvent?.actions?.includes('start')" type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-900/15 dark:text-amber-100 dark:hover:bg-amber-900/25"
                        @click="mapBus?.emit('event:start', selectedEvent)">
                        ▶ Start
                    </button>

                    <button v-if="selectedEvent?.actions?.includes('complete')" type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-900 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-900/15 dark:text-emerald-100 dark:hover:bg-emerald-900/25"
                        @click="mapBus?.emit('event:complete', selectedEvent)">
                        ✓ Complete
                    </button>
                </div>
            </div>

            <!-- MAINTENANCE preview: tree-centric -->
            <div v-if="selectedEvent?.type === 'maintenance'" class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            Tree
                        </div>

                        <div class="mt-1 truncate text-sm font-extrabold text-gray-900 dark:text-gray-100">
                            {{ treeTitle }}
                        </div>

                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Species:</span>
                            {{ selectedTree?.species?.common_name ?? '—' }}
                            <span class="text-gray-400">•</span>
                            {{ selectedTree?.species?.latin_name ?? '—' }}
                        </div>

                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Area:</span>
                            {{ selectedTree?.neighborhood?.name ?? '—' }}
                            <span v-if="selectedTree?.address" class="text-gray-400">•</span>
                            {{ selectedTree?.address ?? '' }}
                        </div>
                    </div>

                    <div class="shrink-0 flex flex-col items-end gap-2">
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold"
                            :class="treeHealthBadgeClass(selectedTree?.health_status)">
                            {{ selectedTree?.health_status ?? '—' }}
                        </span>

                        <span v-if="selectedTree?.calculated_pollen_risk != null"
                            class="inline-flex items-center rounded-md bg-black/5 px-2 py-0.5 text-[11px] font-bold text-gray-700 dark:bg-white/10 dark:text-gray-200">
                            Pollen {{ selectedTree?.calculated_pollen_risk }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <KpiTiny label="Height" :value="fmt(selectedTree?.height_m, 'm')" />
                    <KpiTiny label="DBH" :value="fmt(selectedTree?.dbh_cm, 'cm')" />
                    <KpiTiny label="Canopy" :value="fmt(selectedTree?.canopy_diameter_m, 'm')" />
                    <KpiTiny label="Last inspected" :value="fmtDate(selectedTree?.last_inspected_at)" />
                </div>

                <div v-if="selectedTree?.tags?.length" class="flex flex-wrap gap-2">
                    <span v-for="t in selectedTree?.tags" :key="t.id"
                        class="rounded-full bg-black/5 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-200">
                        {{ t.name }}
                    </span>
                </div>

                <div v-if="selectedTree?.citizenReports?.length"
                    class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-900/30">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            Latest citizen report
                        </div>
                        <span class="text-[11px] font-semibold text-gray-600 dark:text-gray-400">
                            {{ selectedTree?.citizenReports[0].status }}
                        </span>
                    </div>
                    <div class="mt-2 text-xs text-gray-700 dark:text-gray-200">
                        {{ selectedTree?.citizenReports[0].description_preview ??
                            selectedTree?.citizenReports[0].description }}
                    </div>
                </div>

                <div v-if="selectedTree?.status === 'vacant_pit'"
                    class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 dark:border-amber-900/50 dark:bg-amber-900/15 dark:text-amber-100">
                    Note: This record is marked as <span class="font-bold">Vacant pit</span>. Verify on-site.
                </div>
            </div>

        </div>
    </div>
    <div v-else class="w-full overflow-y-auto">
        <!-- Sticky header -->
        <div
            class="lg:sticky top-0 z-10 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 backdrop-blur-md px-4 py-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span
                            class="text-[11px] font-bold uppercase tracking-widest text-brand-600 dark:text-brand-400">
                            My Events
                        </span>
                    </div>

                    <h3 class="mt-1 truncate text-xl font-extrabold text-gray-900 dark:text-white">
                        Assigned work
                    </h3>

                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                        Tap an event to focus it on the map.
                    </p>
                </div>
                <div class="grid col">
                    <button class="shrink-0 rounded-md p-1 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                        @click="closePanel" title="Close">
                        ✕
                    </button>
                    <Button :icon="isFilters ? 'pi pi-filter' : 'pi pi-filter-slash'" severity="secondary" outlined
                        @click="toggleFilters" badgeClass="p-badge-secondary" />
                </div>

            </div>

            <!-- Search + sort -->
            <div v-if="isFilters" class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                <input v-model="search" type="text" placeholder="Search title, location, status..."
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-100 dark:placeholder:text-gray-600 dark:focus:border-indigo-700 dark:focus:ring-indigo-900/40" />

                <select v-model="sortMode"
                    class="w-full sm:w-56 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-100 dark:focus:border-indigo-700 dark:focus:ring-indigo-900/40">
                    <option v-for="o in sortOptions" :key="o.value" :value="o.value">
                        {{ o.label }}
                    </option>
                </select>
            </div>

            <!-- Tabs (horizontal scroll) -->
            <div ref="tabsEl"
                class="py-3 flex gap-2 overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-700 scrollbar-track-transparent">
                <button v-for="t in tabs" :key="t.key" :ref="(el) => setTabRef(t.key, el)" @click="activeTab = t.key"
                    :class="[
                        'shrink-0 inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-semibold transition',
                        activeTab === t.key
                            ? 'border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-900/50 dark:bg-indigo-900/20 dark:text-indigo-300'
                            : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900/40'
                    ]">
                    <span>{{ t.label }}</span>
                    <span class="rounded-md bg-black/5 px-2 py-0.5 text-[11px] font-bold dark:bg-white/10">
                        {{ counts[t.key] }}
                    </span>
                </button>
            </div>

            <!-- Summary -->
            <div class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                Showing
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ filtered.length }}</span>
                of
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ allEvents.length }}</span>
                events.
            </div>
        </div>

        <!-- Body list -->
        <div class="p-4">
            <div v-if="filtered.length === 0"
                class="rounded-xl border border-dashed border-gray-200 p-6 text-center text-sm text-gray-600 dark:border-gray-800 dark:text-gray-400">
                No events found for this filter.
            </div>

            <div v-else class="space-y-4">
                <!-- Maintenance section -->
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
                    <button type="button"
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-800"
                        @click="toggleSection('maintenance')">
                        <div class="flex items-center gap-2 min-w-0">
                            <span
                                class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-200">
                                Maintenance
                            </span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                {{ filteredMaintenance.length }}
                            </span>
                        </div>

                        <span class="text-gray-400">
                            {{ sectionsOpen.maintenance ? '▾' : '▸' }}
                        </span>
                    </button>

                    <div v-show="sectionsOpen.maintenance" class="p-3 space-y-3">
                        <div v-if="filteredMaintenance.length === 0"
                            class="rounded-xl border border-dashed border-gray-200 p-4 text-center text-sm text-gray-600 dark:border-gray-800 dark:text-gray-400">
                            No maintenance events for this filter.
                        </div>

                        <button v-else v-for="ev in filteredMaintenance" :key="ev.key" type="button" @click="select(ev)"
                            :class="[
                                'w-full text-left rounded-xl border p-3 transition shadow-sm',
                                isSelected(ev)
                                    ? 'border-indigo-200 bg-indigo-50/70 dark:border-indigo-900/60 dark:bg-indigo-900/15'
                                    : 'border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-900/40'
                            ]">
                            <!-- your existing card content -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold"
                                    :class="typeBadgeClass(ev.type)">
                                    {{ ev.typeLabel ?? 'Maintenance' }}
                                </span>

                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold"
                                    :class="statusBadgeClass(ev.status)">
                                    {{ ev.statusLabel ?? ev.status }}
                                </span>

                                <span v-if="ev.isOverdue"
                                    class="inline-flex items-center rounded-md bg-red-100 px-2 py-0.5 text-[11px] font-bold text-red-800 dark:bg-red-500/15 dark:text-red-200">
                                    Overdue
                                </span>

                                <span v-if="ev.isToday"
                                    class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200">
                                    Today
                                </span>

                                <span v-if="isSelected(ev)"
                                    class="ml-auto text-[11px] font-bold text-indigo-700 dark:text-indigo-300">
                                    Selected
                                </span>
                            </div>

                            <div class="mt-2 truncate text-sm font-extrabold text-gray-900 dark:text-gray-100">
                                {{ ev.title }}
                            </div>

                            <div class="mt-1 space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                <div v-if="ev.whenText">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">When:</span>
                                    {{ ev.whenText }}
                                </div>
                                <div v-if="ev.locationText">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Location:</span>
                                    {{ ev.locationText }}
                                </div>
                                <div v-if="ev.progressText">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Progress:</span>
                                    {{ ev.progressText }}
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <button v-if="ev.actions?.includes('start')" type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-900/15 dark:text-amber-100 dark:hover:bg-amber-900/25"
                                    @click.stop="mapBus?.emit('event:start', ev)">
                                    ▶ Start
                                </button>

                                <button v-if="ev.actions?.includes('complete')" type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-900 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-900/15 dark:text-emerald-100 dark:hover:bg-emerald-900/25"
                                    @click.stop="mapBus?.emit('event:complete', ev)">
                                    ✓ Complete
                                </button>

                                <button v-if="ev.detailsUrl" type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-900 hover:bg-indigo-100 dark:border-indigo-900/50 dark:bg-indigo-900/15 dark:text-indigo-100 dark:hover:bg-indigo-900/25"
                                    @click.stop="mapBus?.emit('event:details', ev)">
                                    ↗ Details
                                </button>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Planting section -->
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
                    <button type="button"
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-800"
                        @click="toggleSection('planting')">
                        <div class="flex items-center gap-2 min-w-0">
                            <span
                                class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200">
                                Planting
                            </span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                {{ filteredPlanting.length }}
                            </span>
                        </div>

                        <span class="text-gray-400">
                            {{ sectionsOpen.planting ? '▾' : '▸' }}
                        </span>
                    </button>

                    <div v-show="sectionsOpen.planting" class="p-3 space-y-3">
                        <div v-if="filteredPlanting.length === 0"
                            class="rounded-xl border border-dashed border-gray-200 p-4 text-center text-sm text-gray-600 dark:border-gray-800 dark:text-gray-400">
                            No planting events for this filter.
                        </div>

                        <button v-else v-for="ev in filteredPlanting" :key="ev.key" type="button" @click="select(ev)"
                            :class="[
                                'w-full text-left rounded-xl border p-3 transition shadow-sm',
                                isSelected(ev)
                                    ? 'border-indigo-200 bg-indigo-50/70 dark:border-indigo-900/60 dark:bg-indigo-900/15'
                                    : 'border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-900/40'
                            ]">
                            <!-- same card markup -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold"
                                    :class="typeBadgeClass(ev.type)">
                                    {{ ev.typeLabel ?? 'Planting' }}
                                </span>

                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold"
                                    :class="statusBadgeClass(ev.status)">
                                    {{ ev.statusLabel ?? ev.status }}
                                </span>

                                <span v-if="ev.isOverdue"
                                    class="inline-flex items-center rounded-md bg-red-100 px-2 py-0.5 text-[11px] font-bold text-red-800 dark:bg-red-500/15 dark:text-red-200">
                                    Overdue
                                </span>

                                <span v-if="ev.isToday"
                                    class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200">
                                    Today
                                </span>

                                <span v-if="isSelected(ev)"
                                    class="ml-auto text-[11px] font-bold text-indigo-700 dark:text-indigo-300">
                                    Selected
                                </span>
                            </div>

                            <div class="mt-2 truncate text-sm font-extrabold text-gray-900 dark:text-gray-100">
                                {{ ev.title }}
                            </div>

                            <div class="mt-1 space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                <div v-if="ev.whenText">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">When:</span>
                                    {{ ev.whenText }}
                                </div>
                                <div v-if="ev.locationText">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Location:</span>
                                    {{ ev.locationText }}
                                </div>
                                <div v-if="ev.progressText">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Progress:</span>
                                    {{ ev.progressText }}
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <!-- <button type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-900 hover:bg-blue-100 dark:border-blue-900/50 dark:bg-blue-900/15 dark:text-blue-100 dark:hover:bg-blue-900/25"
                                    @click.stop="mapBus?.emit('open-map', ev)">
                                    <i class="pi pi-map" /> Open Map </button> -->
                                <button v-if="ev.actions?.includes('start')" type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-900/15 dark:text-amber-100 dark:hover:bg-amber-900/25"
                                    @click.stop="mapBus?.emit('event:start', ev)">
                                    ▶ Start
                                </button>

                                <button v-if="ev.actions?.includes('complete')" type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-900 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-900/15 dark:text-emerald-100 dark:hover:bg-emerald-900/25"
                                    @click.stop="mapBus?.emit('event:complete', ev)">
                                    ✓ Complete
                                </button>

                                <!-- <button v-if="ev.detailsUrl" type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-900 hover:bg-indigo-100 dark:border-indigo-900/50 dark:bg-indigo-900/15 dark:text-indigo-100 dark:hover:bg-indigo-900/25"
                                    @click.stop="mapBus?.emit('details', ev)">
                                    ↗ Details
                                </button> -->
                            </div>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { useMapUiState } from "@/Lib/Map/useMapUiState";
import { computed, inject, nextTick, onMounted, reactive, ref, unref, watch } from "vue";
import KpiTiny from "@/Components/Map/Partials/KpiTiny.vue";
import { useStoredPrefs } from "@/Composables/useStoredPrefs";

const props = defineProps({
    selectedTree: { type: Object, default: null },
});

const mapBus = inject('mapBus')

watch(() => props.selectedTree,
    (v) => {
        if (v) {
            if (!selectedEvent.value) return
            showEventDetails.value = true
        }
    })

const showEventDetails = ref(false);

const injectedEvents = inject("userEvents", ref([]));

const isFilters = ref(true)

const { closePanel } = useMapUiState();

const { getPref, setPref } = useStoredPrefs()

const STORAGE_KEY = "eventPanel_type-"

const sectionsOpen = ref({
    maintenance: true,
    planting: true,
})

const tabs = [
    { key: "today", label: "Today" },
    { key: "upcoming", label: "Upcoming" },
    { key: "in_progress", label: "In Progress" },
    { key: "overdue", label: "Overdue" },
    { key: "completed", label: "Completed" },
];

const activeTab = ref("today");
const search = ref("");

const sortOptions = [
    { label: "Start time (asc)", value: "start_asc" },
    { label: "Start time (desc)", value: "start_desc" },
    { label: "Title (A–Z)", value: "title_asc" },
];
const sortMode = ref("start_asc");

const selectedEvent = ref(null);

const allEvents = computed(() => unref(injectedEvents) ?? []);

const counts = computed(() => {
    const base = { today: 0, upcoming: 0, in_progress: 0, overdue: 0, completed: 0 };
    for (const e of allEvents.value) {
        if (e?.tab && base[e.tab] !== undefined) base[e.tab]++;
    }
    return base;
});

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    let list = allEvents.value.filter((e) => e?.tab === activeTab.value);

    if (q) {
        list = list.filter((e) => {
            const hay = [e.title, e.typeLabel, e.statusLabel, e.whenText, e.locationText, e.progressText]
                .filter(Boolean)
                .join(" ")
                .toLowerCase();
            return hay.includes(q);
        });
    }

    const sort = sortMode.value;
    list = [...list].sort((a, b) => {
        if (sort === "title_asc") return (a.title ?? "").localeCompare(b.title ?? "");
        const aT = a.start ? Date.parse(a.start) : 0;
        const bT = b.start ? Date.parse(b.start) : 0;
        return sort === "start_desc" ? bT - aT : aT - bT;
    });

    return list;
});

const toggleFilters = () => {
    isFilters.value = !isFilters.value
}

function select(ev) {
    if (selectedEvent.value == ev) {
        showEventDetails.value = true
    } else {
        selectedEvent.value = ev;
    }
    mapBus?.emit('event:selected', ev) // parent zooms to feature
}

function clearSelection() {
    showEventDetails.value = false
}

function isSelected(ev) {
    return !!selectedEvent.value && selectedEvent.value.key === ev.key;
}


// keep active tab centered 
const tabsEl = ref(null);
const tabRefs = ref(new Map());
function setTabRef(key, el) {
    if (!el) return;
    tabRefs.value.set(key, el);
}
function centerActiveTab() {
    const container = tabsEl.value;
    const btn = tabRefs.value.get(activeTab.value);
    if (!container || !btn) return;

    const cRect = container.getBoundingClientRect();
    const bRect = btn.getBoundingClientRect();

    const current = container.scrollLeft;
    const target =
        current + (bRect.left - cRect.left) - (cRect.width / 2) + (bRect.width / 2);

    container.scrollTo({ left: Math.max(0, target), behavior: "smooth" });
}

watch(activeTab, async () => {
    await nextTick();
    centerActiveTab();
}, { immediate: true });

onMounted(() => centerActiveTab());

const treeTitle = computed(() => {
    const t = props.selectedTree;
    if (!t) return "Tree —";
    const isVacant = t.status === "vacant_pit";
    return isVacant ? `Tree site #${t.id} (Vacant pit)` : `Tree #${t.id}`;
});

function fmt(v, unit) {
    if (v == null || v === "") return "—";
    const n = typeof v === "number" ? v : Number(v);
    if (Number.isNaN(n)) return "—";
    return unit ? `${n} ${unit}` : `${n}`;
}

function fmtDate(iso) {
    if (!iso) return "—";
    try {
        // Keep it compact for field use
        const d = new Date(iso);
        return d.toLocaleDateString(undefined, { year: "numeric", month: "2-digit", day: "2-digit" });
    } catch {
        return "—";
    }
}

function typeBadgeClass(type) {
    return type === "planting"
        ? "bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200"
        : "bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-200";
}

function statusBadgeClass(status) {
    switch (status) {
        case "draft":
            return "bg-stone-100 text-stone-800 dark:bg-white/10 dark:text-stone-200";
        case "scheduled":
            return "bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-200";
        case "in_progress":
            return "bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200";
        case "completed":
            return "bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200";
        case "cancelled":
            return "bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200";
        default:
            return "bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-200";
    }
}

function treeHealthBadgeClass(health) {
    switch (health) {
        case "good":
            return "bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200";
        case "fair":
            return "bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200";
        case "poor":
        case "critical":
            return "bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200";
        default:
            return "bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-200";
    }
}


onMounted(() => {
    getSectionState()
})

const filteredMaintenance = computed(() =>
    filtered.value.filter((e) => e?.type === "maintenance")
);

const filteredPlanting = computed(() =>
    filtered.value.filter((e) => e?.type === "planting")
);

function getSectionState() {
    sectionsOpen.value.maintenance = getPref(`${STORAGE_KEY}maintenance`, true)
    sectionsOpen.value.planting = getPref(`${STORAGE_KEY}planting`, true)
}

function toggleSection(type) {
    sectionsOpen.value[type] = !sectionsOpen.value[type]
    setPref(`${STORAGE_KEY}${type}`, sectionsOpen.value[type])
}

</script>
