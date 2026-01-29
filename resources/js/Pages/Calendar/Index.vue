<template>
  <div class="h-full flex flex-col gap-4">
    <!-- Header row -->
    <div class="flex items-center justify-between">
      <h1 class="text-lg sm:text-xl font-semibold">
        Maintenance Calendar
      </h1>
    </div>

    <!-- Calendar card fills remaining height -->
    <div class="flex-1 min-h-0">
      <div class="h-full rounded-2xl shadow
               bg-white dark:bg-slate-900
               border border-slate-200/60 dark:border-slate-700/60">
        <EventCalendar :initial-date="new Date()" :events="events" @day-click="handleDayClick" />
      </div>
    </div>
  </div>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EventCalendar from '@/Pages/Calendar/Partials/EventCalendar.vue'
import { useCalendarUrlState } from '@/Composables/useCalendarUrlState'
import { ref, watch } from 'vue';

defineOptions({
  layout: AuthenticatedLayout
})


const props = defineProps({
  maintenanceCalendarEvents: {
    type: Array,
    default: () => [],
  },
  events: {
    type: Array,
    default: () => [],
  },
})

const events = ref(props.events)

const { view, date } = useCalendarUrlState()

const cache = new Map()
let aborter = null


async function fetchEvents() {
  const v = view.value ?? 'month'
  const d = date.value ?? new Date().toISOString().slice(0, 10)


  // Cache key by view + appropriate granularity
  const cacheKey =
    v === 'month' ? `${v}:${d.slice(0, 7)}`
      : v === 'year' ? `${v}:${d.slice(0, 4)}`
        : `${v}:${d}`

  if (cache.has(cacheKey)) {
    events.value = cache.get(cacheKey)
    return
  }

  aborter?.abort()
  aborter = new AbortController()

  const qs = new URLSearchParams({ view: v, date: d }).toString()
  const res = await fetch(route('calendar.events') + `?${qs}`, {
    headers: { 'Accept': 'application/json' },
    signal: aborter.signal,
  })

  if (!res.ok) throw new Error(`Failed: ${res.status}`)
  const data = await res.json()

  cache.set(cacheKey, data)
  events.value = data
}

watch([view, date], fetchEvents, { immediate: true })
</script>
