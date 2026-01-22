<template>
    <div
        class="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-700 scrollbar-track-transparent w-full">

        <div class="sticky top-0 z-10 transition-colors duration-300
            bg-white dark:bg-gray-900 backdrop-blur-md 
            border-b border-gray-200 dark:border-gray-800 px-4 lg:py-4">
            
            <div class="flex items-center justify-between mb-2">
                <span :class="[
                    'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest border',
                    isSelected
                        ? 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800'
                        : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'
                ]">
                    {{ isSelected ? 'Selected District' : 'Hovering' }}
                </span>
                <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500">
                    ID: {{ activeNeighborhood.id }}
                </span>
            </div>

            <div class="mb-4">
                <h3 class="text-xl font-extrabold text-gray-900 dark:text-white leading-tight">
                    {{ activeNeighborhood.name || 'Unknown Neighborhood' }}
                </h3>
                <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400 italic flex items-center gap-1">
                    <MapPin class="w-3 h-3" />
                    {{ activeNeighborhood.district || 'District N/A' }}, {{ activeNeighborhood.city }}
                </p>
            </div>
        </div>
        
        

        <div class="p-5 space-y-5">
            
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-1">
                        <Trees class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        <div class="text-[10px] uppercase tracking-tighter text-gray-500 dark:text-gray-400">Total Trees</div>
                    </div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        {{ stats.total_trees || 0 }}
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-1">
                        <Umbrella class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        <div class="text-[10px] uppercase tracking-tighter text-gray-500 dark:text-gray-400">Avg Canopy</div>
                    </div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        {{ stats.avg_canopy ? stats.avg_canopy + 'm' : '—' }}
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center gap-2">
                    <Sprout class="w-4 h-4" />
                    Biodiversity (Top 3)
                </h4>
                
                <div v-if="stats.top_species && stats.top_species.length" class="space-y-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                    <div v-for="(specie, index) in stats.top_species" :key="index">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ specie.name }}</span>
                            <span class="text-gray-500">{{ specie.percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                            <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500"
                                :style="{ width: `${specie.percentage}%` }"></div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-xs text-gray-400 italic text-center py-2">
                    No species data available.
                </div>
            </div>

            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center gap-2">
                    <HeartPulse class="w-4 h-4" />
                    Ecological Health
                </h4>
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800 rounded-lg p-2 text-center">
                        <div class="text-lg font-bold text-green-700 dark:text-green-400">{{ stats.health_good_pct || 0 }}%</div>
                        <div class="text-[10px] text-green-600 dark:text-green-500">Good Health</div>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-lg p-2 text-center">
                        <div class="text-lg font-bold text-amber-700 dark:text-amber-400">{{ stats.health_poor_pct || 0 }}%</div>
                        <div class="text-[10px] text-amber-600 dark:text-amber-500">Attn. Needed</div>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <ClipboardList class="w-4 h-4" />
                        Neighborhood Status
                    </h4>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 space-y-2 text-xs border border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Open Reports</span>
                        <span :class="[
                            'px-2 py-0.5 rounded-full font-bold',
                            (stats.open_reports > 0) ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                        ]">
                            {{ stats.open_reports || 0 }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Last Planting Event</span>
                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                            {{ formatDate(stats.last_planted_at) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Maint. Cost (YTD)</span>
                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                            ${{ stats.maintenance_cost_ytd || '0.00' }}
                        </span>
                    </div>
                </div>
            </div>

            <div v-if="isSelected" class="pt-2">
                <button class="w-full bg-linear-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-md">
                    <FileText class="w-4 h-4" />
                    Download Neighborhood Report
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { MapPin, Trees, Sprout, HeartPulse, ClipboardList, FileText, Umbrella } from 'lucide-vue-next';
import { useDateFormatter } from '@/Composables/useDateFormatter';


const { formatDate } = useDateFormatter()

const props = defineProps({
    activeNeighborhood: {
        type: Object,
        required: true,
        default: () => ({})
    },
    // We expect the parent to fetch these stats when a neighborhood is clicked
    stats: {
        type: Object,
        default: () => ({
            total_trees: 0,
            avg_canopy: 0,
            top_species: [], // {name: 'Oak', percentage: 40}
            health_good_pct: 0,
            health_poor_pct: 0,
            open_reports: 0,
            last_planted_at: null,
            maintenance_cost_ytd: 0
        })
    },
    isSelected: {
        type: Boolean,
        default: true
    }
});


</script>