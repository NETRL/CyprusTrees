<template>

  <Head title="Dashboard" />

  <div class="mx-auto max-w-7xl space-y-10 p-4 pb-20">

    <div>
      <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
        Urban Forest Monitor
      </h1>
      <p class="mt-2 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
        High-level overview of the current forest inventory and recent maintenance operations.
      </p>
    </div>

    <section>
      <div class="mb-5 border-l-4 border-blue-500 pl-4">
        <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
          Inventory Snapshot
        </h2>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">
          Current State
        </p>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

        <BaseChart :options="snapshotDonutOptions" />
        <BaseChart :options="snapshotHistogramOptions" />
        <BaseChart :options="snapshotStackedBarOptions" />

        <BaseChart :options="co2ChartOptions">
          <template #info>
            <ImpactFootnote :data="props.snapshot?.co2" />
          </template>
        </BaseChart>

        <BaseChart :options="co2ByNeighborhoodOptions">
          <template #info>
            <ImpactFootnote :data="props.snapshot?.co2" />
          </template>
        </BaseChart>

        <BaseChart :options="co2ByDbhBinsOptions">
          <template #info>
            <ImpactFootnote :data="props.snapshot?.co2" />
          </template>
        </BaseChart>
      </div>
    </section>

    <div class="border-t border-slate-200 dark:border-slate-800"></div>

    <section>
      <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div class="border-l-4 border-emerald-500 pl-4">
          <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
            Activity & Maintenance
          </h2>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">
            Performance & Operations
          </p>
        </div>

        <div
          class="flex items-center gap-3 bg-white p-1 rounded-full shadow-sm ring-1 ring-slate-200 dark:bg-gray-800 dark:ring-gray-700">
          <span class="pl-3 text-xs font-bold text-slate-400 uppercase">Range</span>
          <div class="relative">
            <select v-model.number="months" :disabled="isLoading"
              class="h-8 cursor-pointer appearance-none rounded-full border-0 bg-slate-50 py-0 pl-3 pr-8 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50 dark:bg-slate-700 dark:text-slate-200">
              <option v-for="opt in monthOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
            <div class="pointer-events-none absolute right-2 top-0 flex h-full items-center text-slate-500">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <KpiCard label="Maintenance Events" :value="activity.maintenance_summary.events" />
        <KpiCard label="Trees Maintained" :value="activity.maintenance_summary.trees" />
        <KpiCard label="Total Cost" :value="`€${activity.maintenance_summary.total_cost}`" />
        <KpiCard label="Avg Cost / Event" :value="`€${activity.maintenance_summary.avg_cost_event}`" />
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
          <BaseChart :options="activityLineOptions" height="400px">
            <template #control>
              <div class="flex max-sm:flex-col gap-1 text-xs font-medium">
                <div class="flex items-center gap-2 rounded-full bg-slate-100 px-2 py-1 dark:bg-slate-700">
                  <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                  <span class="text-slate-600 dark:text-slate-300">New: {{ reportsNewTotal }}</span>
                </div>
                <div class="flex items-center gap-2 rounded-full bg-slate-100 px-2 py-1 dark:bg-slate-700">
                  <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                  <span class="text-slate-600 dark:text-slate-300">Resolved: {{ reportsResolvedTotal }}</span>
                </div>
              </div>
            </template>
          </BaseChart>
        </div>

        <div class="hidden sm:block xl:col-span-1">
          <BaseChart />
        </div>
      </div>
    </section>

  </div>
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
import ImpactFootnote from '@/Components/Charts/ImpactFootnote.vue'

defineOptions({ layout: AuthenticatedLayout })

/* 
   PROPS
 */
const props = defineProps({
  snapshot: Object,
  activity: Object,
  filters: Object,
})

/* 
   THEME
 */
const isDarkMode = ref(false)

function syncDarkMode() {
  isDarkMode.value = document.documentElement.classList.contains('dark')
}

onMounted(() => {
  syncDarkMode()
  const observer = new MutationObserver(syncDarkMode)
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
})

/* 
   SNAPSHOT CHARTS
 */
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





/* 
   ACTIVITY CHARTS
 */
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

/* 
   DATE FILTER
 */
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
