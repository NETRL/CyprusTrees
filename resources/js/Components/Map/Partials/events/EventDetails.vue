<template>
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900/85">
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
                    @click="emit('clearSelection')" title="Clear selection">
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
</template>

<script setup>
import KpiTiny from '@/Components/Map/Partials/KpiTiny.vue';
import { computed, inject } from 'vue';

const props = defineProps({
    selectedTree: { type: Object, default: null },
    selectedEvent: { type: Object, default: null },
})

// const selectedEvent = computed(() => props.event)
// const selectedTree = computed(() => props.tree)


const emit = defineEmits(['clearSelection'])

const mapBus = inject('mapBus')

const treeTitle = computed(() => {
    const t = props.selectedTree;
    if (!t) return "Tree —";
    const isVacant = t.status === "vacant_pit";
    return isVacant ? `Tree site #${t.id} (Vacant pit)` : `Tree #${t.id}`;
});


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



</script>