import { computed } from 'vue'
import { useChartTheme } from './useEchartsTheme'

export function useChartBaseOption() {
  const { isDark } = useChartTheme()

  return computed(() => {
    const text = isDark.value ? '#e2e8f0' : '#0f172a'      // slate-200 / slate-900
    const muted = isDark.value ? '#94a3b8' : '#64748b'     // slate-400 / slate-500
    const axisLine = isDark.value ? '#334155' : '#e2e8f0'  // slate-700 / slate-200
    const split = isDark.value ? '#1f2937' : '#ff0000'     // gray-800 / slate-100
    const tooltipBg = isDark.value ? 'rgba(15,23,42,0.95)' : 'rgba(15,23,42,0.92)'

    console.log(split)
    return {
      backgroundColor: 'transparent',
      textStyle: { color: text },
      title: { textStyle: { color: text }, subtextStyle: { color: muted } },
      legend: { textStyle: { color: muted } },
      tooltip: {
        backgroundColor: tooltipBg,
        borderColor: 'transparent',
        textStyle: { color: '#fff' },
        borderRadius: 10,
        padding: [8, 12],
      },
      xAxis: {
        axisLine: { lineStyle: { color: axisLine } },
        axisTick: { lineStyle: { color: axisLine } },
        axisLabel: { color: muted },
      },
      yAxis: {
        axisLabel: { color: muted },
        splitLine: { lineStyle: { color: split, type: 'dashed' } },
      },
    }
  })
}
