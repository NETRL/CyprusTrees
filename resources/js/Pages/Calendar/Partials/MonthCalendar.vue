<template>
    <div ref="rootEl" class="h-full flex flex-col p-2 sm:p-4">
        <div class="flex items-center justify-between gap-3 mb-4 px-1">
            <div class="flex items-center gap-2">
                <button type="button" class="inline-flex items-center justify-center rounded-lg px-3 py-2
                 border border-slate-200 dark:border-slate-700
                 bg-white dark:bg-slate-800
                 text-slate-700 dark:text-slate-100
                 text-xs sm:text-sm font-medium
                 hover:bg-slate-50 dark:hover:bg-slate-750
                 active:scale-95
                 transition-all duration-150
                 shadow-sm hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                    @click="goToToday">
                    Today
                </button>

                <div class="flex items-center gap-0.5 bg-slate-100 dark:bg-slate-800 rounded-xl p-0.5 shadow-inner">
                    <IconButton @click="prevMonth" aria-label="Previous month" class="text-lg"
                        :disabled="isAnimating ">
                        ‹
                    </IconButton>
                    <IconButton @click="nextMonth" aria-label="Next month" class="text-lg"
                        :disabled="isAnimating ">
                        ›
                    </IconButton>
                </div>
            </div>

            <div class="text-right">
                <p class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-slate-50">
                    {{ monthLabel }}
                </p>
                <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">
                    {{ yearLabel }}
                </p>
            </div>
        </div>
        <div class="grid grid-cols-7 text-xs sm:text-sm font-bold uppercase tracking-wider
             text-slate-600 dark:text-slate-400 mb-2 px-1 border-b border-slate-200 dark:border-slate-700 pb-1">
            <div v-for="dow in weekdays" :key="dow" class="text-center py-1">
                {{ dow }}
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-hidden relative">
            <div ref="calendarContainer" class="absolute inset-0 transition-transform duration-500 ease-out" :class="{
                'translate-x-full opacity-0 scale-[0.9] skew-y-1': isAnimating && transitionDirection === 'right',
                '-translate-x-full opacity-0 scale-[0.9] -skew-y-1': isAnimating && transitionDirection === 'left',
                'pointer-events-none': isAnimating,
            }">
                <div class="h-full grid grid-cols-7 grid-rows-6 gap-1
                     rounded-lg overflow-hidden
                     bg-slate-100/50 dark:bg-slate-800/30 p-1">
                    <button v-for="day in days" :key="day.iso" type="button" class="day-cell relative flex flex-col items-start justify-start
                        px-1.5 sm:px-3 py-1.5 sm:py-3
                        text-left text-xs sm:text-sm
                        rounded-lg
                        transition-all duration-150
                        focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2
                        hover:bg-slate-50 dark:hover:bg-slate-800/80
                        active:scale-[0.98] active:shadow-inner
                        bg-white dark:bg-slate-900/80
                        min-h-[60px] sm:min-h-[70px] overflow-hidden" :class="[
                            !day.isCurrentMonth
                                ? 'text-slate-300 dark:text-slate-600 opacity-60'
                                : 'text-slate-800 dark:text-slate-100',
                            day.isToday && day.isCurrentMonth
                                ? 'ring-2 ring-emerald-500 shadow-xl shadow-emerald-500/20'
                                : 'shadow-md',
                        ]" @click="handleDayClick(day.date, $event)">
                        <div class="absolute inset-0 p-3 flex flex-col transition-opacity duration-150"
                            :class="{ 'opacity-0': isAnimating }">
                            <div class="flex items-center justify-between w-full mb-0.5 sm:mb-1">
                                <span class="inline-flex items-center justify-center min-w-6 h-6 sm:min-w-7 sm:h-7 rounded-full
                                 text-[0.7rem] sm:text-sm font-semibold
                                 transition-colors duration-150 z-10" :class="day.isToday
                                    ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-md'
                                    : 'bg-transparent hover:bg-slate-100 dark:hover:bg-slate-800'
                                    ">
                                    {{ day.date.getDate() }}
                                </span>
                            </div>

                            <div class="flex-1 w-full space-y-0.5 sm:space-y-1 overflow-hidden z-10">
                                <div v-for="(event, idx) in day.events.slice(0, getMaxEvents())" :key="event.id" class="flex items-center gap-1 px-1 py-0.5 rounded
                                 bg-opacity-80 transition-all duration-150" :class="[
                                    day.isCurrentMonth ? 'bg-slate-100 dark:bg-slate-700/50' : 'bg-transparent'
                                ]" :style="`animation-delay: ${idx * 50}ms`">
                                    <div class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full shrink-0 shadow-sm"
                                        :class="event.color"></div>
                                    <p class="text-[0.55rem] sm:text-xs font-medium truncate leading-tight" :class="!day.isCurrentMonth
                                        ? 'text-slate-400 dark:text-slate-600'
                                        : 'text-slate-700 dark:text-slate-300'
                                        ">
                                        {{ event.title }}
                                    </p>
                                </div>
                                <template v-if="day.events.length > getMaxEvents()">
                                    <p
                                        class="text-[0.55rem] sm:text-xs font-medium text-emerald-600 dark:text-emerald-400 px-1 pt-0.5">
                                        +{{ day.events.length - getMaxEvents() }} more
                                    </p>
                                </template>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
    nextTick,
} from 'vue'

import IconButton from '@/Pages/Calendar/Partials/IconButton.vue'

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
})

