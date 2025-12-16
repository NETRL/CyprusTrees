<template>

  <Head title="Dashboard" />

  <!-- ===================== -->
  <!-- SNAPSHOT SECTION -->
  <!-- ===================== -->
  <section class="p-2">
    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
      Inventory snapshot
    </h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">
      Current state of the urban forest.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-auto-fit gap-5">
      <BaseChart :options="snapshotDonutOptions" />
      <BaseChart :options="snapshotHistogramOptions" />
      <BaseChart :options="snapshotStackedBarOptions" />
      <BaseChart :options="co2ChartOptions">
        <template #info>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
            CO₂ values are estimates derived from DBH and planting year. Coverage:
            {{ props.snapshot?.co2?.coverage_pct ?? 0 }}% ({{ props.snapshot?.co2?.eligible ?? 0 }}/{{
              props.snapshot?.co2?.total ?? 0 }} trees).
            <a :href="route('dashboard.methodology')" class="underline">Methodology</a>
          </p>
        </template>
      </BaseChart>
      <BaseChart :options="co2ByNeighborhoodOptions">
        <template #info>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
            CO₂ values are estimates derived from DBH and planting year. Coverage:
            {{ props.snapshot?.co2?.coverage_pct ?? 0 }}% ({{ props.snapshot?.co2?.eligible ?? 0 }}/{{
              props.snapshot?.co2?.total ?? 0 }} trees).
            <a :href="route('dashboard.methodology')" class="underline">Methodology</a>
          </p>
        </template>
      </BaseChart>

      <BaseChart :options="co2ByDbhBinsOptions">
        <template #info>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
            CO₂ values are estimates derived from DBH and planting year. Coverage:
            {{ props.snapshot?.co2?.coverage_pct ?? 0 }}% ({{ props.snapshot?.co2?.eligible ?? 0 }}/{{
              props.snapshot?.co2?.total ?? 0 }} trees).
            <a :href="route('dashboard.methodology')" class="underline">Methodology</a>
          </p>
        </template>
      </BaseChart>
    </div>
  </section>

  <!-- Divider -->
  <div class="my-6 border-t border-slate-200 dark:border-slate-700"></div>

  <!-- ===================== -->
  <!-- ACTIVITY SECTION -->
  <!-- ===================== -->
  <section class="p-2">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
          Activity within selected period
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          From {{ filters.startDate }} to {{ filters.endDate }}
        </p>
      </div>

      <!-- Date Range Control -->
      <div class="flex items-center gap-2">
        <span class="text-sm text-slate-600 dark:text-slate-300">Range</span>

        <select v-model.number="months" class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm
                 text-slate-900 shadow-sm outline-none
                 focus:ring-2 focus:ring-slate-300
                 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-slate-600">
          <option v-for="opt in monthOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>

        <span v-if="isLoading" class="text-xs text-slate-500 dark:text-slate-400">
          Updating…
        </span>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-auto-fit gap-5">
      <BaseChart :options="activityLineOptions">
        <template #control>
          <div class="flex items-center gap-2 text-xs">
            <span class="rounded-full border border-slate-200 bg-white px-2 py-1 text-slate-700
                 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
              New: <span class="font-semibold">{{ reportsNewTotal.toLocaleString() }}</span>
            </span>

            <span class="rounded-full border border-slate-200 bg-white px-2 py-1 text-slate-700
                 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
              Resolved: <span class="font-semibold">{{ reportsResolvedTotal.toLocaleString() }}</span>
            </span>
          </div>
        </template>
      </BaseChart>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
        <KpiCard label="Maintenance events" :value="activity.maintenance_summary.events" />
        <KpiCard label="Trees maintained" :value="activity.maintenance_summary.trees" />
        <KpiCard label="Total cost" :value="`€${activity.maintenance_summary.total_cost}`" />
        <KpiCard label="Avg cost / event" :value="`€${activity.maintenance_summary.avg_cost_event}`" />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import BaseChart from '@/Components/Charts/BaseChart.vue'
import {
  useDonutChartOptions,
  useLineChartOptions,
  useHistogramOptions,
  useStackedBarChartOptions
} from '@/Composables/Charts/useGenericCharts'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import KpiCard from '@/Components/Charts/KpiCard.vue'

defineOptions({ layout: AuthenticatedLayout })

/* ======================
   PROPS
====================== */
const props = defineProps({
  snapshot: Object,
  activity: Object,
  filters: Object,
})

