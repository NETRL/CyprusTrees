<template>
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                <h2 class="text-2xl font-bold dark:text-white text-gray-900">Environmental Impact Report</h2>
                <button @click="$emit('close')" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full">
                    <X class="w-6 h-6 dark:text-gray-400" />
                </button>
            </div>

            <div class="p-6 overflow-y-auto grid md:grid-cols-2 gap-6">
                <div class="p-5 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                    <div class="flex items-center gap-3 mb-4">
                        <CloudRain class="w-8 h-8 text-emerald-600" />
                        <h3 class="font-bold dark:text-white">Carbon Sequestration</h3>
                    </div>
                    <p class="text-3xl font-black text-emerald-700 dark:text-emerald-400">~{{ (totalTrees * 22).toLocaleString() }} kg</p>
                    <p class="text-xs text-emerald-600/80 mt-1">Estimated CO2 absorbed per year</p>
                </div>

                <div class="p-5 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800">
                    <div class="flex items-center gap-3 mb-4">
                        <Wind class="w-8 h-8 text-blue-600" />
                        <h3 class="font-bold dark:text-white">Oxygen Production</h3>
                    </div>
                    <p class="text-3xl font-black text-blue-700 dark:text-blue-400">{{ (totalTrees * 2).toLocaleString() }} people</p>
                    <p class="text-xs text-blue-600/80 mt-1">Daily oxygen for Nicosia residents</p>
                </div>

                <div class="md:col-span-2">
                    <h3 class="font-bold mb-4 dark:text-white">Top Contributing Campaigns</h3>
                    <div class="space-y-2">
                        <div v-for="event in events" :key="event.id" class="w-full">
                            <div class="flex justify-between text-xs mb-1 dark:text-gray-400">
                                <span>{{ event.campaign?.name }}</span>
                                <span>{{ event.event_trees?.length }} Trees</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 h-2 rounded-full">
                                <div class="bg-emerald-500 h-full rounded-full" :style="{ width: (event.event_trees.length / totalTrees * 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { X, CloudRain, Wind } from 'lucide-vue-next';

const props = defineProps(['events']);
const totalTrees = computed(() => props.events.reduce((sum, e) => sum + e.event_trees.length, 0));
</script>