import { computed } from 'vue'

/**
 * Composable for formatting dates as DD/MM/YYYY HH:MM (24h)
 * with internal timezone fallback.
 */
export function useDateFormatter() {

  // --- Define your preferred application timezone here ---
  //
  // Options:
  // 1. Hardcode a project-wide timezone:
  //      const preferredTimeZone = 'Europe/Nicosia'
  //
  // 2. Or leave null to ALWAYS use browser timezone:
  //      const preferredTimeZone = null
  //
  const preferredTimeZone = 'Europe/Nicosia'   // <--- set your default here


  // --- Fallback to browser timezone ---
  const browserTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone

  const effectiveTimeZone = computed(() =>
    preferredTimeZone || browserTimeZone
  )


  const formatDateTime = (value) => {
    if (!value) return '-'

    const date = new Date(value)
    if (isNaN(date.getTime())) return '-'

    const formatter = new Intl.DateTimeFormat('en-GB', {
      timeZone: effectiveTimeZone.value,
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      hour12: false, // 24h
    })

    const parts = formatter.formatToParts(date)
    const get = (type) => parts.find(p => p.type === type)?.value ?? ''

    const day = get('day')
    const month = get('month')
    const year = get('year')
    const hour = get('hour')
    const minute = get('minute')

    return `${day}/${month}/${year} ${hour}:${minute}`
  }

  return {
    formatDate: formatDateTime,
    formatDateTime,
    timeZone: effectiveTimeZone,
  }
}
