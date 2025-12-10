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
               border border-slate-200/60 dark:border-slate-700/60
               p-3 sm:p-4">
        <MonthCalendar :initial-date="new Date()" :events="myEvents" @day-click="handleDayClick" />
      </div>
    </div>
  </div>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MonthCalendar from '@/Pages/Calendar/Partials/MonthCalendar.vue'

defineOptions({
  layout: AuthenticatedLayout
})

const myEvents = [
  { id: 1, title: 'Team Sync Up', start: '2025-12-10T09:30:00', color: 'bg-indigo-500' },
  { id: 2, title: 'Project Deadline', start: '2025-12-10T17:00:00', color: 'bg-red-500' },
  { id: 3, title: 'Gym Session', start: '2025-12-12T18:00:00', color: 'bg-emerald-500' },
  { id: 4, title: 'Project Deadline', start: '2025-12-10T17:00:00', color: 'bg-red-500' },
  { id: 5, title: 'Project Deadline', start: '2025-12-10T17:00:00', color: 'bg-red-500' },
  // ... more events
]
</script>
