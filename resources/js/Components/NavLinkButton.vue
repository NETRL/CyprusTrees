<template>
  <Link 
    :href="href" 
    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white p-3 text-xs font-bold uppercase tracking-wider text-slate-600 shadow-sm transition-all hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-brand-900 dark:hover:bg-brand-950 dark:hover:text-brand-400"
  >
    <slot />
  </Link>
</template>


<script setup>

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: '',
    },
});

</script>