<template>
    <div ref="rootEl"
        class="h-full flex flex-col bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 select-none rounded-2xl overflow-hidden shadow-2xl shadow-slate-300/50 dark:shadow-black/50">
        <!-- Header -->
        <header
            class="flex flex-col md:flex-row items-center justify-between gap-4 py-4 lg:py-5 px-6 bg-white/70 dark:bg-slate-900/70 backdrop-blur-lg sticky top-0 z-20 border-b border-slate-200/70 dark:border-slate-800/70">

            <div class="flex items-baseline gap-3">
                <h1 class="text-3xl font-light tracking-normal text-slate-900 dark:text-white">
                    {{ monthLabel }}
                </h1>
                <span class="text-xl font-thin text-slate-400 dark:text-slate-500">
                    {{ yearLabel }}
                </span>
            </div>

            <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end">

                <div
                    class="flex p-0.5 bg-slate-100/50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-800">
                    <button v-for="mode in ['year', 'month', 'day']" :key="mode" @click="setViewMode(mode)"
                        class="px-3 py-1 text-sm font-medium rounded-md transition-all duration-200 capitalize" :class="viewMode === mode
                            ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-300 shadow-sm ring-1 ring-black/5 dark:ring-white/10'
                            : 'text-slate-500 dark:text-slate-400 hover:text-emerald-500 dark:hover:text-emerald-400'">
                        {{ mode }}
                    </button>
                </div>

                <div class="h-6 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                <div class="flex items-center gap-2">
                    <button @click="goToToday"
                        class="hidden sm:inline-flex px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 active:scale-[0.98] transition-all duration-150">
                        Today
                    </button>

                    <div
                        class="flex items-center rounded-lg border border-slate-200 dark:border-slate-700 divide-x divide-slate-200 dark:divide-slate-700">
                        <IconButton @click="prevStep" :disabled="isAnimating"
                            class="p-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-emerald-600 transition-colors rounded-l-lg">
                            <ChevronLeftIcon class="w-5 h-5" />
                        </IconButton>
                        <IconButton @click="nextStep" :disabled="isAnimating"
                            class="p-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-emerald-600 transition-colors rounded-r-lg">
                            <ChevronRightIcon class="w-5 h-5" />
                        </IconButton>
                    </div>
                </div>
            </div>
        </header>

        <!-- Month Row -->
        <div v-if="viewMode === 'month'"
            class="grid grid-cols-7 border-b border-slate-200/70 dark:border-slate-800/70 bg-slate-50/50 dark:bg-slate-900/50">
            <div v-for="dow in weekdays" :key="dow"
                class="py-2 text-center text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-600">
                {{ dow.substring(0, 3) }}
            </div>
        </div>

        <main class="flex-1 relative overflow-hidden bg-white dark:bg-slate-900">
            <!-- Month Grid -->
        <Transition v-if="viewMode === 'month'" :name="transitionName" mode="out-in">
    <div :key="monthKey" class="absolute inset-0 grid grid-cols-7 grid-rows-6">
        <button v-for="day in days" :key="day.iso" @click="handleDayClick(day.date, $event)"
            class="group relative flex flex-col items-stretch justify-start p-1 lg:p-2 border-b border-r border-slate-100 dark:border-slate-800 transition-colors duration-150 outline-none focus:z-10 focus:ring-1 focus:ring-inset focus:ring-emerald-500/50"
            :class="[
                !day.isCurrentMonth ? 'bg-slate-50/50 dark:bg-slate-950/50 text-slate-400/70' : 'bg-white dark:bg-slate-900',
                day.isToday ? 'bg-emerald-50/10 dark:bg-emerald-900/10' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'
            ]">

            <div class="flex items-center justify-center mb-1 lg:mb-2">
                <span
                    class="text-xs lg:text-sm font-medium w-6 h-6 lg:w-7 lg:h-7 flex items-center justify-center rounded-full transition-all"
                    :class="day.isToday
                        ? 'bg-emerald-100 dark:bg-emerald-800 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-500/50'
                        : 'text-slate-700 dark:text-slate-300 group-hover:bg-slate-200/70 dark:group-hover:bg-slate-700/70'">
                    {{ day.date.getDate() }}
                </span>
            </div>

            <div class="flex-1 w-full overflow-hidden grid content-start gap-1">
                
                <div v-for="event in day.events.slice(0, getMaxEvents())" :key="event.id"
                    class="hidden sm:flex items-center px-1.5 py-0.5 lg:py-1 rounded-[3px] border-l-[3px] transition-all duration-200 hover:brightness-95 hover:translate-x-0.5"
                    :class="[
                        eventChipClasses(event), // Should provide bg-color and text-color
                        'border-current shadow-sm' // border-current uses the text color for the thin line
                    ]">
                    <span class="truncate font-medium text-[10px] lg:text-xs leading-tight">
                        {{ event.title }}
                    </span>
                </div>

                <div class="sm:hidden flex gap-1 justify-center flex-wrap content-center h-full max-h-[60%]">
                    <div v-for="event in day.events.slice(0, 4)" :key="event.id"
                        class="w-1.5 h-1.5 rounded-full ring-1 ring-white dark:ring-slate-900" 
                        :class="[eventBulletColors(event)]">
                    </div>
                </div>

                <div v-if="day.events.length > getMaxEvents()"
                    class="hidden sm:block text-center sm:text-left px-1">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                        +{{ day.events.length - getMaxEvents() }} more
                    </span>
                </div>
            </div>
        </button>
    </div>
