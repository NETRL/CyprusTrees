<!-- components/KpiCard.vue -->
<template>
  <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
    <p class="text-xs uppercase text-slate-500 dark:text-slate-400">{{ label }}</p>
    <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">
      {{ value }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  value: [String, Number],
})
</script>
