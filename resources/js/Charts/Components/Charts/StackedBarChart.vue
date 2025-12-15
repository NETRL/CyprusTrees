<script setup>
import { computed } from 'vue'
import BaseChart from '@/Charts/Components/BaseChart.vue'
import { useChartTheme } from '@/Composables/Charts/useEchartsTheme'
import { useResponsiveText } from '@/Composables/Charts/useResponsiveText'

const props = defineProps({
  title: { type: String, default: '' },
  categories: { type: Array, default: () => [] },
  series: { type: Array, default: () => [] },
  height: { type: [Number, String], default: 320 },
})

const { colors } = useChartTheme()
const { textSize } = useResponsiveText()

const option = computed(() => ({
  title: props.title ? { 
    text: props.title, 
    left: 'left',
    textStyle: { fontSize: textSize.value.title, color: colors.value.textPrimary }
  } : undefined,

  // Tooltip trigger axis + shadow pointer
  tooltip: { 
    trigger: 'axis', 
    axisPointer: { type: 'shadow' } // Highlights the whole bar column
  },

  legend: { top: 0 },
  
  grid: { left: 5, right: 10, top: 40, bottom: 20, containLabel: true },
  
  xAxis: { 
    type: 'category', 
    data: props.categories 
  },
  
  yAxis: { type: 'value' },
  
  series: props.series.map(s => ({
    type: 'bar',
    stack: s.stack ?? 'total', // Stacks bars on top of each other
    name: s.name,
    data: s.data ?? [],
    itemStyle: { borderRadius: 0 } // Keep stacked segments flat
  })),
}))
</script>

<template>
  <BaseChart :option="option" :height="height" />
</template>