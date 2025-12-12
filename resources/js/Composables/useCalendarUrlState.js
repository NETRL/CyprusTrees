import { computed } from "vue"
import { router, usePage } from "@inertiajs/vue3"

const DEFAULTS = {
  view: "month",
  date: () => new Date().toISOString().slice(0, 10),
}

function clampView(v) {
  return ["month", "year", "day"].includes(v) ? v : DEFAULTS.view
}

function clampDate(d) {
  return /^\d{4}-\d{2}-\d{2}$/.test(d) ? d : DEFAULTS.date()
}

export function useCalendarUrlState() {
  const page = usePage()

  const query = computed(() => {
    const url = new URL(page.url, window.location.origin)
    const sp = url.searchParams
    return {
      raw: Object.fromEntries(sp.entries()),
      hasView: sp.has("view"),
      hasDate: sp.has("date"),
    }
  })

  const hasCalendarState = computed(() => query.value.hasView || query.value.hasDate)

  function updateQuery(patch, { replace = true } = {}) {
    const url = new URL(page.url, window.location.origin)
    const sp = url.searchParams

    Object.entries(patch).forEach(([k, v]) => {
      if (v == null || v === "") sp.delete(k)
      else sp.set(k, String(v))
    })

    const qs = sp.toString()
    router.get(qs ? `${url.pathname}?${qs}` : url.pathname, {}, {
      preserveScroll: true,
      preserveState: true,
      replace,
    })
  }

  // If param is missing, return null (so Calendar can decide what to do)
  const view = computed({
    get: () => (query.value.hasView ? clampView(query.value.raw.view) : null),
    set: (v) => updateQuery({ view: clampView(v) }),
  })

  const date = computed({
    get: () => (query.value.hasDate ? clampDate(query.value.raw.date) : null),
    set: (d) => updateQuery({ date: clampDate(d) }),
  })

  return { view, date, updateQuery, hasCalendarState }
}
