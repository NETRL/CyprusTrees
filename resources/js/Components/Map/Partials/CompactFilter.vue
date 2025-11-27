<!-- CompactFilter.vue -->
<template>
    <div class="relative py-1">
        <!-- Fade gradients to indicate scrollability -->
        <div 
            class="absolute left-0 top-0 bottom-0 w-8 bg-linear-to-r rounded-lg from-white dark:from-gray-900/90 to-transparent pointer-events-none z-10 transition-opacity duration-300"
            :class="{ 'opacity-0': !showLeftGradient }"
        ></div>
        <div 
            class="absolute right-0 top-0 bottom-0 w-8 bg-linear-to-l rounded-lg from-white dark:from-gray-900/90 to-transparent pointer-events-none z-10 transition-opacity duration-300"
            :class="{ 'opacity-0': !showRightGradient }"
        ></div>
        
        <div 
            ref="scrollContainer"
            @scroll="handleScroll"
            class="flex gap-2 overflow-x-auto scrollbar-hide scroll-smooth"
        >
            <button
                v-for="option in options"
                :key="option.value"
                :ref="el => buttonRefs[option.value] = el"
                @click="selectOption(option.value)"
                type="button"
                class="flex items-center gap-2 px-3 py-2 rounded-full border whitespace-nowrap transition-all duration-200 shrink-0"
                :class="[
                    option.value === modelValue
                        ? 'border-brand-700 bg-brand-700 dark:border-brand-800 dark:bg-brand-800 text-white font-medium shadow-lg'
                        : 'border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
                ]"
            >
                <component :is="option.icon" class="w-4 h-4 shrink-0" />
                <span class="text-xs sm:text-sm">{{ option.label }}</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'

const props = defineProps({
    modelValue: String,
    options: Array // Each option should have: { label, value, icon }
})

const emit = defineEmits(['update:modelValue'])

const scrollContainer = ref(null)
const buttonRefs = ref({})
const showLeftGradient = ref(false)
const showRightGradient = ref(false)

const handleScroll = () => {
    if (!scrollContainer.value) return
    
    const { scrollLeft, scrollWidth, clientWidth } = scrollContainer.value
    
    showLeftGradient.value = scrollLeft > 10
    showRightGradient.value = scrollLeft < scrollWidth - clientWidth - 10
}

const scrollToCenter = (value) => {
    nextTick(() => {
        const button = buttonRefs.value[value]
        const container = scrollContainer.value
        
        if (!button || !container) return
        
        const buttonRect = button.getBoundingClientRect()
        const containerRect = container.getBoundingClientRect()
        
        const buttonCenter = button.offsetLeft + (buttonRect.width / 2)
        const containerCenter = container.scrollLeft + (containerRect.width / 2)
        
        const scrollTo = buttonCenter - (containerRect.width / 2)
        
        container.scrollTo({
            left: Math.max(0, scrollTo),
            behavior: 'smooth'
        })
    })
}

const selectOption = (value) => {
    emit('update:modelValue', value)
    scrollToCenter(value)
}

const update = (value) => {
    emit('update:modelValue', value)
}

// Initialize scroll indicators on mount
onMounted(() => {
    if (scrollContainer.value) {
        handleScroll()
        // Scroll to selected item on mount
        if (props.modelValue) {
            scrollToCenter(props.modelValue)
        }
    }
})

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        scrollToCenter(newValue)
    }
})
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>