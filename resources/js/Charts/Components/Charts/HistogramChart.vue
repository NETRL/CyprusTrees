<script setup>
import { computed } from 'vue'
import BaseChart from '@/Charts/Components/BaseChart.vue'
import * as echarts from 'echarts/core' // Needed for gradient
import { useResponsiveText } from '@/Composables/Charts/useResponsiveText'

const props = defineProps({
  title: { type: String, default: '' },
  bins: { type: Array, default: () => [] },
  counts: { type: Array, default: () => [] },
  height: { type: [Number, String], default: 320 },
  color: { type: String, default: '#3b82f6' },
  unit: { type: String, default: '' },
})

const { isMobile, textSize } = useResponsiveText()

const option = computed(() => ({
  title: props.title ? { 
    text: props.title, 
    left: '10', // Slight padding from left
    top: '10',
    textStyle: { fontSize: textSize.value.title, fontWeight: 600 } 
  } : undefined,
  
  tooltip: { 
    trigger: 'axis',
    formatter: (params) => {
      const p = params[0]
      return `<div class="font-semibold">${p.name}</div>
              <div class="text-xs text-gray-500 mt-1">
                Count: <span class="text-gray-900 font-bold dark:text-white">${p.value}</span>
              </div>`
    }
  },

  grid: { 
    left: isMobile.value ? 10 : 20, 
    right: isMobile.value ? 10 : 20, 
    top: props.title ? 60 : 30, 
    bottom: 10, 
    containLabel: true 
  },

  xAxis: { 
    type: 'category', 
    data: props.bins,
    axisTick: { show: false },
    axisLine: { lineStyle: { color: '#e5e7eb' } }, // light gray
    axisLabel: { 
      fontSize: textSize.value.axis,
      color: '#6b7280',
      interval: isMobile.value ? 'auto' : 0, // Auto hide labels on mobile to prevent overlap
      hideOverlap: true
    } 
  },

  yAxis: { 
    type: 'value',
    splitLine: { lineStyle: { type: 'dashed', color: '#f3f4f6' } },
    axisLabel: { color: '#9ca3af', fontSize: textSize.value.axis }
  },

  series: [{
    type: 'bar',
    data: props.counts,
    barMaxWidth: 50,
    itemStyle: {
      color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
        { offset: 0, color: props.color },
        { offset: 1, color: adjustOpacity(props.color, 0.6) } // Helper needed or use fixed hex
      ]),
      borderRadius: [4, 4, 0, 0]
    },
    // Optional: Smooth entrance animation
    animationDelay: (idx) => idx * 10
  }]
}))

// Simple helper to fake opacity on hex if needed, 
// or just use the prop color directly if you prefer flat.
function adjustOpacity(color, opacity) {
  return color // Implementation omitted for brevity, plain color is fine too
}
</script>

<template>
  <BaseChart 
    :option="option" 
    :height="height" 
    :loading="!bins.length && !counts.length" 
  />
</template>