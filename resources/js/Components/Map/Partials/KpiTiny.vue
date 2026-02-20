<template>
  <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-900/40">
    <div class="text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
      {{ label }}
    </div>
    <div class="mt-1 truncate text-sm font-extrabold text-gray-900 dark:text-gray-100">
      {{ value ?? "—" }}
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], default: null },
})
</script>
