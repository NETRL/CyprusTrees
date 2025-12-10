<template>
  <AuthenticatedLayout>
    <!-- Page wrapper: full viewport minus layout header -->
    <div class="flex flex-col h-[calc(100vh-4rem)] px-3 py-3 gap-3">
      <!-- Top bar -->
      <div class="flex items-center justify-between">
        <h2 class="text-lg sm:text-xl font-semibold">
          Maintenance Calendar
        </h2>

        <!-- Placeholder for future filters / actions -->
        <div class="flex items-center gap-2 text-xs sm:text-sm">
          <span class="px-2 py-1 rounded-full border border-slate-300 dark:border-slate-600">
            Read-only
          </span>
        </div>
      </div>

      <!-- Calendar container: fills remaining height -->
      <div class="flex-1 min-h-0">
        <div class="h-full bg-white dark:bg-slate-900 rounded-2xl shadow p-2 sm:p-4">
          <FullCalendar :options="calendarOptions"/>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const isMobile = ref(false)

const updateIsMobile = () => {
  if (typeof window === 'undefined') return
  isMobile.value = window.innerWidth < 768 // Tailwind's md breakpoint
}

onMounted(() => {
  updateIsMobile()
  window.addEventListener('resize', updateIsMobile)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateIsMobile)
})

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],

  // Different default view on mobile vs desktop
  initialView: isMobile.value ? 'listWeek' : 'dayGridMonth',

  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
  },

  // Critical for "fill parent"
  height: '100%',
  expandRows: true,
  stickyHeaderDates: true,

  // No data yet
  events: [],

  // UX tweaks
  buttonText: {
    today: 'Today',
    month: 'Month',
    week: 'Week',
    day: 'Day',
    list: 'List',
  },
  firstDay: 1, // Monday
  locale: 'en-gb', // change later if you want

  // No interaction yet – you'll enable later
  selectable: false,
  editable: false,
  navLinks: true,
  eventTimeFormat: {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  },
}))
</script>

<style scoped>
/* Make FullCalendar fonts a bit smaller on mobile */
:deep(.fc) {
  font-size: 0.75rem; /* 12px */
}


/* Make header toolbar compact on mobile */
:deep(.fc .fc-toolbar.fc-header-toolbar) {
  gap: 0.25rem;
  flex-wrap: nowrap;
}

:deep(.fc .fc-col-header-cell-cushion){
font-size: 0.55rem;
}

:deep(.fc .fc-toolbar-title) {
  font-size: 1rem;
  font-weight: 600;
}
</style>
