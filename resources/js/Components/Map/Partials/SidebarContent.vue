<template>
    <div class="flex flex-col gap-4 max-w-[390px]">
        <span class="lg:text-xl font-bold">Expolore Trees in Nicosia</span>
        <SegmentedFilter v-model="selected" :options="options" />
        
        <span>Some very very very very very very very very very very very very very very very very very very
            very very long text</span>
            <span>More content here</span>
            <span>And more...</span>
        </div>
    </template>

<script setup>
import SegmentedFilter from '@/Components/Map/Partials/SegmentedFilter.vue'
import { ref } from 'vue';

const selected = ref('type')

const options = [
    { label: 'Type', value: 'type' },
    { label: 'Girth', value: 'girth' },
    { label: 'Age', value: 'age' },
    { label: 'Family', value: 'family' },
]
</script>