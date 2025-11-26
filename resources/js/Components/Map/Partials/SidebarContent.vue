<template>
    <div class="flex flex-col gap-4 max-w-[390px]">
        <span class="lg:text-xl font-bold">Expolore Trees in Nicosia</span>
        <SegmentedFilter v-model="currentSelection" :options="options" />

        <template v-if="currentSelection === 'type'">
            <span>Here is Type</span>
        </template>
        <template v-if="currentSelection === 'height'">
            <span>Here is Height</span>
        </template>
        <template v-if="currentSelection === 'age'">
            <span>Here is Age</span>
        </template>
        <template v-if="currentSelection === 'species'">
            <span>Here is Species</span>
        </template>
    </div>
</template>

<script setup>
import SegmentedFilter from '@/Components/Map/Partials/SegmentedFilter.vue'
import { useMapFilter } from '@/Composables/useMapFilter';
import { ref, watch } from 'vue';

const { currentSelection } = useMapFilter();

const options = [
    { label: 'Type', value: 'type' },
    { label: 'Height', value: 'height' },
    { label: 'Age', value: 'age' },
    { label: 'Species', value: 'species' },
]
</script>