/* ======================
   THEME
====================== */
const isDarkMode = ref(false)

function syncDarkMode() {
  isDarkMode.value = document.documentElement.classList.contains('dark')
}

onMounted(() => {
  syncDarkMode()
  const observer = new MutationObserver(syncDarkMode)
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
})

/* ======================
   SNAPSHOT CHARTS
====================== */
const snapshotOwnerTotal = computed(() =>
  (props.snapshot?.owner_distribution ?? [])
    .reduce((sum, r) => sum + Number(r.value || 0), 0)
)

const snapshotDonutOptions = computed(() =>
  useDonutChartOptions(
    props.snapshot?.owner_distribution ?? [],
    'Tree Owner Distribution',
    isDarkMode.value,
    {
      centerLabel: {
        value: snapshotOwnerTotal.value,
        label: 'Total trees'
      }
    }
  )
)

const snapshotHistogramOptions = computed(() =>
  useHistogramOptions(
    props.snapshot?.dbh_distribution ?? { xAxisData: [], seriesData: [] },
    'DBH (cm) Distribution',
    'Number of Trees',
    'Trees',
    isDarkMode.value
  )
)

const snapshotStackedBarOptions = computed(() =>
  useStackedBarChartOptions(
    props.snapshot?.health_by_neighborhood ?? { xAxisData: [], seriesData: [] },
    'Health Status by Neighborhood',
    'Trees',
    isDarkMode.value
  )
)

const co2Yearly = computed(() =>
  props.activity?.co2_yearly ?? { xAxisData: [], seriesData: [] }
)

const co2ChartOptions = computed(() =>
  useLineChartOptions(
    co2Yearly.value,
    'Estimated annual CO₂ sequestration (t/year)',
    't CO₂ / year',
    isDarkMode.value,
    false // no zoom needed
  )
)

const co2ByNeighborhoodOptions = computed(() =>
  useHistogramOptions(
    props.snapshot?.co2_by_neighborhood ?? { xAxisData: [], seriesData: [] },
    'Estimated CO₂ by neighborhood (t/year)',
    't CO₂ / year',
    'CO₂',
    isDarkMode.value
  )
)

const co2ByDbhBinsOptions = computed(() =>
  useHistogramOptions(
    props.snapshot?.co2_by_dbh_bins ?? { xAxisData: [], seriesData: [] },
    'Estimated CO₂ by DBH class (t/year)',
    't CO₂ / year',
    'CO₂',
    isDarkMode.value
  )
)



/* ======================
   ACTIVITY CHARTS
====================== */
const reportsTimeline = computed(() =>
  props.activity?.reports_timeline ?? { xAxisData: [], seriesData: [] }
)

const reportsNewTotal = computed(() => {
  const s = reportsTimeline.value.seriesData?.find(x => x.name === 'New Reports')
  return (s?.data ?? []).reduce((sum, v) => sum + Number(v || 0), 0)
})

const reportsResolvedTotal = computed(() => {
  const s = reportsTimeline.value.seriesData?.find(x => x.name === 'Resolved')
  return (s?.data ?? []).reduce((sum, v) => sum + Number(v || 0), 0)
})

const activityLineOptions = computed(() =>
  useLineChartOptions(
    props.activity?.reports_timeline ?? { xAxisData: [], seriesData: [] },
    'Citizen Reports Timeline',
    'Reports',
    isDarkMode.value
  )
)

const co2YearlyOptions = computed(() =>
  useLineChartOptions(
    props.activity?.co2_yearly ?? { xAxisData: [], seriesData: [] },
    'Estimated annual CO₂ sequestration (t/year)',
    't CO₂ / year',
    isDarkMode.value,
    false
  )
)

/* ======================
   DATE FILTER
====================== */
const monthOptions = [
  { value: 3, label: 'Last 3 months' },
  { value: 6, label: 'Last 6 months' },
  { value: 12, label: 'Last 12 months' },
  { value: 24, label: 'Last 24 months' },
  { value: 36, label: 'Last 36 months' },
  { value: 0, label: 'All time' },
]

const months = ref(props.filters?.months ?? 12)
const isLoading = ref(false)

watch(
  () => props.filters?.months,
  (m) => { if (m !== months.value) months.value = m }
)

watch(months, () => {
  isLoading.value = true
  router.get(
    route('dashboard'),
    { months: months.value },
    {
      only: ['activity', 'filters'],
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => (isLoading.value = false),
    }
  )
})
</script>
