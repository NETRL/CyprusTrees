<template>
  <div class="chart-container" ref="chartRef" :style="{ height: height, width: width }">
    <v-chart :option="chartOptions" :autoresize="true" :style="{ height: '100%', width: '100%' }" />
  </div>
</template>

<script setup>
import { defineProps, watch, shallowRef } from 'vue';
import VChart from 'vue-echarts'; // Ensure this import matches your library name
import { use } from 'echarts/core';

// 1. IMPORT THE RENDERER
// Choose either CanvasRenderer (generally faster for simpler charts)
// OR SVGRenderer (generally better for zooming/high-DPI, but slower initialization)
import { CanvasRenderer } from 'echarts/renderers';

// 2. IMPORT THE CHART TYPES YOU ARE USING
import { PieChart } from 'echarts/charts'; // For Donut Charts
import { BarChart } from 'echarts/charts';
import { LineChart } from 'echarts/charts';

// 3. IMPORT THE NECESSARY COMPONENTS (Tooltip, Legend, Grid, etc.)
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  DataZoomComponent,
} from 'echarts/components';

// Define props to receive the ECharts options and component size
const props = defineProps({
  options: {
    type: Object,
    required: true
  },
  height: {
    type: String,
    default: '300px'
  },
  width: {
    type: String,
    default: '100%'
  }
});

// Use shallowRef for the reactive options object
const chartOptions = shallowRef(props.options);

// Watch for changes in the parent component's data and update the chart
watch(
  () => props.options,
  (newOptions) => {
    chartOptions.value = newOptions;
  },
  { deep: true }
);

use([
  CanvasRenderer,
  PieChart,
  BarChart,
  LineChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  DataZoomComponent,
]);
</script>
