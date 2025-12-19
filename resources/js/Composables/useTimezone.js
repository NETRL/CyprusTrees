import { usePage } from "@inertiajs/vue3"
import axios from "axios"

export function syncTimezoneOnce() {
  const page = usePage()
  const backendTz = page.props.auth?.user?.timezone ?? null
  const clientTz = Intl.DateTimeFormat().resolvedOptions().timeZone

  console.log(backendTz)
  console.log(clientTz)
  if (!clientTz) return
  if (backendTz === clientTz) return

  // prevent spamming on every navigation
  const key = 'tz_synced_v1'
  const last = localStorage.getItem(key)
  console.log(last)
  if (last === clientTz) return

  axios.post(route('me.timezone.store'), { timezone: clientTz })
    .then(() => localStorage.setItem(key, clientTz))
    .catch(() => {}) // don’t break the app if it fails
}