// js/Composables/Charts/useEchartsTheme.js
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'

export function useChartTheme() {
  const isDark = ref(false)

  const read = () => {
    isDark.value = document.documentElement.classList.contains('dark')
  }

  let observer
  onMounted(() => {
    read()
    observer = new MutationObserver(read)
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
  })

  onBeforeUnmount(() => observer?.disconnect())

  const theme = computed(() => (isDark.value ? 'dark' : 'light'))

  // Define global colors for charts here
  const colors = computed(() => {
    const dark = isDark.value
    return {
      // Text
      textPrimary: dark ? '#f3f4f6' : '#1f2937',   // gray-100 vs gray-800
      textSecondary: dark ? '#9ca3af' : '#6b7280', // gray-400 vs gray-500
      
      // Lines & Borders
      axisLine: dark ? '#374151' : '#e5e7eb',      // gray-700 vs gray-200
      splitLine: dark ? '#374151' : '#f3f4f6',     // gray-700 vs gray-100
      
      // Tooltip specific
      tooltipBg: dark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)',
      tooltipBorder: dark ? '#4b5563' : '#e5e7eb',
      tooltipText: dark ? '#f3f4f6' : '#1f2937'
    }
  })

  return { isDark, theme, colors }
}