<template>
  <div class="group relative overflow-hidden rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200 transition-all hover:shadow-md dark:bg-gray-800 dark:ring-gray-700">
    <div class="absolute left-0 top-0 h-full w-1 bg-blue-500 opacity-0 transition-opacity group-hover:opacity-100"></div>

    <div class="flex flex-col justify-between h-full">
      <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">
        {{ label }}
      </p>
      
      <div class="mt-3 flex items-baseline gap-2">
        <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">
          {{ value }}
        </p>
        <slot name="trend"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  value: [String, Number],
})
</script>