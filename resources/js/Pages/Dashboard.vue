<template>
  <Head title="Dashboard" />

  <!-- Toolbar -->
  <div class="flex items-center justify-between gap-3 p-2">
    <div class="flex items-center gap-2">
      <span class="text-sm text-slate-600 dark:text-slate-300">Range</span>

      <select
        v-model.number="months"
        class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm
               text-slate-900 shadow-sm outline-none
               focus:ring-2 focus:ring-slate-300
               dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-slate-600"
      >
        <option v-for="opt in monthOptions" :key="opt.value" :value="opt.value">
          {{ opt.label }}
        </option>
      </select>
    </div>

    <div v-if="isLoading" class="text-xs text-slate-500 dark:text-slate-400">
      Updating…
    </div>
  </div>

  <!-- Charts -->
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-auto-fit gap-5 sm:gap-2.5 p-2">
    <BaseChart :options="donutChartOptions" />
    <BaseChart :options="lineChartOptions" />
    <BaseChart :options="histogramOptions" />
    <BaseChart :options="stackedBarOptions" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import BaseChart from '@/Charts/Components/BaseChart.vue'
import {
  useDonutChartOptions,
  useLineChartOptions,
  useHistogramOptions,
  useStackedBarChartOptions
} from '@/Composables/Charts/useGenericCharts'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
  kpis: {
    type: Object,
    default: () => ({})
  },
  filters: {
    type: Object,
    default: () => ({ months: 12, topN: 10 })
  }
})

/** ----------------------
 * Filters (UI state)
 * --------------------- */
const monthOptions = [
  { value: 3, label: 'Last 3 months' },
  { value: 6, label: 'Last 6 months' },
  { value: 12, label: 'Last 12 months' },
  { value: 24, label: 'Last 24 months' },
  { value: 36, label: 'Last 36 months' },
]

// local v-model state (don’t bind directly to props)
const months = ref(Number(props.filters?.months ?? 12))
const topN = ref(Number(props.filters?.topN ?? 10)) // keep for later

const isLoading = ref(false)

/** Keep local filters in sync if server updates props (back/forward navigation etc.) */
watch(
  () => props.filters,
  (f) => {
    const nextMonths = Number(f?.months ?? 12)
    if (months.value !== nextMonths) months.value = nextMonths

    const nextTopN = Number(f?.topN ?? 10)
    if (topN.value !== nextTopN) topN.value = nextTopN
  },
  { deep: true }
)

/** Trigger KPI refresh when months changes */
watch(months, () => {
  refreshKpis()
})

function refreshKpis() {
  isLoading.value = true

  router.get(
    route('dashboard'),
    { months: months.value, topN: topN.value },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true, // keeps URL updated without adding history spam
      only: ['kpis', 'filters'],
      onFinish: () => { isLoading.value = false },
    }
  )
}

/** ----------------------
 * Theme + chart building
 * --------------------- */
const isDarkMode = ref(false)

const donutChartOptions = ref({})
const lineChartOptions = ref({})
const histogramOptions = ref({})
const stackedBarOptions = ref({})

const ownerDistribution = computed(() => props.kpis?.owner_distribution ?? [])
const reportsTimeline = computed(() => props.kpis?.reports_timeline ?? { xAxisData: [], seriesData: [] })
const dbhDistribution = computed(() => props.kpis?.dbh_distribution ?? { xAxisData: [], seriesData: [] })
const healthByNeighborhood = computed(() => props.kpis?.health_by_neighborhood ?? { xAxisData: [], seriesData: [] })

function createChartOptions() {
  const dark = isDarkMode.value

  donutChartOptions.value = useDonutChartOptions(
    ownerDistribution.value,
    'Tree Owner Distribution',
    dark
  )

  lineChartOptions.value = useLineChartOptions(
    reportsTimeline.value,
    'Citizen Reports Timeline',
    'Reports',
    dark
  )

  histogramOptions.value = useHistogramOptions(
    dbhDistribution.value,
    'DBH (cm) Distribution',
    'Number of Trees',
    'Trees',
    dark
  )

  stackedBarOptions.value = useStackedBarChartOptions(
    healthByNeighborhood.value,
    'Health Status by Neighborhood',
    'Trees',
    dark
  )
}

function readDarkModeFromDom() {
  isDarkMode.value = document.documentElement.classList.contains('dark')
}

onMounted(() => {
  readDarkModeFromDom()
  createChartOptions()

  const observer = new MutationObserver(() => {
    const next = document.documentElement.classList.contains('dark')
    if (next !== isDarkMode.value) isDarkMode.value = next
  })
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
})

watch(isDarkMode, () => createChartOptions())

watch(
  () => props.kpis,
  () => createChartOptions(),
  { deep: true }
)
</script>
