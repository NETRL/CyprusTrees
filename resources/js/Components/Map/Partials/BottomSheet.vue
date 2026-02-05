<template>
    <!-- Mobile Bottom Sheet wrapper -->
    <div class="lg:hidden fixed bottom-0 z-50">
        <!-- Backdrop -->
        <div v-if="currentState !== 'closed' && showBackdrop"
            class="fixed inset-0 bg-black/50 z-40 transition-opacity duration-300" @click="goClosed"></div>

        <!-- Bottom Sheet -->
        <div :class="[
            'fixed left-0 right-0 bottom-0 bg-white dark:bg-gray-900 dark:border-gray-800 dark:text-white text-gray-900',
            !isDragging ? 'transition-all duration-300 ease-in-out' : '',
            'z-50 border-t border-gray-200 rounded-t-2xl shadow-2xl flex flex-col overflow-hidden',
        ]" :style="{ height: `${currentHeight}px` }">
            <!-- Handle Bar -->
            <button
                class="w-full p-4 flex flex-col items-center gap-2 touch-manipulation select-none shrink-0 border-b border-gray-700/50"
                @pointerdown="handlePointerDown">
                <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full"></div>
                <slot name="header"></slot>
            </button>

            <!-- Scrollable content -->
            <div class="pt-4 flex-1 overflow-y-auto select-none flex justify-center">
                <slot />
            </div>
        </div>

        <!-- FAB Toggle Button -->
        <button v-if="showFab && currentState === 'closed'" @click="goMid"
            class="fixed right-4 bottom-20 z-30 grid h-11 w-11 place-items-center rounded-xl bg-white/90 shadow ring-1 ring-slate-200 backdrop-blur dark:bg-slate-900/90 dark:ring-slate-700 transition-colors group">
            <i :class="[' group-hover:rotate-25 transition-all duration-300 ease-in-out', fabIcon]"></i>
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'

const props = defineProps({
    /** v-model:state: 'open' | 'mid' | 'closed' */
    state: {
        type: String,
        default: 'closed',
        validator: (v) => ['open', 'mid', 'closed'].includes(v),
    },
    /** fab icon class, e.g. 'pi pi-map' */
    fabIcon: {
        type: String,
        default: 'pi pi-map',
    },
    /** bottom sheet height as fraction of viewport height (0–1) */
    heightRatio: {
        type: Number,
        default: 0.8,
    },
    /** whether to show the FAB button */
    showFab: {
        type: Boolean,
        default: true,
    },
    /** whether to show backdrop */
    showBackdrop: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['update:state'])

// Drag state
const isDragging = ref(false)
const startY = ref(0)
const currentY = ref(0)
const baseHeight = ref(0)

// Heights
const openHeight = ref(0)
const midHeight = ref(0)
const closedHeight = ref(0)

const currentState = ref(props.state) // 'open' | 'mid' | 'closed'
const currentHeight = ref(0)

onMounted(() => {
    const vh = window.innerHeight
    const fullSheet = vh * props.heightRatio // e.g. 0.8 * vh

    openHeight.value = fullSheet
    midHeight.value = fullSheet * 0.5
    closedHeight.value = 0

    updateHeightForState()
})

onBeforeUnmount(() => {
    window.removeEventListener('pointermove', handlePointerMove)
    window.removeEventListener('pointerup', handlePointerUp)
})

/**
 * Sync internal state -> parent
 */
watch(currentState, (val) => {
    emit('update:state', val)
    updateHeightForState()
})

/**
 * Sync parent prop -> internal state
 * (allows v-model:state to control the sheet)
 */
watch(
    () => props.state,
    (val) => {
        if (val !== currentState.value) {
            setState(val)
        }
    }
)

const updateHeightForState = () => {
    if (currentState.value === 'open') {
        currentHeight.value = openHeight.value
    } else if (currentState.value === 'mid') {
        currentHeight.value = midHeight.value
    } else {
        currentHeight.value = closedHeight.value
    }
}

const setState = async (nextState) => {
    const prevState = currentState.value
    const openingFromClosed =
        !isDragging.value && prevState === 'closed' && nextState !== 'closed'

    currentState.value = nextState

    // If simply switching between Mid/Open, update immediately
    if (!openingFromClosed) {
        updateHeightForState()
        return
    }

    currentHeight.value = closedHeight.value

    // Wait for Vue to update the DOM with height
    await nextTick()
    // Use double requestAnimationFrame to ensure the browser paints the 0px frame
    // before applying the new height. This forces the CSS transition to trigger.
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            updateHeightForState()
        })
    })
}

const goOpen = () => setState('open')
const goMid = () => setState('mid')
const goClosed = () => setState('closed')

const handlePointerDown = (event) => {
    if (event.pointerType === 'mouse' && event.button !== 0) return

    isDragging.value = true
    startY.value = event.clientY
    currentY.value = event.clientY
    // remember height at drag start
    baseHeight.value = currentHeight.value

    window.addEventListener('pointermove', handlePointerMove, { passive: false })
    window.addEventListener('pointerup', handlePointerUp)
}

const handlePointerMove = (event) => {
    if (!isDragging.value) return

    event.preventDefault()
    currentY.value = event.clientY

    const deltaY = currentY.value - startY.value
    let newHeight = baseHeight.value - deltaY

    // clamp between closed and open
    newHeight = Math.max(closedHeight.value, Math.min(openHeight.value, newHeight))

    currentHeight.value = newHeight
}

const handlePointerUp = () => {
    if (!isDragging.value) return

    isDragging.value = false
    window.removeEventListener('pointermove', handlePointerMove)
    window.removeEventListener('pointerup', handlePointerUp)

    const deltaY = currentY.value - startY.value
    const smallThreshold = 40   // minimal swipe to change state
    const strongThreshold = 160  // “hard” swipe

    // Swipe DOWN (closing)
    if (deltaY > smallThreshold) {
        if (currentState.value === 'open') {
            // From open: hard swipe → closed, soft swipe → mid
            if (deltaY > strongThreshold) {
                goClosed()
            } else {
                goMid()
            }
        } else if (currentState.value === 'mid') {
            // From mid: any swipe down → closed
            goClosed()
        } else {
            // already closed → just snap back to closed height
            goClosed()
        }
        return
    }

    // Swipe UP (opening)
    if (deltaY < -smallThreshold) {
        const absDelta = Math.abs(deltaY)

        if (currentState.value === 'closed') {
            // From closed: hard swipe up → open, soft → mid
            if (absDelta > strongThreshold) {
                goOpen()
            } else {
                goMid()
            }
        } else if (currentState.value === 'mid') {
            // From mid: swipe up → open
            goOpen()
        } else {
            // already open → just snap back to open
            goOpen()
        }
        return
    }

    // Very small drag → snap to nearest height
    snapToNearestHeight()
}

const snapToNearestHeight = () => {
    const target = currentHeight.value
    const distances = [
        { state: 'open', dist: Math.abs(target - openHeight.value) },
        { state: 'mid', dist: Math.abs(target - midHeight.value) },
        { state: 'closed', dist: Math.abs(target - closedHeight.value) },
    ].sort((a, b) => a.dist - b.dist)

    if (distances[0].state === 'open') goOpen()
    else if (distances[0].state === 'mid') goMid()
    else goClosed()
}
</script>
