<template>
  <div class="flex items-center gap-3 py-1.5 px-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-default">
    <div 
      class="w-3.5 h-3.5 rounded-full shrink-0 shadow-sm" 
      :style="{ 
        backgroundColor: color, 
        border: isWhite ? '2px solid #d1d5db' : 'none' 
      }"
    ></div>
    <span class="text-sm text-gray-700 dark:text-gray-300 leading-tight select-none">
      {{ label }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  color: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  }
});

// Helper to make white dots visible
const isWhite = computed(() => props.color === '#ffffff');
</script>
