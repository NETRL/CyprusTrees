// useRenamedHeaders.js
import { computed, unref } from 'vue'

export function useRenamedHeaders(rawColumns, labelOverrides = {}) {
  const columns = computed(() => {
    const source = unref(rawColumns)

    // If nothing yet, just return something harmless
    if (!source) return null

    // Helper to rename only the headers we override
    const mapItems = (items) => {
      if (!Array.isArray(items)) return []
      return items.map(col => {
        const currentHeader = col.header
        const override = currentHeader ? labelOverrides[currentHeader] : null

        if (!override) return col

        return {
          ...col,
          header: override,
        }
      })
    }

    // Case 1: your usual `{ label, items: [...] }`
    if (Array.isArray(source.items)) {
      return {
        ...source,
        items: mapItems(source.items),
      }
    }

    // Case 2: plain array input `[{ field, header, ... }]`
    if (Array.isArray(source)) {
      return mapItems(source)
    }

    // Fallback: unknown shape, just return as-is
    return source
  })

  return { columns }
}
