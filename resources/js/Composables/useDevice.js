import { ref, onMounted, onUnmounted, computed } from 'vue'

export function useDevice() {
  const width = ref(window.innerWidth)

  const update = () => {
    width.value = window.innerWidth
  }

  onMounted(() => window.addEventListener('resize', update))
  onUnmounted(() => window.removeEventListener('resize', update))

  const isMobile = computed(() => width.value < 768)
  const isTablet = computed(() => width.value >= 768 && width.value < 1024)
  const isDesktop = computed(() => width.value >= 1024)

  return { isMobile, isTablet, isDesktop }
}