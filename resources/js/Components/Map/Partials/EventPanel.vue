<template>
    <div class="w-full overflow-y-auto">
        <!-- Sticky header -->
        <div
            class="sticky top-0 z-10 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80 backdrop-blur-md px-4 py-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2"> <span
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

                <button class="shrink-0 rounded-md p-1 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    @click="closePanel" title="Close">
                    ✕
                </button>
            </div>

            <!-- Search + sort -->
            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                <input v-model="search" type="text" placeholder="Search title, location, status..."
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-100 dark:placeholder:text-gray-600 dark:focus:border-indigo-700 dark:focus:ring-indigo-900/40" />

                <select v-model="sortMode"
                    class="w-full sm:w-56 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-200 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-100 dark:focus:border-indigo-700 dark:focus:ring-indigo-900/40">
                    <option v-for="o in sortOptions" :key="o.value" :value="o.value">
                        {{ o.label }}
                    </option>
                </select>
            </div>

            <!-- Tabs -->
            <div class="mt-3 flex flex-wrap gap-2">
                <button v-for="t in tabs" :key="t.key" @click="activeTab = t.key" :class="[
                    'inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-semibold transition',
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

        <!-- Body -->
        <div class="p-4">
            <div v-if="filtered.length === 0"
                class="rounded-xl border border-dashed border-gray-200 p-6 text-center text-sm text-gray-600 dark:border-gray-800 dark:text-gray-400">
                No events found for this filter.
            </div>

            <div v-else class="space-y-3">
                <button v-for="ev in filtered" :key="ev.key" type="button" @click="select(ev)" :class="[
                    'w-full text-left rounded-xl border p-3 transition shadow-sm',
                    isSelected(ev)
                        ? 'border-indigo-200 bg-indigo-50/70 dark:border-indigo-900/60 dark:bg-indigo-900/15'
                        : 'border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-900/40'
                ]">
                    <!-- top row badges -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold"
                            :class="typeBadgeClass(ev.type)">
                            {{ ev.typeLabel ?? (ev.type === 'planting' ? 'Planting' : 'Maintenance') }}
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

                    <!-- title -->
                    <div class="mt-2 truncate text-sm font-extrabold text-gray-900 dark:text-gray-100">
                        {{ ev.title }}
                    </div>

                    <!-- meta -->
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

                    <!-- row actions -->
                    <div class="mt-3 flex flex-wrap gap-2">
                        <!-- <button type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900/40"
                            @click.stop="emit('openMap', ev)">
                            <span class="text-sm">🗺️</span>
                            Show on Map
                        </button> -->

                        <button v-if="ev.actions?.includes('start')" type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-900/15 dark:text-amber-100 dark:hover:bg-amber-900/25"
                            @click.stop="emit('start', ev)">
                            ▶ Start
                        </button>

                        <button v-if="ev.actions?.includes('complete')" type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-900 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-900/15 dark:text-emerald-100 dark:hover:bg-emerald-900/25"
                            @click.stop="emit('complete', ev)">
                            ✓ Complete
                        </button>

                        <button v-if="ev.detailsUrl" type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-900 hover:bg-indigo-100 dark:border-indigo-900/50 dark:bg-indigo-900/15 dark:text-indigo-100 dark:hover:bg-indigo-900/25"
                            @click.stop="emit('details', ev)">
                            ↗ Details
                        </button>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useMapUiState } from "@/Lib/Map/useMapUiState";
import { computed, inject, ref, watch } from "vue";

const props = defineProps({
    // parent can keep selected in UI state (recommended)
    selectedKey: { type: String, default: null },
    selectedTree: { type: Object, default: null},
});

watch(()=>props.selectedTree, (v) => {console.log(v)})

const events = inject('userEvents')

const emit = defineEmits(["select", "openMap", "start", "complete", "details"]);

const allEvents = computed(() => events ?? []);

const { closePanel } = useMapUiState()


const mapBus = inject('mapBus')

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
            const hay = [
                e.title,
                e.typeLabel,
                e.statusLabel,
                e.whenText,
                e.locationText,
                e.progressText,
            ]
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

function select(ev) {
    mapBus?.emit('event:selected', ev)
}

function isSelected(ev) {
    return !!props.selectedKey && props.selectedKey === ev.key;
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
</script>
