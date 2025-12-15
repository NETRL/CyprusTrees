import { onBeforeUnmount, onMounted } from 'vue'

export function useChartResize(chartRef, rootElRef) {
  let ro

  onMounted(() => {
    if (!rootElRef?.value) return

    ro = new ResizeObserver(() => {
      // vue-echarts exposes chart instance on the component via `getEchartsInstance()`
      const inst = chartRef?.value?.getEchartsInstance?.()
      inst?.resize?.()
    })

    ro.observe(rootElRef.value)
  })

  onBeforeUnmount(() => ro?.disconnect())
}
