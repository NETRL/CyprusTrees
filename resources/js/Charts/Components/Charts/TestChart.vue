<script setup>
import { ref } from 'vue'
import BaseChart from '@/Charts/Components/BaseChart.vue'

const option = ref({
  xAxis: {
    type: 'category',
    data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
  },
  yAxis: {
    type: 'value'
  },
  series: [
    {
      data: [120, 200, 150, 80, 70, 110, 130],
      type: 'bar'
    }
  ]
})
</script>

<template>
  <div class="p-4 bg-white dark:bg-gray-800 rounded-lg">
    <h3 class="text-lg font-semibold mb-4">Test Chart</h3>
    <BaseChart :option="option" :height="300" />
  </div>
</template>