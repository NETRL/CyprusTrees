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
        <MonthCalendar :initial-date="new Date()" :events="events" @day-click="handleDayClick" />
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
</script>
