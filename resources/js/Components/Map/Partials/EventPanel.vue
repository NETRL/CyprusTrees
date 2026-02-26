<template>
    <div v-if="showEventDetails" class="w-full overflow-y-auto">
        <EventDetails :selectedEvent="selectedEvent" :selectedTree="selectedTree"
            @clearSelection="handleClearSelection" />
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
                            {{ panelTitle }}
                        </span>
                    </div>

                    <h3 class="mt-1 truncate text-xl font-extrabold text-gray-900 dark:text-white">
                        {{ shouldUseTreeEvents ? (selectedTree?.label ?? `Selected tree #${selectedTree?.id}`) :
                        'Assigned work' }}
                    </h3>

                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                        {{ panelSubtitle }}
                    </p>
                </div>
                <div class="flex flex-col items-end justify-end">
                    <div>
                        <button v-if="shouldUseTreeEvents"
                            class=" mb-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300 hover:underline"
                            @click="exitTreeEvents">
                            ← All events
                        </button>

                        <button v-else class="rounded-md p-1 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                            @click="closePanel" title="Close">
                            ✕
                        </button>
                    </div>

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
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ sourceEvents.length }}</span>
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
import { useStoredPrefs } from "@/Composables/useStoredPrefs";
import EventDetails from "./events/EventDetails.vue";

const props = defineProps({
    selectedTree: { type: Object, default: null },
});

const emit = defineEmits(['clearSelection'])

const mapBus = inject('mapBus')
const injectedEvents = inject("userEvents", ref([]));

const showEventDetails = ref(false);
const showTreeEvents = ref(false);

const treeEvents = ref(null)

const isFilters = ref(true)
const sortMode = ref("start_asc");
const selectedEvent = ref(null);

const sectionsOpen = ref({
    maintenance: true,
    planting: true,
})

let isSelectClicked = false


const activeTab = ref("today");
const search = ref("");

const STORAGE_KEY = "eventPanel_type-"

const tabs = [
    { key: "today", label: "Today" },
    { key: "upcoming", label: "Upcoming" },
    { key: "in_progress", label: "In Progress" },
    { key: "overdue", label: "Overdue" },
    { key: "completed", label: "Completed" },
];

const sortOptions = [
    { label: "Start time (asc)", value: "start_asc" },
    { label: "Start time (desc)", value: "start_desc" },
    { label: "Title (A–Z)", value: "title_asc" },
];

const { closePanel } = useMapUiState();
const { getPref, setPref } = useStoredPrefs()

const allEvents = computed(() => unref(injectedEvents) ?? []);

// Single “source of truth” for what list we are browsing
const shouldUseTreeEvents = computed(() => {
    const hasTree = !!props.selectedTree?.id;
    const hasTreeEventsLoaded = treeEvents.value !== null; // matches your requirement
    return hasTree && showTreeEvents.value && hasTreeEventsLoaded;
});

const sourceEvents = computed(() => {
    return shouldUseTreeEvents.value ? (treeEvents.value ?? []) : allEvents.value
})

const panelTitle = computed(() => (shouldUseTreeEvents.value ? "Tree Events" : "My Events"));
const panelSubtitle = computed(() =>
    shouldUseTreeEvents.value
        ? "Events for the selected tree."
        : "Tap an event to focus it on the map."
);

const counts = computed(() => {
    const base = { today: 0, upcoming: 0, in_progress: 0, overdue: 0, completed: 0 };
    for (const e of sourceEvents.value) {
        if (e?.tab && base[e.tab] !== undefined) base[e.tab]++;
    }
    return base;
});

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    let list = sourceEvents.value.filter((e) => e?.tab === activeTab.value);

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


const filteredMaintenance = computed(() => filtered.value.filter((e) => e?.type === "maintenance"));
const filteredPlanting = computed(() => filtered.value.filter((e) => e?.type === "planting"));


// props.selectedTrees comes from emit(event:select) OR if a tree feature is clicked.
watch(() => props.selectedTree?.id,
    (treeId) => {
        if (!treeId) {
            // tree deselected
            treeEvents.value = null;
            showTreeEvents.value = false;
            // reset to list:
            onClearSelection()
            return
        }
        // If the user intentionally clicked an event, keep details view.
        if (selectedEvent.value && isSelectClicked) {
            showEventDetails.value = true;
            isSelectClicked = false;
            return;
        }

        // Otherwise, entering “tree browsing” mode
        onClearSelection();
        showTreeEvents.value = true;
        findTreeEvents(treeId);
    })

function findTreeEvents(treeId) {
    if (!treeId) {
        treeEvents.value = null;
        return;
    }
    // mark as "loaded" even if empty array
    treeEvents.value = allEvents.value.filter((item) => item?.meta?.tree_id === treeId);
}


const toggleFilters = () => { isFilters.value = !isFilters.value }


function select(ev) {
    isSelectClicked = true
    showEventDetails.value = true
    selectedEvent.value = ev;
    mapBus?.emit('event:selected', ev)
}

const handleClearSelection = () => {
    onClearSelection()
    emit('clearSelection')
}

function onClearSelection() {
    showEventDetails.value = false
    selectedEvent.value = null
}

function isSelected(ev) {
    return !!selectedEvent.value && selectedEvent.value.key === ev.key;
}

function exitTreeEvents() {
    showTreeEvents.value = false;
    treeEvents.value = null;
    emit('clearSelection')
}


onMounted(() => {
    sectionsOpen.value.maintenance = getPref(`${STORAGE_KEY}maintenance`, true)
    sectionsOpen.value.planting = getPref(`${STORAGE_KEY}planting`, true)
})



function toggleSection(type) {
    sectionsOpen.value[type] = !sectionsOpen.value[type]
    setPref(`${STORAGE_KEY}${type}`, sectionsOpen.value[type])
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
