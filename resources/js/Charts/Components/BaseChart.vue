<script setup>
import { computed, ref, onMounted } from 'vue'
import { VChart } from '@/Charts/echarts'
import { useChartTheme } from '@/Composables/Charts/useEchartsTheme'
import { useChartResize } from '@/Composables/Charts/useChartResize'
import { useChartOption } from '@/Composables/Charts/useChartOptions'

const props = defineProps({
  option: { type: Object, required: true },
  height: { type: [Number, String], default: 300 },
  loading: { type: Boolean, default: false },
  autoresize: { type: Boolean, default: true },
  baseOption: { type: Object, default: () => ({}) },
  noData: { type: Boolean, default: false },
})

const emit = defineEmits(['ready', 'click'])
const rootEl = ref(null)
const chart = ref(null)
const error = ref(null)

const { theme, colors } = useChartTheme()

// Global Defaults for ALL charts
const defaultOption = computed(() => ({
  backgroundColor: 'transparent',
  
  // 1. Global Text Style (Applies to Title, Legend, etc.)
  textStyle: {
    fontFamily: 'inherit',
    color: colors.value.textSecondary // Default to secondary color
  },
  
  // 2. Global Tooltip Styling (Solves visibility issues)
  tooltip: {
    confine: true, // Keep inside chart
    backgroundColor: colors.value.tooltipBg,
    borderColor: colors.value.tooltipBorder,
    borderWidth: 1,
    padding: [8, 12],
    textStyle: {
      color: colors.value.tooltipText,
      fontSize: 13
    },
    // Force high z-index to sit above everything
    extraCssText: 'z-index: 100; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 8px;'
  },

  // 3. Global Legend Styling
  legend: {
    textStyle: {
      color: colors.value.textSecondary
    },
    pageIconColor: colors.value.textSecondary,
    pageTextStyle: {
      color: colors.value.textSecondary
    }
  },

  // 4. Global Axis Styling (Lines and Labels)
  xAxis: {
    axisLine: { lineStyle: { color: colors.value.axisLine } },
    axisLabel: { color: colors.value.textSecondary },
    splitLine: { show: false }
  },
  yAxis: {
    axisLine: { show: false },
    axisLabel: { color: colors.value.textSecondary },
    splitLine: { 
      lineStyle: { 
        color: colors.value.splitLine,
        type: 'dashed' 
      } 
    },
    nameTextStyle: { color: colors.value.textSecondary }
  }
}))

const mergedBase = useChartOption({ baseOption: defaultOption.value, option: props.baseOption })
const finalOption = useChartOption({ baseOption: mergedBase.value, option: props.option })

const style = computed(() => ({
  height: typeof props.height === 'number' ? `${props.height}px` : props.height,
  width: '100%',
  minHeight: '200px',
}))

const isEmpty = computed(() => {
  if (props.noData) return true
  if (!props.option.series) return true
  const series = Array.isArray(props.option.series) ? props.option.series : [props.option.series]
  return series.every(s => !s.data || s.data.length === 0)
})

if (props.autoresize) useChartResize(chart, rootEl)

function onReady(instance) {
  error.value = null
  emit('ready', instance)
}
</script>

<template>
  <div 
    ref="rootEl" 
    class="relative w-full rounded-xl bg-white dark:bg-gray-800 transition-colors duration-300 border border-gray-100 dark:border-gray-700 shadow-sm"
    :style="{ height: style.height }"
  >
    <div v-if="loading" class="absolute inset-0 z-10 flex items-center justify-center bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="isEmpty" class="absolute inset-0 z-10 flex flex-col items-center justify-center text-gray-400">
      <span class="text-sm">No data available</span>
    </div>

    <VChart
      v-else
      ref="chart"
      class="w-full h-full"
      :option="finalOption"
      :theme="theme"
      :autoresize="autoresize"
      @ready="onReady"
      @click="(p) => emit('click', p)"
    />
  </div>
</template>