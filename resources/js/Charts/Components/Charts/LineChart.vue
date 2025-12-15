<script setup>
import { computed } from 'vue'
import BaseChart from '@/Charts/Components/BaseChart.vue'
import { useResponsiveText } from '@/Composables/Charts/useResponsiveText'
import { useChartTheme } from '@/Composables/Charts/useEchartsTheme'

const props = defineProps({
  title: { type: String, default: '' },
  categories: { type: Array, default: () => [] },
  series: { type: Array, default: () => [] },
  height: { type: [Number, String], default: 320 },
})

const { isMobile, textSize } = useResponsiveText()
const { colors } = useChartTheme()

const option = computed(() => ({
  title: props.title ? { 
    text: props.title, 
    left: 'left',
    // We override color here manually if we want the title to be bolder
    textStyle: { fontSize: textSize.value.title, color: colors.value.textPrimary } 
  } : undefined,

  // IMPORTANT: 'axis' trigger is required for Line Charts to show tooltip anywhere on the vertical line
  tooltip: { 
    trigger: 'axis',
  },

  legend: { 
    top: 0,
    type: 'scroll' 
  },

  grid: { 
    left: isMobile.value ? 5 : 10, 
    right: isMobile.value ? 10 : 20, 
    top: 40, 
    bottom: 20, 
    containLabel: true 
  },

  xAxis: { 
    type: 'category', 
    data: props.categories,
    boundaryGap: false, // Cleaner look for line charts
    axisLabel: { 
        fontSize: textSize.value.axis,
        // Optional: truncate x-axis labels on mobile
        formatter: (val) => isMobile.value && val.length > 5 ? val.substring(0, 5) + '..' : val
    }
  },

  yAxis: { 
    type: 'value',
    axisLabel: { fontSize: textSize.value.axis }
  },

  series: props.series.map(s => ({
    type: 'line',
    name: s.name,
    data: s.data ?? [],
    smooth: true,
    showSymbol: !isMobile.value, // Hide dots on mobile for cleaner UI
    symbolSize: 6,
    lineStyle: { width: 3 }
  })),
}))
</script>

<template>
  <BaseChart :option="option" :height="height" />
</template>