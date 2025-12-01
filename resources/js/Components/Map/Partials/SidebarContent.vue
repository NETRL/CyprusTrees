<template>
    <div class="flex flex-col h-full overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-700 scrollbar-track-transparent">
        <!-- Header -->
        <div class="sticky shrink-0 pb-5 border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xl lg:text-2xl font-bold text-gray-900 dark:text-white">
                    Nicosia Trees
                </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Explore urban forestry data
            </p>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Total trees mapped: <span class="font-semibold">{{ totalTrees }}</span>
            </p>
        </div>

        <!-- Content Area -->
        <div class="flex flex-col gap-5 min-h-0 flex-1 pt-5">
            <!-- Filter Tabs -->
            <div class="shrink-0">
                <CompactFilter v-model="selectedFilter" :options="options" />
            </div>

            <!-- Legend Content -->
            <div
                class="flex-1 ">

                <!-- Status Filter -->
                <template v-if="selectedFilter === 'status'">
                    <div class="space-y-4 pb-4">
                        <div>
                            <h3
                                class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                                <Leaf class="w-4 h-4 text-emerald-600" />
                                Administrative Status
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                Shows the current state or life cycle of each tree or planting location.
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <LegendItem v-for="item in statusLegend" :key="item.key" :color="item.color"
                                :label="item.label" :count="item.count" :icon="item.icon"
                                @toggle="() => emit('toggleCategory', { mode: selectedFilter, key: item.key })" />
                        </div>
                    </div>
                </template>

                <!-- Origin Filter -->
                <template v-if="selectedFilter === 'origin'">
                    <div class="space-y-4 pb-4">
                        <div>
                            <h3
                                class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                                <MapPin class="w-4 h-4 text-emerald-600" />
                                Species Origin & Significance
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                Indicates whether species are native, endemic to Cyprus, or introduced.
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <LegendItem v-for="item in originLegend" :key="item.key" :color="item.color"
                                :label="item.label" :count="item.count" :icon="item.icon"
                                @toggle="() => emit('toggleCategory', { mode: selectedFilter, key: item.key })" />

                        </div>
                    </div>
                </template>

                <!-- Pollen Risk Filter -->
                <template v-if="selectedFilter === 'pollen_risk'">
                    <div class="space-y-4 pb-4">
                        <div>
                            <h3
                                class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                                <AlertTriangle class="w-4 h-4 text-amber-600" />
                                Pollen Risk (OPALS)
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                Based on OPALS score (1-10) for respiratory health risk.
                                <span class="font-medium">Note:</span> Primarily applies to male trees.
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 space-y-2">
                            <div class="h-6 rounded-full shadow-inner" :style="{
                                background: `linear-gradient(to right, ${POLLEN_RISK_COLORS[2]} 0%, ${POLLEN_RISK_COLORS[8]} 30%, ${POLLEN_RISK_COLORS[14]} 60%, ${POLLEN_RISK_COLORS[20]} 100%)`
                            }"></div>
                            <div class="flex justify-between text-xs font-medium text-gray-600 dark:text-gray-400">
                                <span>1 (Low Risk)</span>
                                <span>10 (Extreme)</span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Water Use Filter -->
                <template v-if="selectedFilter === 'water_use'">
                    <div class="space-y-4 pb-4">
                        <div>
                            <h3
                                class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                                <Droplets class="w-4 h-4 text-blue-600" />
                                Water Use & Drought Tolerance
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                Species' water requirements in Nicosia's dry Mediterranean climate.
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <LegendItem v-for="item in waterUseLegend" :key="item.key" :color="item.color"
                                :label="item.label" :count="item.count" :icon="item.icon"
                                @toggle="() => emit('toggleCategory', { mode: selectedFilter, key: item.key })" />

                        </div>
                    </div>
                </template>

                <!-- Shade Filter -->
                <template v-if="selectedFilter === 'shade'">
                    <div class="space-y-4 pb-4">
                        <div>
                            <h3
                                class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                                <Sun class="w-4 h-4 text-amber-600" />
                                Shade Contribution
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                Expected canopy size and shade area when the tree reaches maturity.
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <LegendItem v-for="item in shadeLegend" :key="item.key" :color="item.color"
                                :label="item.label" :count="item.count" :icon="item.icon"
                                @toggle="() => emit('toggleCategory', { mode: selectedFilter, key: item.key })" />

                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, watch } from 'vue';
import CompactFilter from '@/Components/Map/Partials/CompactFilter.vue';
import LegendItem from '@/Components/Map/Partials/LegentItem.vue';
import { useMapColors } from '@/Composables/useMapColors';
import { Leaf, MapPin, AlertTriangle, Droplets, Sun } from 'lucide-vue-next';
import { useMapFilter } from '@/Composables/useMapFilter';

const props = defineProps({
    selectedData: {
        type: Object,
        default: null
    },
    treeData: {
        type: Object,
        default: () => null
    },
    neighborhoodData: {
        type: Object,
        default: () => null
    },
    hiddenCategories: {
        type: Object,
        default: () => ({}), // { status: Set(), origin: Set(), ... }
    },
})

const emit = defineEmits(['toggleCategory']);

// --- Reactive State ---
const { selectedFilter } = useMapFilter();

// Define the available filters
const options = [
    { label: 'Status', value: 'status', icon: Leaf },
    { label: 'Origin', value: 'origin', icon: MapPin },
    { label: 'Pollen Risk', value: 'pollen_risk', icon: AlertTriangle },
    { label: 'Water Use', value: 'water_use', icon: Droplets },
    { label: 'Shade', value: 'shade', icon: Sun },
];

