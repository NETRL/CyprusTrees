<template>
    <!-- Scrollable Content -->
    <div
        class="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-700 scrollbar-track-transparent w-full">

        <!-- Header Section -->
        <div class="sticky top-0 z-10 transition-colors duration-300
            bg-white dark:bg-gray-900 backdrop-blur-md 
            border-b border-gray-200 dark:border-gray-800 px-4 lg:py-4">
            <div class="flex items-center justify-between mb-2">
                <span :class="[
                    'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest border',
                    isSelected
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800'
                        : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'
                ]">
                    {{ isSelected ? 'Selected' : 'Hovering' }}
                </span>
                <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500">
                    ID: {{ activeTree.id }}
                </span>
            </div>

            <div class="mb-4">
                <h3 class="text-xl font-extrabold text-gray-900 dark:text-white leading-tight">
                    {{ speciesData?.common_name || 'Unknown' }}
                </h3>
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 italic">
                    {{ speciesData?.latin_name || 'Species not identified' }}
                </p>
            </div>

            <button v-if="true && isSelected" type="button" class="px-2.5 py-1 w-full text-xs rounded-md border border-gray-200 hover:bg-gray-50
               dark:border-gray-700 dark:hover:bg-white/5 disabled:opacity-50" @click="copySelected"
                title="Copy values from the currently selected tree">
                Copy selected
            </button>
        </div>

        <!-- Content Sections -->
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-3 gap-2">
                <div
                    class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50 rounded-lg p-2 text-center">
                    <div class="text-[10px] uppercase tracking-tighter text-gray-500 dark:text-gray-400">Height</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ activeTree.height_m || '—' }}m
                    </div>
                </div>
                <div
                    class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50 rounded-lg p-2 text-center">
                    <div class="text-[10px] uppercase tracking-tighter text-gray-500 dark:text-gray-400">DBH</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ activeTree.dbh_cm || '—' }}cm
                    </div>
                </div>
                <div
                    class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50 rounded-lg p-2 text-center">
                    <div class="text-[10px] uppercase tracking-tighter text-gray-500 dark:text-gray-400">Canopy</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ activeTree.canopy_diameter_m ||
                        '—'
                        }}m</div>
                </div>
            </div>

            <!-- Location Info -->
            <div class="space-y-2">
                <h4
                    class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center gap-2">
                    <MapPin class="w-4 h-4" />
                    Location
                </h4>

                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                    <!-- Address Information -->
                    <div class="space-y-1 mb-3">
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

                    <!-- Google Maps Button -->
                    <a :href="`https://www.google.com/maps/search/?api=1&query=${activeTree.lat},${activeTree.lon}`"
                        target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 w-full px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white text-sm font-medium transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a3 3 0 100-6 3 3 0 000 6z"
                                clip-rule="evenodd"></path>
                        </svg>
                        View in Google Maps
                    </a>
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

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <MessageSquare class="w-4 h-4" />
                        Reports
                    </h4>
                </div>

                <!-- Status Summary -->
                <div class="flex gap-2">
                    <div
                        class="flex-1 bg-red-50 dark:bg-red-900/20 rounded-lg p-2.5 text-center border border-red-200 dark:border-red-800">
                        <div class="text-lg font-bold text-red-700 dark:text-red-400">
                            {{reportsWithNames.filter(r => r.status === 'open').length}}
                        </div>
                        <div class="text-xs text-red-600 dark:text-red-500">Open</div>
                    </div>
                    <div
                        class="flex-1 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-2.5 text-center border border-amber-200 dark:border-amber-800">
                        <div class="text-lg font-bold text-amber-700 dark:text-amber-400">
                            {{reportsWithNames.filter(r => r.status === 'triaged').length}}
                        </div>
                        <div class="text-xs text-amber-600 dark:text-amber-500">Triaged</div>
                    </div>
                    <div
                        class="flex-1 bg-green-50 dark:bg-green-900/20 rounded-lg p-2.5 text-center border border-green-200 dark:border-green-800">
                        <div class="text-lg font-bold text-green-700 dark:text-green-400">
                            {{reportsWithNames.filter(r => r.status === 'resolved').length}}
                        </div>
                        <div class="text-xs text-green-600 dark:text-green-500">Resolved</div>
                    </div>
                </div>

                <!-- Recent Reports List -->
                <details v-if="reportsWithNames.length > 0" class="space-y-1.5">
                    <summary
                        class="font-medium cursor-pointer text-gray-700 dark:text-gray-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all">
                        View Recent Activity
                    </summary>
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400 px-1">Recent Activity</p>
                    <div v-for="report in reportsWithNames.slice(0, 3)" :key="report.id"
                        class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <div :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                            report.status === 'resolved'
                                ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400'
                                : report.status === 'open'
                                    ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
                                    : 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400'
                        ]">
                            <Flag class="w-3.5 h-3.5" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ report.reportTypeName }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ formatRelativeTime(report.created_at) }}
                            </p>
                        </div>
                        <Check v-if="report.status === 'resolved'" class="w-4 h-4 text-green-600 dark:text-green-400" />
                        <Clock v-else-if="report.status === 'open'" class="w-4 h-4 text-red-600 dark:text-red-400" />
                        <Clock v-else class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                    </div>
                </details>
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
            <div v-if="isSelected" :class="[
                'pt-2 grid gap-2',
                (can('trees.edit') ? 'grid-cols-1 sm:grid-cols-2' : 'grid-cols-1')
            ]">
                <!-- Report Issue -->
                <div>
                    <div v-if="userHasActiveReports"
                        class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mb-2">
                        <div class="flex items-center gap-2 text-sm text-yellow-800 dark:text-yellow-300">
                            <Info class="w-4 h-4" />
                            You have a pending report for this tree. Thank you for your contribution!
                        </div>
                    </div>

                    <button @click="toggleReportModal"
                        class="w-full bg-linear-to-r text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg active:scale-[0.98]"
                        :class="userHasActiveReports
                            ? 'from-yellow-600 to-yellow-600 hover:from-yellow-700 hover:to-yellow-700'
                            : 'from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700'">
                        <Flag class="w-4 h-4" />
                        <span>{{ userHasActiveReports ? 'Report another Issue' : 'Report an Issue' }}</span>
                    </button>
                </div>

                <!-- Edit Tree -->
                <button @click="emit('editClick')" v-if="can('trees.edit')"
                    class="w-full bg-linear-to-r from-green-600 to-emerald-600 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg active:scale-[0.98]">
                    <Edit class="w-4 h-4" />
                    Edit Tree Info
                </button>
            </div>

        </div>
        <TreeReportCard @closeModal="toggleReportModal" @submitted="handleSubmitted" :showModal="showModal"
            :tree="selected" />
    </div>


