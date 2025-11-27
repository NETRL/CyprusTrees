<template>

        <div class="inline-flex rounded-full border border-white/40 bg-black/30 overflow-hidden min-h-9">
            <button v-for="option in options" :key="option.value" @click="update(option.value)" type="button"
                class="px-4 py-1.5 w-full text-xs sm:text-sm font-medium transition-colors duration-150 text-nowrap" :class="[
                    option.value === modelValue
                        ? 'bg-white text-black'
                        : 'text-white/70 hover:bg-white/10',
                    option !== options[0] ? 'border-l border-white/20' : ''
                ]">
                {{ option.label }}
            </button>
        </div>
</template>

<script setup>
const props = defineProps({
    modelValue: String,
    options: Array
})
const emit = defineEmits(['update:modelValue'])
const update = (value) => emit('update:modelValue', value)
</script>
