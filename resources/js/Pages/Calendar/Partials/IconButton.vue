<template>
    <button type="button" :aria-label="ariaLabel" class="inline-flex items-center justify-center
            w-8 h-8 text-sm sm:text-base
            border border-slate-200 dark:border-slate-700
            bg-slate-50 dark:bg-slate-800
            text-slate-700 dark:text-slate-200
            hover:bg-slate-100 dark:hover:bg-slate-700
            transition-colors" @click="$emit('click')">
        <slot />
    </button>
</template>

<script setup>

defineEmits(['click'])

defineProps({
    ariaLabel: String,
})



</script>