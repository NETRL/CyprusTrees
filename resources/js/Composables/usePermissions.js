import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function usePermissions() {
  const page = usePage()

  const names = computed(() =>
    new Set((page.props.auth?.permissions ?? []).map(p => p.name))
  )

  const can = (permission) => names.value.has(permission) // boolean

  return { can }
}
