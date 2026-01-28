import { computed, unref } from 'vue'

export function useTreeOptions(treeProps) {
  return computed(() =>
    (unref(treeProps) ?? []).map((t) => {
      const common = t.species?.common_name ?? 'Unknown species'
      const latin = t.species?.latin_name ? ` (${t.species.latin_name})` : ''
      const tags = t.tags_label ? ` · ${t.tags_label}` : ''

      return {
        value: t.id,
        label: `${t.id} - ${common}${latin}${tags}`,
      }
    })
  )
}