</Transition>

            <!-- Year Grid -->
            <Transition v-else-if="viewMode === 'year'" :name="transitionName" mode="out-in" @wheel.stop>
                <div :key="yearKey" class="absolute inset-0 overflow-y-auto p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <button v-for="month in yearMonths" :key="month.index" @click="selectMonthFromYear(month.index)"
                            class="relative flex flex-col items-center justify-center p-6 rounded-xl border transition-all duration-200 group"
                            :class="month.index === currentDate.getMonth()
                                ? 'bg-emerald-50/10 dark:bg-emerald-900/10 border-emerald-300 dark:border-emerald-700 shadow-sm'
                                : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-emerald-400/50 dark:hover:border-emerald-600/50 hover:shadow-lg/50'">

                            <span class="text-xl font-medium mb-1 group-hover:text-emerald-600 transition-colors"
                                :class="month.index === currentDate.getMonth() ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white'">
                                {{ month.label }}
                            </span>
                            <span class="text-xs font-light text-slate-400 uppercase tracking-widest">
                                View Month
                            </span>
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Day View -->
            <Transition v-else :name="transitionName" mode="out-in">
                <div :key="dayKey" class="absolute inset-0 flex flex-col bg-slate-50 dark:bg-slate-950">

                    <div
                        class="px-6 py-4 lg:px-8 lg:py-6 bg-white dark:bg-slate-900 border-b border-slate-200/70 dark:border-slate-800/70">
                        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white mb-1">
                            {{ selectedDayLabel }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ selectedDayEvents.length
                            }}</span>
                            events scheduled
                        </p>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 relative">
                        <div
                            class="absolute left-10 top-6 bottom-6 w-px bg-slate-200/70 dark:bg-slate-800 ml-6 hidden sm:block">
                        </div>

                        <div v-if="selectedDayEvents.length" class="space-y-6" @wheel.stop>
                            <div v-for="event in selectedDayEvents" :key="event.id"
                                class="relative flex flex-col sm:flex-row gap-4 sm:gap-8 group">

                                <div
                                    class="sm:w-32 shrink-0 flex max-sm:justify-start justify-end items-center space-x-2">
                                    <div class="hidden sm:block w-3.5 h-3.5 rounded-full ring-4 ring-slate-50 dark:ring-slate-950 ml-2 z-10"
                                        :class="[eventBulletColors(event)]"></div>
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-200 block">
                                        {{ formatDate(event.start) }}
                                    </span>
                                </div>

                                <div :key="event.id"
                                    class="flex-1 px-4 py-3 lg:py-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all duration-300 cursor-pointer group/card"
                                    :class="[
                                        // Subtle hover effect: changes border to accent color and slightly lifts
                                        'hover:border-current hover:shadow-md',
                                        // Expanded state: maintains the accent border and shadow
                                        { [eventCardColors(event, 'border') + ' shadow-md']: isExpanded(event.id) }
                                    ]" @click="toggleExpansion(event.id)" role="button" :aria-expanded="isExpanded(event.id)"
                                    :aria-label="`${isExpanded(event.id) ? 'Collapse' : 'Expand'} event details`"
                                    tabindex="0" @keydown.enter="toggleExpansion(event.id)"
                                    @keydown.space.prevent="toggleExpansion(event.id)">

                                    <div v-if="event.title" class="flex items-start justify-between gap-4 mb-2">
                                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 leading-snug">
                                            {{ event.title }}
                                        </h3>
                                        <Link :href="route('/', { tree_id: event.tree.id })" @click.stop
                                            class="text-xs shrink-0 font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition-colors duration-200 mt-0.5">
                                        View in map
                                        </Link>
                                    </div>

                                    <div class="h-px w-full bg-slate-200 dark:bg-slate-700/50 mb-3"></div>

                                    <div class="overflow-hidden transition-[max-height] duration-500 ease-in-out"
                                        :style="{ maxHeight: isExpanded(event.id) ? '500px' : '48px' }">
                                        <p class="text-sm text-slate-600 dark:text-slate-400 whitespace-pre-line leading-relaxed line-clamp-2"
                                            :class="{ 'line-clamp-none': isExpanded(event.id) }">
                                            {{ event.description || 'No additional details provided for this event.' }}
                                        </p>
                                    </div>

                                    <button
                                        v-if="isExpanded(event.id) || (event.description && event.description.length > 70)"
                                        @click.stop="toggleExpansion(event.id)"
                                        class="mt-3 flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 transition-colors hover:text-emerald-700 dark:hover:text-emerald-300">

                                        <span>{{ isExpanded(event.id) ? 'Collapse Details' : 'View Full Details'
                                            }}</span>

                                        <svg class="w-3 h-3 transition-transform duration-300"
                                            :class="{ 'rotate-180': isExpanded(event.id) }" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="h-full flex flex-col items-center justify-center text-center p-8 opacity-60">
                            <div
                                class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <p class="text-lg font-medium text-slate-900 dark:text-white">No events</p>
                            <p class="text-sm text-slate-500">Go enjoy your free time!</p>
                        </div>
                    </div>
                </div>
            </Transition>

        </main>
    </div>
