<template>
  <div :class="[
    'rounded-2xl border border-gray-200 dark:border-gray-800 flex flex-col',
    className,
   transparent ? 'bg-transparent!' : 'bg-white dark:bg-white/3'
  ]">
    <div v-if="title || desc" class="px-6 pt-5">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
        {{ title }}
      </h3>
      <p v-if="desc" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ desc }}
      </p>
    </div>
    <div class="p-4 border-gray-100 dark:border-gray-800 sm:p-6 flex-1 flex flex-col">
      <div class="space-y-5 flex-1 flex flex-col">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  className: {
    type: String,
    default: '',
  },
  desc: {
    type: String,
    default: '',
  },
  transparent: {
    type: Boolean,
    default: false,
  },
})
</script>

