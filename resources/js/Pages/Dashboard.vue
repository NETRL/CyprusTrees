<template>

    <Head title="Dashboard" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <DonutChart title="Health Status" :data="donutChartData" />
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <LineChart :categories="dates" :series="lineSeries" />
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <StackedBarChart :categories="dates" :series="lineSeries" />
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <HistogramChart :categories="dates" :counts="thiCounts" :bins="thiBins" />
        </div>
    </div>
</template>

<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DonutChart from '@/Charts/Components/Charts/DonutChart.vue';
import TestChart from '@/Charts/Components/Charts/TestChart.vue';
import LineChart from '@/Charts/Components/Charts/LineChart.vue';
import StackedBarChart from '@/Charts/Components/Charts/StackedBarChart.vue';
import HistogramChart from '@/Charts/Components/Charts/HistogramChart.vue';
import { computed } from 'vue';

defineOptions({
    layout: AuthenticatedLayout
});

const randomInt = () =>
    Math.floor(Math.random() * (1000 - 1 + 1)) + 1;


const donutChartData = [
    { name: 'Excellent', value: randomInt() },
    { name: 'Good', value: randomInt() },
    { name: 'Fair', value: randomInt() },
    { name: 'Poor', value: randomInt() },
    { name: 'Critical', value: randomInt() },
    { name: 'Unknown', value: randomInt() },
]


const lineSeries = [
    { name: 'Excellent', data: Array.from({ length: 8 }, () => randomInt()) },
    { name: 'Good', data: Array.from({ length: 8 }, () => randomInt()) },
    { name: 'Fair', data: Array.from({ length: 8 }, () => randomInt()) },
    { name: 'Poor', data: Array.from({ length: 8 }, () => randomInt()) },
    { name: 'Critical', data: Array.from({ length: 8 }, () => randomInt()) },
    { name: 'Unknown', data: Array.from({ length: 8 }, () => randomInt()) },
];

const thiHistogramTable = [
    { range: '0–10', count: 12 },
    { range: '10–20', count: 34 },
    { range: '20–30', count: 78 },
    { range: '30–40', count: 140 },
    { range: '40–50', count: 220 },
    { range: '50–60', count: 310 },
    { range: '60–70', count: 410 },
    { range: '70–80', count: 520 },
    { range: '80–90', count: 390 },
    { range: '90–100', count: 156 },
]

const thiBins = computed(() =>
  thiHistogramTable.map(r => r.range)
)

const thiCounts = computed(() =>
  thiHistogramTable.map(r => r.count)
)

</script>