</template>

<script setup>
import { MapPin, Activity, Heart, Leaf, AlertTriangle, Info, Calendar, FileText, Flag, Edit, CheckCircle, Clock, User, History, ChevronDown, MessageSquare, Check, AlertCircle } from 'lucide-vue-next';
import { computed, inject, ref, watch } from 'vue';
import TreeReportCard from '@/Components/Map/Partials/TreeReportCard.vue';
import { safeJsonParse } from '@/Composables/safeJsonParser';
import { usePage } from '@inertiajs/vue3';
import { fetchTreeDetails } from '@/Lib/Map/DataLayers';

const emit = defineEmits(['update:selected', 'editClick'])

const formatRelativeTime = (date) => {
    const now = new Date();
    const reportDate = new Date(date);
    const diffInHours = Math.floor((now - reportDate) / (1000 * 60 * 60));

    if (diffInHours < 1) return 'Just now';
    if (diffInHours < 24) return `${diffInHours}h ago`;
    if (diffInHours < 48) return 'Yesterday';
    if (diffInHours < 168) return `${Math.floor(diffInHours / 24)}d ago`;
    return formatDate(date);
};

const page = usePage();

const can = inject('can')

const props = defineProps({
    hovered: { type: Object, default: null },
    selected: { type: Object, default: null },
    isHovered: { type: Boolean, required: true },
    isSelected: { type: Boolean, required: true }
});

const reportTypes = inject('reportTypes');

const isPlantingMode = inject('isPlantingMode')
const lastCreatedTree = inject('lastCreatedTree')

const userId = computed(() => page.props.auth.user?.id);

const userHasActiveReports = computed(() => {

    const reports = reportData.value;
    if (!reports || reports.length === 0) {
        return false;
    }
    const currentUserId = userId.value;
    if (!currentUserId) {
        return false;
    }
    return reports.some(report =>
        report.created_by === currentUserId &&
        report.status !== 'resolved' &&
        report.status !== 'triaged'
    );
})

const showModal = ref(false)

const treeOverride = ref(null)
// Active tree is selected if available, otherwise hovered
const activeTree = computed(() => treeOverride.value || props.selected || props.hovered || {});

watch(
    () => props.selected?.id,
    () => {
        // whenever selection changes from outside, drop override
        treeOverride.value = null
    }
)

// Parse JSON strings from data
const speciesData = computed(() => {
    return safeJsonParse(activeTree.value?.species, null)
});

const reportData = computed(() => {
    return safeJsonParse(activeTree.value?.citizenReports, null)
});

const reportsWithNames = computed(() => {

    const reports = reportData.value;

    if (!reports) {
        return [];
    }

    // Create a simple lookup map for report types (e.g., {1: "Irrigation", 2: "Pests"})
    // This is much faster than looping through the reportTypes array for every report.
    const typeLookup = reportTypes.reduce((acc, type) => {
        acc[type.id] = type.name;
        return acc;
    }, {});

    // Map the reports to include the readable name
    const mappedReports = reports.map(report => ({
        ...report,
        reportTypeName: typeLookup[report.report_type_id] || 'Unknown Type',
    }));

    // Sort the final array by creation date (most recent first)
    return mappedReports.sort((a, b) => {
        const dateA = new Date(a.created_at || 0);
        const dateB = new Date(b.created_at || 0);
        return dateB - dateA;
    });
});

const toggleReportModal = () => {
    showModal.value = !showModal.value
}

const handleSubmitted = () => {
    toggleReportModal()
    fetchTreeDetails(
        activeTree.value.id,
        {
            onDataLoaded: (data) => {
                treeOverride.value = data
            },
        }
    )
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

function copySelected() {
    lastCreatedTree.value = activeTree.value
    console.log(lastCreatedTree.value)
}

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