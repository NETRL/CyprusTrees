<template>
  <div class="chart-container" ref="chartRef" :style="{ height: height, width: width }">
    <v-chart :option="chartOptions" :autoresize="true" :style="{ height: '100%', width: '100%' }" />
  </div>
</template>

<script setup>
import { defineProps, watch, shallowRef } from 'vue';
import VChart from 'vue-echarts'; 
import { use } from 'echarts/core';

// Import the rendered
import { CanvasRenderer } from 'echarts/renderers';

// Import the chart types
import { PieChart } from 'echarts/charts'; // For Donut Charts
import { BarChart } from 'echarts/charts';
import { LineChart } from 'echarts/charts';

// Import the components
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  DataZoomComponent,
} from 'echarts/components';

// Props
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