// --- Composable Import ---
const {
    STATUS_COLORS,
    ORIGIN_COLORS,
    POLLEN_RISK_COLORS,
    WATER_USE_COLORS,
    SHADE_COLORS
} = useMapColors();

const treeFeatures = computed(() =>
    props.treeData?.features ?? []
);

const totalTrees = computed(() => treeFeatures.value.length);

const makeCounts = (propName) => computed(() => {
    const counts = {};
    for (const feature of treeFeatures.value) {
        const val = feature.properties?.[propName];
        if (!val) continue;
        counts[val] = (counts[val] || 0) + 1;
    }
    counts['all'] = totalTrees.value
    return counts;
});


const statusCounts = makeCounts('status');
const originCounts = makeCounts('species_origin');
const waterUseCounts = makeCounts('species_drought_tolerance');
const shadeCounts = makeCounts('species_canopy_class');

// --- Helper Function for Categorical Legends ---
const getLegendData = (colorArray, labels) => {
    const data = [];
    for (let i = 0; i < colorArray.length - 1; i += 2) {
        data.push({
            key: colorArray[i],
            color: colorArray[i + 1],
            label: labels[colorArray[i]] || colorArray[i].replace(/_/g, ' ').toUpperCase(),
        });
    }
    return data;
};

// --- Labels for Categorical Data  ---
const statusLabels = {
    all: 'All',
    existing: 'Existing Tree',
    newly_planted: 'Newly Planted',
    proposed: 'Proposed Planting',
    vacant_pit: 'Vacant Pit (Opportunity)',
    pending_removal: 'Pending Removal (Hazard)',
    dead: 'Dead',
    stump: 'Stump Remaining',
    removed: 'Removed',
    missing: 'Missing',
    unknown: 'Unknown Status',
};

const originLabels = {
    native: 'Native to Cyprus',
    endemic: 'Endemic (Only in Cyprus)',
    exotic: 'Exotic (Introduced)',
};

const waterUseLabels = {
    low: 'High Water Need (Low Drought Tolerance)',
    moderate: 'Moderate Water Need',
    high: 'Low Water Need (High Drought Tolerance)',
};

const shadeLabels = {
    S: 'Small Canopy',
    M: 'Medium Canopy',
    L: 'Large Canopy',
};
// --- Legends with counts and icons ---

// Status includes "all"
const statusLegend = computed(() => {
    const orderedKeys = [
        'all',
        'existing',
        'newly_planted',
        'proposed',
        'vacant_pit',
        'pending_removal',
        'dead',
        'stump',
        'removed',
        'missing',
        'unknown',
    ];

    const counts = statusCounts.value;
    const hiddenSet =
        props.hiddenCategories?.status instanceof Set
            ? props.hiddenCategories.status
            : new Set();

    const anyHidden = hiddenSet.size > 0;

    return orderedKeys
        .map((key) => {
            // "all" is virtual row: neutral color + action-based icon
            if (key === 'all') {
                return {
                    key,
                    color: '#6b7280', // neutral gray
                    label: statusLabels[key],
                    count: counts[key] ?? totalTrees.value,
                    icon: anyHidden ? 'pi-eye' : 'pi-eye-slash',
                    isAll: true,
                };
            }

            const colorIndex = STATUS_COLORS.indexOf(key);
            const color = STATUS_COLORS[colorIndex + 1];
            if (!color) return null;

            const visible = !hiddenSet.has(key);
            const icon = visible ? 'pi-eye' : 'pi-eye-slash';

            return {
                key,
                color,
                label: statusLabels[key],
                count: counts[key] ?? 0,
                icon,
                isAll: false,
            };
        })
        .filter(Boolean);
});

const originLegend = computed(() => {
    const base = getLegendData(ORIGIN_COLORS, originLabels);
    const counts = originCounts.value;
    const hiddenSet =
        props.hiddenCategories?.origin instanceof Set
            ? props.hiddenCategories.origin
            : new Set();

    return base.map((item) => {
        const visible = !hiddenSet.has(item.key);
        const icon = visible ? 'pi-eye-slash' : 'pi-eye';

        return {
            ...item,
            count: counts[item.key] ?? 0,
            icon,
        };
    });
});

const waterUseLegend = computed(() => {
    const base = getLegendData(WATER_USE_COLORS, waterUseLabels);
    const counts = waterUseCounts.value;
    const hiddenSet =
        props.hiddenCategories?.water_use instanceof Set
            ? props.hiddenCategories.water_use
            : new Set();

    return base.map((item) => {
        const visible = !hiddenSet.has(item.key);
        const icon = visible ? 'pi-eye-slash' : 'pi-eye';

        return {
            ...item,
            count: counts[item.key] ?? 0,
            icon,
        };
    });
});

const shadeLegend = computed(() => {
    const base = getLegendData(SHADE_COLORS, shadeLabels);
    const counts = shadeCounts.value;
    const hiddenSet =
        props.hiddenCategories?.shade instanceof Set
            ? props.hiddenCategories.shade
            : new Set();

    return base.map((item) => {
        const visible = !hiddenSet.has(item.key);
        const icon = visible ? 'pi-eye-slash' : 'pi-eye';

        return {
            ...item,
            count: counts[item.key] ?? 0,
            icon,
        };
    });
});


</script>