</template>


<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue'

import IconButton from './IconButton.vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@/Icons'
import { useCalendarUrlState } from '@/Composables/useCalendarUrlState'
import { useDateFormatter } from '@/Composables/useDateFormatter'

const emit = defineEmits(['day-click'])

const props = defineProps({
    initialDate: {
        type: [String, Date],
        default: () => new Date(),
    },
    events: {
        type: Array,
        default: () => [],
    },
    transitionPreset: {
        type: String,
        default: 'fade', // slide | fade| scale
    },
})

const { view: urlView, date: urlDate, updateQuery, hasCalendarState } = useCalendarUrlState()

const currentDate = ref(normalizeToDate(props.initialDate))
const rootEl = ref(null)
const transitionDirection = ref(null)
const isAnimating = ref(false)
const touchStartX = ref(0)
const touchMoveX = ref(0)
const touchStartY = ref(0)
const isHorizontalSwipe = ref(false)
const swipeThreshold = 50
const windowWidth = ref(window.innerWidth)

const calendarContainer = ref(null)
const viewMode = ref('month')
const applyingFromUrl = ref(false)

const expandedEventIds = ref([]);

watch([hasCalendarState, urlView, urlDate], () => {
    // If URL has state, it should win (back/forward, shared link, reload)
    if (hasCalendarState.value) setFromUrl()
}, { immediate: true })

const monthKey = computed(() => {
    const d = currentDate.value
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
})

const yearKey = computed(() => currentDate.value.getFullYear())

const dayKey = computed(() => toLocalYmd(currentDate.value))

const transitionName = computed(() => {
    if (props.transitionPreset === 'fade') return 'cal-fade'
    if (props.transitionPreset === 'scale') return 'cal-scale'

    return transitionDirection.value === 'left'
        ? 'cal-slide-left'
        : 'cal-slide-right'
})

const monthLabel = computed(() =>
    currentDate.value.toLocaleString(undefined, { month: 'long' })
)

