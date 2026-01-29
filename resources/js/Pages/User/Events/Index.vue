<template>
  <div class="p-4">
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">My Events</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Assigned planting and maintenance work.
        </p>
      </div>

      <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <InputText
          v-model="search"
          placeholder="Search by title, neighborhood, campaign..."
          class="w-full sm:w-80"
        />
        <Dropdown
          v-model="sortMode"
          :options="sortOptions"
          optionLabel="label"
          optionValue="value"
          class="w-full sm:w-52"
        />
      </div>
    </div>

    <!-- Tabs -->
    <div class="mb-3 flex flex-wrap gap-2">
      <Button
        v-for="t in tabs"
        :key="t.key"
        size="small"
        :severity="activeTab === t.key ? 'primary' : 'secondary'"
        class="!px-3"
        @click="activeTab = t.key"
      >
        <span class="mr-2">{{ t.label }}</span>
        <span
          class="rounded-md bg-black/5 px-2 py-0.5 text-xs dark:bg-white/10"
        >{{ counts[t.key] }}</span>
      </Button>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
      <div class="p-3 text-sm text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
        Showing <span class="font-medium text-gray-900 dark:text-gray-100">{{ filtered.length }}</span>
        of <span class="font-medium text-gray-900 dark:text-gray-100">{{ events.length }}</span> events.
      </div>

      <div v-if="filtered.length === 0" class="p-6 text-center text-gray-600 dark:text-gray-400">
        No events found for this filter.
      </div>

      <div v-else class="divide-y divide-gray-200 dark:divide-gray-800">
        <div
          v-for="ev in filtered"
          :key="ev.key"
          class="p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
        >
          <!-- Left block -->
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium"
                :class="typeBadgeClass(ev.type)"
              >
                {{ ev.typeLabel }}
              </span>

              <span
                class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium"
                :class="statusBadgeClass(ev.status)"
              >
                {{ ev.statusLabel }}
              </span>

              <span v-if="ev.isOverdue" class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200">
                Overdue
              </span>

              <span v-if="ev.isToday" class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200">
                Today
              </span>
            </div>

            <div class="mt-1 flex items-start gap-2">
              <div class="min-w-0">
                <div class="truncate text-base font-semibold text-gray-900 dark:text-gray-100">
                  {{ ev.title }}
                </div>

                <div class="mt-1 flex flex-col gap-1 text-sm text-gray-600 dark:text-gray-400">
                  <div v-if="ev.whenText">
                    <span class="font-medium text-gray-700 dark:text-gray-300">When:</span>
                    {{ ev.whenText }}
                  </div>

                  <div v-if="ev.locationText">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Location:</span>
                    {{ ev.locationText }}
                  </div>

                  <div v-if="ev.progressText">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Progress:</span>
                    {{ ev.progressText }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right actions -->
          <div class="flex flex-wrap gap-2 md:justify-end">
            <Button
              severity="primary"
              size="small"
              icon="pi pi-map"
              label="Open Map"
              @click="openMap(ev)"
            />

            <Button
              v-if="ev.actions?.includes('start')"
              severity="secondary"
              size="small"
              icon="pi pi-play"
              label="Start"
              @click="startEvent(ev)"
            />

            <Button
              v-if="ev.actions?.includes('complete')"
              severity="success"
              size="small"
              icon="pi pi-check"
              label="Complete"
              @click="completeEvent(ev)"
            />

            <Button
              v-if="ev.detailsUrl"
              severity="help"
              size="small"
              icon="pi pi-external-link"
              label="Details"
              @click="router.visit(ev.detailsUrl)"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputText from "primevue/inputtext";
import Dropdown from "primevue/dropdown";
import Button from "primevue/button";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
  events: { type: Array, default: () => [] },
});

const events = computed(() => props.events ?? []);

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
  for (const e of events.value) {
    if (e?.tab && base[e.tab] !== undefined) base[e.tab]++;
  }
  return base;
});

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase();

  let list = events.value.filter((e) => e?.tab === activeTab.value);

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

  // Sort
  const sort = sortMode.value;
  list = [...list].sort((a, b) => {
    if (sort === "title_asc") return (a.title ?? "").localeCompare(b.title ?? "");
    const aT = a.start ? Date.parse(a.start) : 0;
    const bT = b.start ? Date.parse(b.start) : 0;
    return sort === "start_desc" ? (bT - aT) : (aT - bT);
  });

  return list;
});

function openMap(ev) {
  if (ev.mapUrl) {
    router.visit(ev.mapUrl);
    return;
  }

  // fallback: build map route from lat/lon if available
  if (ev.lat != null && ev.lon != null) {
    router.visit(route("/", { lat: ev.lat, lon: ev.lon }));
    return;
  }

  // last fallback: just go home
  router.visit(route("/"));
}

function startEvent(ev) {
  // Placeholder: wire to your endpoints later (e.g. POST /planting-events/{id}/start)
  // Keep it as visit() for now if you already have routes:
  if (ev.startUrl) router.post(ev.startUrl, {}, { preserveScroll: true });
}

function completeEvent(ev) {
  if (ev.completeUrl) router.post(ev.completeUrl, {}, { preserveScroll: true });
}

// Styling helpers
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
