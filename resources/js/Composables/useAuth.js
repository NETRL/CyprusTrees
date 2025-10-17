import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useAuth() {
  const page = usePage()

  // The raw user object from backend share
  const user = computed(() => page.props.auth?.user ?? null)

  // Reactive boolean flag
  const isAuthenticated = computed(() => !!user.value?.id)

  return { user, isAuthenticated }
}
