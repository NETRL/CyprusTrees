<template>
    <div class="flex flex-col h-full">
        <!-- Sticky header for mobile bottom sheet -->
        <div class="shrink-0 pb-4 lg:pb-2">
            <span class="text-lg lg:text-xl font-bold text-gray-900 dark:text-white">
                Explore Trees in Nicosia
            </span>
        </div>
        
        <!-- Scrollable content area -->
        <div class="flex flex-col gap-4 min-h-0 flex-1">
            <div class="shrink-0">
                <CompactFilter v-model="selectedFilter" :options="options" />
            </div>
            
            <!-- Filter content -->
            <div class="flex-1 overflow-y-auto">
                <template v-if="selectedFilter === 'status'">
                    <div class="space-y-3">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Here is Status</span>
                        <!-- Add your status content here -->
                    </div>
                </template>
                <template v-if="selectedFilter === 'origin'">
                    <div class="space-y-3">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Here is Origin</span>
                        <!-- Add your origin content here -->
                    </div>
                </template>
                <template v-if="selectedFilter === 'pollen_risk'">
                    <div class="space-y-3">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Here is Pollen Risk</span>
                        <!-- Add your pollen risk content here -->
                    </div>
                </template>
                <template v-if="selectedFilter === 'water_use'">
                    <div class="space-y-3">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Here is Water Use</span>
                        <!-- Add your water use content here -->
                    </div>
                </template>
                <template v-if="selectedFilter === 'shade'">
                    <div class="space-y-3">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Here is Shade</span>
                        <!-- Add your shade content here -->
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import CompactFilter from '@/Components/Map/Partials/CompactFilter.vue'
import { useMapFilter } from '@/Composables/useMapFilter'
import { Leaf, MapPin, AlertTriangle, Droplets, Sun } from 'lucide-vue-next'

const { selectedFilter } = useMapFilter()

const options = [
    { label: 'Status', value: 'status', icon: Leaf },
    { label: 'Origin', value: 'origin', icon: MapPin },
    { label: 'Pollen Risk', value: 'pollen_risk', icon: AlertTriangle },
    { label: 'Water Use', value: 'water_use', icon: Droplets },
    { label: 'Shade', value: 'shade', icon: Sun },
]
</script>