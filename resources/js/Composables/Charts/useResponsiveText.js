import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

export function useResponsiveText() {
  const width = ref(window.innerWidth)
  let timeoutId = null

  const update = () => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
      width.value = window.innerWidth
    }, 100)
  }

  onMounted(() => window.addEventListener('resize', update))
  onBeforeUnmount(() => {
    window.removeEventListener('resize', update)
    clearTimeout(timeoutId)
  })

  const isMobile = computed(() => width.value < 768)

  // Centralized standard sizes for consistency
  const textSize = computed(() => ({
    title: isMobile.value ? 14 : 16,
    subtitle: isMobile.value ? 11 : 12,
    axis: isMobile.value ? 10 : 12,
    legend: isMobile.value ? 11 : 12,
    tooltip: isMobile.value ? 12 : 14,
  }))

  return { isMobile, textSize }
}