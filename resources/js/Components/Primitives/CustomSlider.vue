<script setup>
import { computed } from 'vue'
import Slider from 'primevue/slider'
import Button from 'primevue/button'

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: null, // we will treat null as "no value"
  },
  min: {
    type: Number,
    default: 0,
  },
  max: {
    type: Number,
    default: 10,
  },
  step: {
    type: Number,
    default: 1,
  },
  nullable: {
    type: Boolean,
    default: true,
  },
  id: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

/**
 * We keep a computed "internal" value for the slider thumb.
 * If the actual value is null, we still need a numeric position (use min).
 */
const internalValue = computed({
  get() {
    if (props.modelValue === null || props.modelValue === '') {
      return props.min
    }

    const n = Number(props.modelValue)
    return Number.isNaN(n) ? props.min : n
  },
  set(val) {
    const n = Number(val)
    const safe = Number.isNaN(n) ? null : n
    emit('update:modelValue', safe)
    emit('change', safe)
  },
})

const clear = () => {
  if (!props.nullable) return
  emit('update:modelValue', null)
  emit('change', null)
}
</script>

<template>
  <div class="flex items-center gap-3">
    <Slider
      :id="id"
      v-model="internalValue"
      :min="min"
      :max="max"
      :step="step"
      class="flex-1"
    />

    <span class="w-10 text-center text-sm text-gray-700 dark:text-gray-300">
      {{ modelValue ?? '—' }}
    </span>

    <Button
      v-if="nullable && modelValue !== null"
      type="button"
      icon="pi pi-times"
      class="p-button-text text-gray-400 hover:text-red-500"
      @click="clear"
    />
  </div>
</template>
