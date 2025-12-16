<template>
  <div class="bg-white dark:bg-gray-800 dark:border dark:border-gray-700 
           touch-pan-y p-3 rounded-lg shadow-md 
           flex flex-col" ref="chartRef" :style="{ height: height, width: width }">

    <div class="mb-2">
      <slot name="control">
      </slot>
    </div>

    <VChart :option="chartOptions" :autoresize="true" class="grow" :style="{ width: '100%', minHeight: '0' }" />
    <div class="mb-2">
      <slot name="info">
      </slot>
    </div>
  </div>
</template>

<script setup>
import { watch, shallowRef } from 'vue';
import VChart from 'vue-echarts';
import { use } from 'echarts/core';

import { CanvasRenderer } from 'echarts/renderers'; // Import the rendered
import { PieChart, BarChart, LineChart } from 'echarts/charts'; // Import the chart types
import { TitleComponent, TooltipComponent, LegendComponent, GridComponent, DataZoomComponent } from 'echarts/components'; // Import the components

// Props
const props = defineProps({
  options: {
    type: Object,
    required: true
  },
  height: {
    type: String,
    default: '400px'
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