const yearLabel = computed(() => currentDate.value.getFullYear())

const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

const days = computed(() => buildMonthGrid(currentDate.value, props.events))

const yearMonths = computed(() => {
    const labels = [
        'January', 'February', 'March', 'April',
        'May', 'June', 'July', 'August',
        'September', 'October', 'November', 'December',
    ]
    return labels.map((label, index) => ({
        label,
        index,
    }))
})

const selectedDayLabel = computed(() => {
    const d = currentDate.value
    return d.toLocaleDateString(undefined, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    })
})

const selectedDayEvents = computed(() => {
    if (viewMode.value !== 'day') return []
    const key = toLocalYmd(currentDate.value)

    return props.events.filter((event) => {
        const d = normalizeToDate(event.start)
        return d && toLocalYmd(d) === key
    })
})


function toLocalYmd(d) {
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${day}`
}

function parseLocalYmd(s) {
    // expects YYYY-MM-DD
    const [y, m, d] = s.split('-').map(Number)
    return new Date(y, (m || 1) - 1, d || 1)
}
function setFromUrl() {
    if (!hasCalendarState.value) return

    applyingFromUrl.value = true

    const nextView = urlView.value ?? "month"
    const nextDate = normalizeToDate(urlDate.value ?? new Date())

    // Only assign if actually different (prevents loops / extra renders)
    if (viewMode.value !== nextView) viewMode.value = nextView

    const curIso = toLocalYmd(currentDate.value)
    const nextIso = toLocalYmd(nextDate)
    if (curIso !== nextIso) currentDate.value = nextDate

    applyingFromUrl.value = false
}

function syncUrl({ replace = true } = {}) {
    if (applyingFromUrl.value) return

    const iso = toLocalYmd(currentDate.value)

    let normalized = iso
    if (viewMode.value === "month") normalized = iso.slice(0, 7) + "-01"
    if (viewMode.value === "year") normalized = iso.slice(0, 4) + "-01-01"

    updateQuery({ view: viewMode.value, date: normalized }, { replace })
}



// keep in sync when URL changes (back/forward, shared links, etc.)

const toggleExpansion = (eventId) => {
    const index = expandedEventIds.value.indexOf(eventId);
    if (index > -1) {
        // ID found, collapse it
        expandedEventIds.value.splice(index, 1);
    } else {
        // ID not found, expand it
        expandedEventIds.value.push(eventId);
    }
};

const isExpanded = (eventId) => {
    return expandedEventIds.value.includes(eventId);
};

// Single source of truth for color mappings
const COLOR_MAP = {
    emerald: 'emerald',
    amber: 'amber',
    blue: 'blue',
    red: 'red',
    stone: 'stone',
    yellow: 'yellow',
    slate: 'slate',
    teal: 'teal',
    default: 'default',
};

const eventChipClasses = (event) => {
    const color = COLOR_MAP[event?.color] || 'emerald';

    // All classes explicitly written for Tailwind compiler
    const colors = {
        emerald: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200 border-emerald-500',
        amber: 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 border-amber-500',
        blue: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 border-blue-500',
        red: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 border-red-500',
        stone: 'bg-stone-100 dark:bg-stone-900/30 text-stone-800 dark:text-stone-200 border-stone-500',
        yellow: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 border-yellow-500',
        slate: 'bg-slate-100 dark:bg-slate-900/30 text-slate-800 dark:text-slate-200 border-slate-500',
        teal: 'bg-teal-100 dark:bg-teal-900/30 text-teal-800 dark:text-teal-200 border-teal-500',
        default: 'bg-black/30 dark:bg-white/30 text-black dark:text-white border-black dark:border-white ',
    };

    return colors[color] || colors.emerald;
};

const eventCardColors = (event, type) => {
    const color = COLOR_MAP[event?.color] || 'emerald';

    const colorsByType = {
        hover: {
            emerald: 'hover:border-emerald-300 dark:hover:border-emerald-600',
            amber: 'hover:border-amber-300 dark:hover:border-amber-600',
            blue: 'hover:border-blue-300 dark:hover:border-blue-600',
            red: 'hover:border-red-300 dark:hover:border-red-600',
            stone: 'hover:border-stone-300 dark:hover:border-stone-600',
            yellow: 'hover:border-yellow-300 dark:hover:border-yellow-600',
            slate: 'hover:border-slate-300 dark:hover:border-slate-600',
            teal: 'hover:border-teal-300 dark:hover:border-teal-600',
            default: 'hover:border-black dark:hover:border-white',
        },
        border: {
            emerald: 'border-emerald-300 dark:border-emerald-600',
            amber: 'border-amber-300 dark:border-amber-600',
            blue: 'border-blue-300 dark:border-blue-600',
            red: 'border-red-300 dark:border-red-600',
            stone: 'border-stone-300 dark:border-stone-600',
            yellow: 'border-yellow-300 dark:border-yellow-600',
            slate: 'border-slate-300 dark:border-slate-600',
            teal: 'border-teal-300 dark:border-teal-600',
            default: 'border-black dark:border-white',
        },
        text: {
            emerald: 'text-emerald-600 dark:text-emerald-400',
            amber: 'text-amber-600 dark:text-amber-400',
            blue: 'text-blue-600 dark:text-blue-400',
            red: 'text-red-600 dark:text-red-400',
            stone: 'text-stone-600 dark:text-stone-400',
            yellow: 'text-yellow-600 dark:text-yellow-400',
            slate: 'text-slate-600 dark:text-slate-400',
            teal: 'text-teal-600 dark:text-teal-400',
            default: 'text-black dark:text-white',
        },
    };

    return colorsByType[type]?.[color] || colorsByType[type]?.emerald || '';
};

const eventBulletColors = (event) => {
    const color = COLOR_MAP[event?.color] || 'emerald';

    const colors = {
        emerald: 'bg-emerald-500',
        amber: 'bg-amber-600',
        blue: 'bg-blue-500',
        red: 'bg-red-700',
        stone: 'bg-stone-500',
        yellow: 'bg-yellow-400',
        slate: 'bg-slate-500',
        teal: 'bg-teal-700',
        default: 'bg-black dark:bg-white',
    };

    return colors[color] || colors.emerald;
};



function setViewMode(mode) {
    viewMode.value = mode
    syncUrl({ replace: false }) // clicking tab = push history entry 
}

function goToToday() {
    currentDate.value = new Date()
    viewMode.value = 'month'
    syncUrl({ replace: false })
}


function prevStep() {
    if (isAnimating.value) return

    transitionDirection.value = 'right'

    if (viewMode.value === 'year') {
        const d = new Date(currentDate.value)
        d.setFullYear(d.getFullYear() - 1)
        currentDate.value = d
        syncUrl({ replace: true })
        return
    }

    if (viewMode.value === 'day') {
        const d = new Date(currentDate.value)
        d.setDate(d.getDate() - 1)
        currentDate.value = d
        syncUrl({ replace: true })
        return
    }

    changeMonth('prev')
}

function nextStep() {
    if (isAnimating.value) return

    transitionDirection.value = 'left'

    if (viewMode.value === 'year') {
        const d = new Date(currentDate.value)
        d.setFullYear(d.getFullYear() + 1)
        currentDate.value = d
        syncUrl({ replace: true })
        return
    }

    if (viewMode.value === 'day') {
        const d = new Date(currentDate.value)
        d.setDate(d.getDate() + 1)
        currentDate.value = d
        syncUrl({ replace: true })
        return
    }

    changeMonth('next')
}


function selectMonthFromYear(monthIndex) {
    const d = new Date(currentDate.value)
    d.setMonth(monthIndex)
    d.setDate(1)
    currentDate.value = d
    viewMode.value = 'month'
    syncUrl({ replace: false })
}

function getMaxEvents() {
    return windowWidth.value < 640 ? 2 : 3
}

const TRANSITION_DURATION = 50

function changeMonth(direction) {
    if (isAnimating.value) return

    transitionDirection.value = direction === 'next' ? 'left' : 'right'

    const d = new Date(currentDate.value)
    if (direction === 'next') {
        d.setMonth(d.getMonth() + 1)
    } else {
        d.setMonth(d.getMonth() - 1)
    }

    currentDate.value = d
    syncUrl({ replace: true })
}

function handleDayClick(date, event) {
    if (isHorizontalSwipe.value && Math.abs(touchMoveX.value - touchStartX.value) > 10) return

    const button = event.currentTarget
    const rect = button.getBoundingClientRect()
    const ripple = document.createElement('span')

    const size = Math.max(rect.width, rect.height)
    const x = event.clientX - rect.left - size / 2
    const y = event.clientY - rect.top - size / 2

    ripple.style.width = ripple.style.height = `${size}px`
    ripple.style.left = `${x}px`
    ripple.style.top = `${y}px`
    ripple.style.zIndex = 20
    ripple.classList.add('ripple')

    button.querySelectorAll('.ripple').forEach((r) => r.remove())
    button.appendChild(ripple)

    setTimeout(() => ripple.remove(), 600)

    currentDate.value = new Date(date)
    viewMode.value = 'day'
    syncUrl({ replace: false })

    emit('day-click', date)
}

let lastScrollTime = 0

function handleWheel(e) {
    if (!rootEl.value || !rootEl.value.contains(e.target)) return
    if (isAnimating.value) return

    const now = Date.now()
    if (now - lastScrollTime < TRANSITION_DURATION + 100) return

    const { deltaX, deltaY } = e
    const useHorizontal = Math.abs(deltaX) > Math.abs(deltaY)
    const delta = useHorizontal ? deltaX : deltaY

    if (Math.abs(delta) < 20) return

    e.preventDefault()
    lastScrollTime = now

    if (delta > 0) {
        nextStep()
    } else {
        prevStep()
    }
}

function handleTouchStart(e) {
    if (isAnimating.value) return

    const t = e.touches[0]
    touchStartX.value = t.clientX
    touchStartY.value = t.clientY
    touchMoveX.value = t.clientX
    isHorizontalSwipe.value = false
}

function handleTouchMove(e) {
    if (isAnimating.value) return

    const t = e.touches[0]
    const dx = t.clientX - touchStartX.value
    const dy = t.clientY - touchStartY.value

    if (!isHorizontalSwipe.value) {
        if (Math.abs(dx) > 10 && Math.abs(dx) > Math.abs(dy) * 1.3) {
            isHorizontalSwipe.value = true
        } else {
            return
        }
    }

    e.preventDefault()
    touchMoveX.value = t.clientX
}

function handleTouchEnd() {
    if (isAnimating.value || !isHorizontalSwipe.value) {
        resetTouch()
        return
    }

    const dx = touchMoveX.value - touchStartX.value
    const containerWidth = calendarContainer.value?.clientWidth || 0
    const threshold = Math.max(swipeThreshold, containerWidth * 0.15)

    if (Math.abs(dx) > threshold) {
        if (dx < 0) {
            nextStep()
        } else {
            prevStep()
        }
    }

    resetTouch()
}

function handleTouchCancel() {
    resetTouch()
}

function resetTouch() {
    touchStartX.value = 0
    touchStartY.value = 0
    touchMoveX.value = 0
    isHorizontalSwipe.value = false
}

function handleResize() {
    windowWidth.value = window.innerWidth
}

onMounted(() => {
    if (!hasCalendarState.value) {
        currentDate.value = normalizeToDate(props.initialDate)
        viewMode.value = "month"
        syncUrl({ replace: true }) // set the initial canonical URL once
    }
    if (rootEl.value) {
        rootEl.value.addEventListener('wheel', handleWheel, { passive: false })
        rootEl.value.addEventListener('touchstart', handleTouchStart, { passive: true })
        rootEl.value.addEventListener('touchmove', handleTouchMove, { passive: false })
        rootEl.value.addEventListener('touchend', handleTouchEnd)
        rootEl.value.addEventListener('touchcancel', handleTouchCancel)
    }

    window.addEventListener('resize', handleResize)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize)

    if (rootEl.value) {
        rootEl.value.removeEventListener('wheel', handleWheel)
        rootEl.value.removeEventListener('touchstart', handleTouchStart)
        rootEl.value.removeEventListener('touchmove', handleTouchMove)
        rootEl.value.removeEventListener('touchend', handleTouchEnd)
        rootEl.value.removeEventListener('touchcancel', handleTouchCancel)
    }
})

function normalizeToDate(val) {
    if (val instanceof Date) return new Date(val)

    if (typeof val === 'string') {
        // IMPORTANT: parse date-only strings as LOCAL, not UTC
        if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
            const d = parseLocalYmd(val)
            return Number.isNaN(d.getTime()) ? new Date() : d
        }

        const d = new Date(val)
        return Number.isNaN(d.getTime()) ? new Date() : d
    }

    return new Date()
}

function buildMonthGrid(activeDate, events) {
    const result = []

    const year = activeDate.getFullYear()
    const month = activeDate.getMonth()

    const firstOfMonth = new Date(year, month, 1)
    const firstDay = firstOfMonth.getDay() || 7
    const gridStart = new Date(firstOfMonth)
    gridStart.setDate(firstOfMonth.getDate() - (firstDay - 1))

    const today = new Date()
    stripTime(today)

    const eventsByDay = events.reduce((acc, event) => {
        const d = normalizeToDate(event.start)
        if (!d) return acc
        const dateKey = toLocalYmd(d)
            ; (acc[dateKey] ||= []).push({
                id: event.id,
                title: event.title,
                color: event.color || 'blue',
                start: event.start,
            })
        return acc
    }, {})


    for (let i = 0; i < 42; i++) {
        const date = new Date(gridStart)
        date.setDate(gridStart.getDate() + i)

        const dateKey = toLocalYmd(date)
        const isCurrentMonth = date.getMonth() === month
        const isToday = isSameDay(date, today)

        result.push({
            date,
            iso: dateKey,
            isCurrentMonth,
            isToday,
            events: eventsByDay[dateKey] || [],
        })
    }

    return result
}

function stripTime(d) {
    d.setHours(0, 0, 0, 0)
}

function isSameDay(a, b) {
    return (
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate()
    )
}

const {formatDate} = useDateFormatter()

function dayEventDateFormatter(val) {
    const d = normalizeToDate(val)
    return d.toLocaleTimeString(undefined, {
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>


<style scoped>
@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

:deep(.ripple) {
    position: absolute;
    border-radius: 50%;
    background-color: rgba(16, 185, 129, 0.6);
    transform: scale(0);
    animation: ripple-animation 600ms ease-out;
    pointer-events: none;
}

.day-cell {
    overflow: hidden;
}

/* Clean slide */
.cal-slide-left-enter-active,
.cal-slide-left-leave-active,
.cal-slide-right-enter-active,
.cal-slide-right-leave-active {
    transition: transform 50ms ease-out, opacity 50ms ease-out;
}

.cal-slide-left-enter-from {
    transform: translateX(12%);
    opacity: 0;
}

.cal-slide-left-enter-to {
    transform: translateX(0);
    opacity: 1;
}

.cal-slide-left-leave-from {
    transform: translateX(0);
    opacity: 1;
}

.cal-slide-left-leave-to {
    transform: translateX(-12%);
    opacity: 0.2;
}

.cal-slide-right-enter-from {
    transform: translateX(-12%);
    opacity: 0;
}

.cal-slide-right-enter-to {
    transform: translateX(0);
    opacity: 1;
}

.cal-slide-right-leave-from {
    transform: translateX(0);
    opacity: 1;
}

.cal-slide-right-leave-to {
    transform: translateX(12%);
    opacity: 0.2;
}

/* Crossfade */

.cal-fade-enter-active,
.cal-fade-leave-active {
    transition: opacity 100ms ease-out;
}

.cal-fade-enter-from,
.cal-fade-leave-to {
    opacity: 0;
}

.cal-fade-enter-to,
.cal-fade-leave-from {
    opacity: 1;
}

/* Scale */
.cal-scale-enter-active,
.cal-scale-leave-active {
    transition: transform 100ms ease-out, opacity 100ms ease-out;
}

.cal-scale-enter-from {
    transform: scale(0.97);
    opacity: 0;
}

.cal-scale-enter-to {
    transform: scale(1);
    opacity: 1;
}

.cal-scale-leave-from {
    transform: scale(1);
    opacity: 1;
}

.cal-scale-leave-to {
    transform: scale(1.03);
    opacity: 0;
}
</style>
