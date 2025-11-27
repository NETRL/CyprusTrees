<template>
    <div class="flex flex-col gap-4 max-w-[390px]">
        <span class="lg:text-xl font-bold">Expolore Trees in Nicosia</span>
        <SegmentedFilter v-model="selectedFilter" :options="options" />

        <template v-if="selectedFilter === 'status'">
            <span>Here is Status</span>
        </template>
        <template v-if="selectedFilter === 'pollen_risk'">
            <span>Here is Pollen Risk</span>
        </template>
        <template v-if="selectedFilter === 'water_use'">
            <span>Here is Water Use</span>
        </template>
        <template v-if="selectedFilter === 'shade'">
            <span>Here is Shade</span>
        </template>
    </div>
</template>

<script setup>
import SegmentedFilter from '@/Components/Map/Partials/SegmentedFilter.vue'
import { useMapFilter } from '@/Composables/useMapFilter';
import { ref, watch } from 'vue';

const { selectedFilter } = useMapFilter();

const options = [
    { label: 'Status', value: 'status' },
    { label: 'Pollen Risk', value: 'pollen_risk' },
    { label: 'Water Use', value: 'water_use' },
    { label: 'Shade', value: 'shade' },
]
</script>