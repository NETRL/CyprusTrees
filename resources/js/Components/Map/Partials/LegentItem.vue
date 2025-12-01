<template>
  <div
    class="flex items-center gap-3 py-1.5 px-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer select-none group"
    @click.stop="emitToggle">
    <div class="w-3.5 h-3.5 rounded-full shrink-0 shadow-sm " :style="{
      backgroundColor: color,
      border: isWhite ? '2px solid #d1d5db' : 'none'
    }"></div>
    <div class="flex justify-between items-center w-full group">
      <span class="text-sm text-gray-700 dark:text-gray-300 leading-tight">
        {{ label }}
      </span>
      <div class="flex space-x-3">
        <span v-if="count != null" class="text-xs text-gray-500 dark:text-gray-400">
          {{ count }}
        </span>
        <span
          class=" text-xs text-gray-500 dark:text-gray-400 opacity-20 group-hover:opacity-100! transition-opacity ease-in duration-300">
          <i :class="['pi', icon]" />
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  color: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  count: {
    type: Number,
    default: null
  },
  icon: {
    type: String,
    default: 'pi-eye'
  }
});

const isVisible = ref(true);

const emit = defineEmits(['toggle']);

const emitToggle = () => {
  isVisible.value = !isVisible.value
  emit('toggle');
}

// Helper to make white dots visible
const isWhite = computed(() => props.color === '#ffffff');
</script>
