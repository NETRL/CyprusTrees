<template>

  <Head title="Dashboard" />

  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-auto-fit gap-5 sm:gap-2.5 p-2 ">
    <BaseChart :options="donutChartOptions" title="Ownership Distribution" height="400px" />
    <BaseChart :options="lineChartOptions" title="Citizen Reports Filed" height="400px" />
    <BaseChart :options="histogramOptions" title="DBH (cm) Distribution" height="400px" />
    <BaseChart :options="stackedBarOptions" title="Health Status by Neighborhood" height="400px" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import BaseChart from '@/Charts/Components/BaseChart.vue'
import {
  useDonutChartOptions,
  useLineChartOptions,
  useHistogramOptions,
  useStackedBarChartOptions
} from '@/Composables/Charts/useGenericCharts';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineOptions({
  layout: AuthenticatedLayout
})

const isDarkMode = ref(false); // Default to light mode

// --- ECHARTS OPTION REFS ---
const donutChartOptions = ref({});
const lineChartOptions = ref({});
const histogramOptions = ref({});
const stackedBarOptions = ref({});

// --- MOCK BACKEND DATA (SQL results shaped into JS objects) ---

// 1. Donut Data (e.g., SELECT owner_type, COUNT(*) FROM trees GROUP BY 1)
const mockDonutData = [
  { value: 6500, name: 'Municipal' },
  { value: 2500, name: 'Private' },
  { value: 1000, name: 'Park/Common' },
];

// 2. Line Data (e.g., Reports over 6 months)
const mockLineData = {
  xAxisData: ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  seriesData: [
    { name: 'New Reports', data: [50, 65, 80, 72, 90, 85] },
    { name: 'Reports Resolved', data: [45, 60, 70, 75, 85, 95] }
  ]
};

// 3. Histogram Data (e.g., DBH size buckets)
const mockHistogramData = {
  xAxisData: ['<10cm', '10-25cm', '25-50cm', '>50cm'],
  seriesData: [1200, 3500, 4100, 1200]
};

// 4. Stacked Bar Data (e.g., Health Status by Neighborhood)
const mockStackedBarData = {
  xAxisData: ['Neighborhood A', 'Neighborhood B', 'Neighborhood C', 'Neighborhood D'],
  seriesData: [
    { name: 'Good', data: [300, 450, 150, 500] },
    { name: 'Fair', data: [150, 100, 120, 200] },
    { name: 'Poor', data: [50, 70, 80, 50] }
  ]
};

onMounted(() => {
  isDarkMode.value = document.documentElement.classList.contains('dark');
});

const createChartOptions = () => {
  const mode = isDarkMode.value;
  donutChartOptions.value = useDonutChartOptions(mockDonutData, 'Tree Owner Distribution', mode);
  lineChartOptions.value = useLineChartOptions(mockLineData, 'Citizen Reports Timeline', 'Reports', mode);
  histogramOptions.value = useHistogramOptions(mockHistogramData, 'Tree Diameter (DBH) Profile', 'Number of Trees', 'DBH Bins', mode);
  stackedBarOptions.value = useStackedBarChartOptions(mockStackedBarData, 'Tree Health by Region', 'Tree Count', mode);
};

watch(isDarkMode, createChartOptions);

onMounted(() => {
  // Initial creation of charts
  createChartOptions();
});
</script>