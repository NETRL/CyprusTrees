<template>
    <!-- Scrollable Content -->
    <div
        class="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-700 scrollbar-track-transparent">

        <!-- Header Section -->
        <div
            class="sticky top-0 z-10 bg-gradient-to-br from-emerald-600 to-teal-600 p-5 border-b border-emerald-700/50">
            <!-- Status Badge -->
            <div class="flex items-center justify-between mb-3">
                <span :class="[
                    'px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide',
                    isSelected ? 'bg-white/20 text-white' : 'bg-white/10 text-white/80'
                ]">
                    {{ isSelected ? 'Selected' : 'Hovering' }}
                </span>
                <span class="text-xs text-white/70">
                    ID: {{ activeTree.id }}
                </span>
            </div>

            <!-- Species Name -->
            <div class="space-y-1">
                <h3 class="text-2xl font-bold text-white leading-tight">
                    {{ speciesData?.common_name || 'Unknown' }}
                </h3>
                <p class="text-sm text-white/80 italic">
                    {{ speciesData?.latin_name || 'Species not identified' }}
                </p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-2 mt-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-2 text-center">
                    <div class="text-xs text-white/70 mb-0.5">Height</div>
                    <div class="text-lg font-bold text-white">{{ activeTree.height_m || '—' }}m</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-2 text-center">
                    <div class="text-xs text-white/70 mb-0.5">DBH</div>
                    <div class="text-lg font-bold text-white">{{ activeTree.dbh_cm || '—' }}cm</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-2 text-center">
                    <div class="text-xs text-white/70 mb-0.5">Canopy</div>
                    <div class="text-lg font-bold text-white">{{ activeTree.canopy_diameter_m || '—' }}m</div>
                </div>
            </div>
        </div>

        <!-- Content Sections -->
        <div class="p-5 space-y-4">

            <!-- Location Info -->
            <div class="space-y-2">
                <h4
                    class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center gap-2">
                    <MapPin class="w-4 h-4" />
                    Location
                </h4>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 space-y-1">
                    <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                        {{ activeTree.address || 'No address available' }}
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        {{ neighborhoodData?.name }}, {{ neighborhoodData?.district }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-500 font-mono">
                        {{ activeTree.lat?.toFixed(6) }}, {{ activeTree.lon?.toFixed(6) }}
                    </div>
                </div>
            </div>

            <!-- Status & Health -->
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-2">
                    <h4
                        class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center gap-2">
                        <Activity class="w-4 h-4" />
                        Status
                    </h4>
                    <div :class="[
                        'rounded-lg p-3 text-center',
                        getStatusColor(activeTree.status)
                    ]">
                        <div class="text-xs font-medium mb-1 opacity-80">Tree Status</div>
                        <div class="text-sm font-bold capitalize">
                            {{ activeTree.status?.replace('_', ' ') || 'Unknown' }}
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <h4
                        class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center gap-2">
                        <Heart class="w-4 h-4" />
                        Health
                    </h4>
                    <div :class="[
                        'rounded-lg p-3 text-center',
                        getHealthColor(activeTree.health_status)
                    ]">
                        <div class="text-xs font-medium mb-1 opacity-80">Condition</div>
                        <div class="text-sm font-bold capitalize">
                            {{ activeTree.health_status || 'Unknown' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Environmental Impact -->
            <div class="space-y-2">
                <h4
                    class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center gap-2">
                    <Leaf class="w-4 h-4" />
                    Environmental Info
                </h4>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 space-y-2">
                    <!-- Origin -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600 dark:text-gray-400">Origin</span>
                        <span :class="[
                            'px-2 py-1 rounded text-xs font-medium',
                            activeTree.species_origin === 'native' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' :
                                activeTree.species_origin === 'endemic' ? 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400' :
                                    'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
                        ]">
                            {{ activeTree.species_origin || 'Unknown' }}
                        </span>
                    </div>
                    <!-- Drought Tolerance -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600 dark:text-gray-400">Water Need</span>
                        <span :class="[
                            'px-2 py-1 rounded text-xs font-medium',
                            activeTree.species_drought_tolerance === 'high' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' :
                                activeTree.species_drought_tolerance === 'moderate' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' :
                                    'bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400'
                        ]">
                            {{ getWaterNeed(activeTree.species_drought_tolerance) }}
                        </span>
                    </div>
                    <!-- Canopy Class -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600 dark:text-gray-400">Shade Potential</span>
                        <span :class="[
                            'px-2 py-1 rounded text-xs font-medium',
                            activeTree.species_canopy_class === 'L' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' :
                                activeTree.species_canopy_class === 'M' ? 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400' :
                                    'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                        ]">
                            {{ getCanopySize(activeTree.species_canopy_class) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pollen Risk Warning -->
            <div v-if="activeTree.calculated_pollen_risk >= 7"
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                <div class="flex items-start gap-2">
                    <AlertTriangle class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                    <div>
                        <div class="text-sm font-semibold text-red-900 dark:text-red-300 mb-1">
                            High Pollen Risk
                        </div>
                        <div class="text-xs text-red-700 dark:text-red-400">
                            OPALS Score: {{ activeTree.calculated_pollen_risk }}/10. May cause respiratory issues during
                            flowering season.
                        </div>
                    </div>
                </div>
            </div>
            <div v-else
                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                <div class="flex items-center gap-2">
                    <Info class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                    <div class="text-xs text-blue-700 dark:text-blue-400">
                        Pollen Risk: {{ activeTree.calculated_pollen_risk || 'N/A' }}/10 ({{
                            getPollenRiskLabel(activeTree.calculated_pollen_risk) }})
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="space-y-2">
                <h4
                    class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center gap-2">
                    <Calendar class="w-4 h-4" />
                    Timeline
                </h4>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Planted</span>
                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                            {{ formatDate(activeTree.planted_at) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Last Inspected</span>
                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                            {{ formatDate(activeTree.last_inspected_at) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Owner</span>
                        <span class="text-gray-900 dark:text-gray-100 font-medium capitalize">
                            {{ activeTree.owner_type || 'Unknown' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Species Notes (Only for selected) -->
            <div v-if="isSelected && speciesData?.notes" class="space-y-2">
                <h4
                    class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center gap-2">
                    <FileText class="w-4 h-4" />
                    Species Notes
                </h4>
                <div
                    class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                    {{ speciesData.notes }}
                </div>
            </div>
            <!-- Report Issue Button -->
            <div v-if="isSelected" class="pt-2 pb-1">
                <button @click="toggleReportModal"
                    class="w-full bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg active:scale-[0.98]">
                    <Flag class="w-4 h-4" />
                    Report an Issue
                </button>
            </div>
        </div>
        <TreeReportCard  @closeModal="toggleReportModal" :showModal="showModal" :tree="selected"/>
    </div>


</template>

<script setup>
import { MapPin, Activity, Heart, Leaf, AlertTriangle, Info, Calendar, FileText, Flag } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import TreeReportCard from '@/Components/Map/Partials/TreeReportCard.vue';

const props = defineProps({
    hovered: {
        type: Object,
        default: null
    },
    selected: {
        type: Object,
        default: null
    },
    isHovered: {
        type: Boolean,
        required: true
    },
    isSelected: {
        type: Boolean,
        required: true
    }
});

const showModal = ref(false)

// Active tree is selected if available, otherwise hovered
const activeTree = computed(() => props.selected || props.hovered || {});

// Parse JSON strings from data
const speciesData = computed(() => {
    if (!activeTree.value.species) return null;
    try {
        return typeof activeTree.value.species === 'string'
            ? JSON.parse(activeTree.value.species)
            : activeTree.value.species;
    } catch {
        return null;
    }
});

const toggleReportModal = () => {
        showModal.value = !showModal.value
}


const neighborhoodData = computed(() => {
    if (!activeTree.value.neighborhood) return null;
    try {
        return typeof activeTree.value.neighborhood === 'string'
            ? JSON.parse(activeTree.value.neighborhood)
            : activeTree.value.neighborhood;
    } catch {
        return null;
    }
});

// Helper functions
const getStatusColor = (status) => {
    const colors = {
        existing: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
        newly_planted: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
        proposed: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
        dead: 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400',
        removed: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-500',
        pending_removal: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
        vacant_pit: 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400',
    };
    return colors[status] || 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400';
};

const getHealthColor = (health) => {
    const colors = {
        excellent: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
        good: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
        fair: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
        poor: 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
        critical: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    };
    return colors[health] || 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400';
};

const getWaterNeed = (droughtTolerance) => {
    const mapping = {
        high: 'Low',
        moderate: 'Moderate',
        low: 'High'
    };
    return mapping[droughtTolerance] || 'Unknown';
};

const getCanopySize = (canopyClass) => {
    const mapping = {
        S: 'Small',
        M: 'Medium',
        L: 'Large'
    };
    return mapping[canopyClass] || 'Unknown';
};

const getPollenRiskLabel = (score) => {
    if (score <= 3) return 'Low';
    if (score <= 6) return 'Moderate';
    if (score <= 8) return 'High';
    return 'Extreme';
};

const formatDate = (dateString) => {
    if (!dateString) return 'Not recorded';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};


</script>