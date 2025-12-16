<template>
  <div
    ref="chartRef"
    class="relative flex flex-col rounded-xl bg-white p-1 shadow-[0_1px_2px_rgba(0,0,0,0.05)] ring-1 ring-slate-200 dark:bg-gray-800 dark:ring-gray-700"
    :style="{ height, width }"
  >
    <div v-if="$slots.control" class="absolute right-4 top-4 z-10">
      <slot name="control"></slot>
    </div>

    <div class="grow p-2">
      <VChart :option="chartOptions" :autoresize="true" class="h-full w-full" />
    </div>

    <div v-if="$slots.info" class="px-4 pb-3">
      <slot name="info"></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, toRaw } from 'vue'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'

import { CanvasRenderer } from 'echarts/renderers'
import { PieChart, BarChart, LineChart } from 'echarts/charts'
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  DataZoomComponent
} from 'echarts/components'

use([
  CanvasRenderer,
  PieChart,
  BarChart,
  LineChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  DataZoomComponent
])

const props = defineProps({
  options: { type: Object, required: true },
  height: { type: String, default: '400px' },
  width: { type: String, default: '100%' }
})

const chartRef = ref(null)
const containerWidth = ref(0)
let ro = null

onMounted(() => {
  if (!chartRef.value) return
  ro = new ResizeObserver((entries) => {
    containerWidth.value = Math.floor(entries?.[0]?.contentRect?.width || 0)
  })
  ro.observe(chartRef.value)
})

onBeforeUnmount(() => {
  ro?.disconnect()
  ro = null
})

function wrapTitle(text, maxChars) {
  if (!text || typeof text !== 'string') return text
  if (text.includes('\n')) return text
  if (!text.includes(' ')) return text // single long token; don't split weirdly

  const words = text.split(' ')
  const lines = []
  let line = ''

  for (const w of words) {
    if ((line + w).length > maxChars) {
      if (line.trim()) lines.push(line.trim())
      line = w + ' '
    } else {
      line += w + ' '
    }
  }
  if (line.trim()) lines.push(line.trim())
  return lines.join('\n')
}

function deepClonePlain(obj) {
  if(!obj) return;
  // Turn Vue proxies into plain objects first, then JSON-clone.
  // ECharts options are plain data (functions are not needed), so JSON is fine here.
  const raw = toRaw(obj)
  return JSON.parse(JSON.stringify(raw))
}

function wrapTitleInOption(opt, maxChars) {
  if (!opt || !opt.title) return

  const wrapOne = (t) => {
    if (!t) return
    if (typeof t.text === 'string') t.text = wrapTitle(t.text, maxChars)
  }

  if (Array.isArray(opt.title)) opt.title.forEach(wrapOne)
  else wrapOne(opt.title)
}

const chartOptions = computed(() => {
  const opt = deepClonePlain(props.options)

  // heuristic for fontSize ~15 (adjust divisor if needed)
  const maxChars = Math.max(14, Math.floor((containerWidth.value || 0) / 9))

  wrapTitleInOption(opt, maxChars)
  return opt
})
</script>