// ... (other refs remain the same)
const currentDate = ref(normalizeToDate(props.initialDate))
const rootEl = ref(null)
const transitionDirection = ref(null)
const isAnimating = ref(false)
const touchStartX = ref(0)
const touchMoveX = ref(0)
const swipeThreshold = 50
const windowWidth = ref(window.innerWidth)
const touchStartY = ref(0)
const isHorizontalSwipe = ref(false)


const calendarContainer = ref(null) // New ref for the calendar grid container

watch(
    () => props.initialDate,
    (val) => {
        if (val) currentDate.value = normalizeToDate(val)
    }
)

watch(
    () => props.events,
    () => {
        // Force re-calculation of days when events change (already done by computed)
    }
)

const monthLabel = computed(() =>
    currentDate.value.toLocaleString(undefined, { month: 'long' })
)

const yearLabel = computed(() => currentDate.value.getFullYear())

const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

const days = computed(() => buildMonthGrid(currentDate.value, props.events))

function goToToday() {
    currentDate.value = new Date()
}

function prevMonth() {
    if (isAnimating.value) return
    changeMonth('prev')
}

function nextMonth() {
    if (isAnimating.value) return
    changeMonth('next')
}
function getMaxEvents() {
    // Show 2 events on small screens, 3 on larger
    return windowWidth.value < 640 ? 2 : 3
}

const TRANSITION_DURATION = 100

function changeMonth(direction) {
    if (isAnimating.value) return

    isAnimating.value = true
    transitionDirection.value = direction === 'next' ? 'left' : 'right'

    nextTick(() => {
        const d = new Date(currentDate.value)
        if (direction === 'next') {
            d.setMonth(d.getMonth() + 1)
        } else {
            d.setMonth(d.getMonth() - 1)
        }

        setTimeout(() => {
            currentDate.value = d
            isAnimating.value = false
            transitionDirection.value = null
        }, TRANSITION_DURATION)
    })
}


function handleDayClick(date, event) {
    // Prevent day click if a drag/swipe just occurred
    if (isHorizontalSwipe.value && Math.abs(touchMoveX.value - touchStartX.value) > 10) return

    const button = event.currentTarget
    const rect = button.getBoundingClientRect()
    const ripple = document.createElement('span')

    // Use the maximum dimension for a perfectly round ripple covering the button
    const size = Math.max(rect.width, rect.height)

    // Calculate ripple position relative to the button (centered on the click point)
    const x = event.clientX - rect.left - size / 2
    const y = event.clientY - rect.top - size / 2

    ripple.style.width = ripple.style.height = `${size}px`
    ripple.style.left = `${x}px`
    ripple.style.top = `${y}px`
    // Ensure the ripple is above day content
    ripple.style.zIndex = 20;

    ripple.classList.add('ripple')

    // Remove previous ripples to prevent stacking if clicked too quickly
    button.querySelectorAll('.ripple').forEach(r => r.remove());

    button.appendChild(ripple)

    // Remove the ripple after animation completion (600ms defined in CSS)
    setTimeout(() => {
        ripple.remove()
    }, 600)

    // Emit the day-click event
    emit('day-click', date)
}


let lastScrollTime = 0

function handleWheel(e) {
    if (!rootEl.value || !rootEl.value.contains(e.target)) return

    const now = Date.now()
    if (now - lastScrollTime < TRANSITION_DURATION + 100) return
    if (isAnimating.value) return

    const absX = Math.abs(e.deltaY)
    const absY = Math.abs(e.deltaY)

    // Only react to clear horizontal gestures (trackpad swipe)
    if (absX < 20 || absX < absY) return

    e.preventDefault()
    lastScrollTime = now

    if (e.deltaY > 0) {
        nextMonth()
    } else {
        prevMonth()
    }
}

// --- TOUCH/DRAG HANDLERS ---
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

    // Not yet decided → check if this is a horizontal swipe
    if (!isHorizontalSwipe.value) {
        if (Math.abs(dx) > 10 && Math.abs(dx) > Math.abs(dy) * 1.3) {
            // Clear horizontal intent
            isHorizontalSwipe.value = true
        } else {
            // Vertical / small movement → allow normal scroll
            return
        }
    }

    // Once horizontal swipe is confirmed, block page scroll
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
    const threshold = Math.max(swipeThreshold, containerWidth * 0.15) // ~15% width or 50px

    if (Math.abs(dx) > threshold) {
        if (dx < 0) {
            // swipe left → next month
            nextMonth()
        } else {
            // swipe right → prev month
            prevMonth()
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

onMounted(() => {
    if (rootEl.value) {
        rootEl.value.addEventListener('wheel', handleWheel, { passive: false })
        rootEl.value.addEventListener('touchstart', handleTouchStart, { passive: true })
        rootEl.value.addEventListener('touchmove', handleTouchMove, { passive: false })
        rootEl.value.addEventListener('touchend', handleTouchEnd)
        rootEl.value.addEventListener('touchcancel', handleTouchCancel)
    }

    const handleResize = () => {
        windowWidth.value = window.innerWidth
    }
    window.addEventListener('resize', handleResize)

    onBeforeUnmount(() => {
        window.removeEventListener('resize', handleResize)
    })
})

onBeforeUnmount(() => {
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
        const d = new Date(val)
        if (!Number.isNaN(d.getTime())) return d
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
        const dateKey = normalizeToDate(event.start).toISOString().slice(0, 10)

        if (!acc[dateKey]) {
            acc[dateKey] = []
        }
        acc[dateKey].push({
            id: event.id,
            title: event.title,
            color: event.color || 'bg-blue-500',
        })
        return acc
    }, {})

    for (let i = 0; i < 42; i++) {
        const date = new Date(gridStart)
        date.setDate(gridStart.getDate() + i)

        const dateKey = date.toISOString().slice(0, 10)

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
</style>
