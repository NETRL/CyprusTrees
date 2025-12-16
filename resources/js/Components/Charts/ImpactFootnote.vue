<template>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
        CO₂ values are estimates derived from DBH and planting year. Coverage:
        {{ data.coverage_pct ?? 0 }}% ({{ data.eligible ?? 0 }}/{{
            data.total ?? 0 }} trees).
        <a :href="route('dashboard.methodology')" class="underline">Methodology</a>
    </p>
</template>

<script setup>

    defineProps({
        data: {
            type: Object,
            default: null,
        }
    })
</script>