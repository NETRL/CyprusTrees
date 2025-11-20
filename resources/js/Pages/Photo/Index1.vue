<template>
    <ComponentCard class="w-full flex-1 flex flex-col" title="Manage Tree Photos">
        <!-- Search & Actions Bar -->
        <form class="flex flex-col gap-3" @submit.prevent="loadPhotos">
            <div class="flex flex-col sm:flex-row sm:flex-nowrap gap-3">
                <!-- Tree ID -->
                <div class="relative flex-1 sm:flex-initial">
                    <FormField v-model="filters.tree_id" class="w-full sm:max-w-3xs" name="tree_id" component="Number"
                        label="Tree ID" :displayErrors="true" placeholder="E.g. 123" :useGrouping="false"
                        @input="filters.tree_id = $event.value" />
                </div>

                <!-- Global text search -->
                <div class="flex-1">
                    <FormField v-model="filters.search" class="w-full" name="search"
                        label="Search caption / address / species / neighborhood"
                        placeholder="e.g. oak, downtown, broken branch..." />
                </div>

                <!-- Sort dropdown -->
                <div class="w-full sm:w-56">
                    <FormField v-model="filters.sort" name="sort" component="Dropdown" label="Sort by"
                        :options="sortOptions" optionLabel="label" optionValue="value" />
                </div>

                <!-- Actions -->
                <div class="flex gap-2 sm:flex-col sm:justify-end">
                    <Button class="text-nowrap h-10 sm:h-10! sm:mt-auto" icon="pi pi-search" label="Apply" type="submit"
                        v-has-permission="{ props: $page.props, permissions: ['photos.view'] }" />
                    <Button type="button" class="text-nowrap h-10 sm:h-10! sm:mt-auto" icon="pi pi-times"
                        severity="secondary" label="Reset" @click="resetFilters" />
                </div>
            </div>

            <!-- Advanced filters row -->
            <div class="grid gap-3 sm:grid-cols-4">
                <!-- Date from -->
                <FormField v-model="filters.date_from" name="date_from" component="Calendar" label="Captured From"
                    :showIcon="true" dateFormat="yy-mm-dd" />

                <!-- Date to -->
                <FormField v-model="filters.date_to" name="date_to" component="Calendar" label="Captured To"
                    :showIcon="true" dateFormat="yy-mm-dd" />

                <!-- Neighborhood -->
                <FormField v-model="filters.neighborhood_id" name="neighborhood_id" component="Dropdown"
                    label="Neighborhood" :options="neighborhoodFilterOptions" optionLabel="label" optionValue="id"
                    placeholder="All neighborhoods" />

                <!-- Species -->
                <FormField v-model="filters.species_id" name="species_id" component="Dropdown" label="Species"
                    :options="speciesFilterOptions" optionLabel="label"  optionValue="id" placeholder="All species" />
            </div>
        </form>


        <!-- Content Area -->
        <div class="justify-start mt-6">
            <!-- No photos at all (DB empty, explore mode) -->
            <template v-if="tableData && tableData.total === 0 && !initialTreeId">
                <div class="text-center py-12">
                    <i class="pi pi-image text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <p class="text-lg sm:text-xl text-gray-500 mb-2">
                        There are no photos yet.
                    </p>
                    <p class="text-sm text-gray-400">
                        Add a photo to a tree and they will appear here.
                    </p>
                </div>
            </template>

            <!-- Tree not found -->
            <template v-else-if="initialTreeId && !selectedTree">
                <div class="text-center py-12">
                    <i class="pi pi-exclamation-circle text-6xl text-error-300 dark:text-error-600 mb-4"></i>
                    <p class="text-lg sm:text-xl text-gray-500">
                        The tree with ID: <strong>{{ initialTreeId }}</strong> does not exist.
                    </p>
                </div>
            </template>

            <!-- We have photos or at least a valid tree (explore OR tree mode) -->
            <template v-else>
                <!-- Explore mode OR tree mode wrapper -->
                <div v-if="tableData">
                    <!-- HEADER  -->
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <!-- Tree mode -->
                        <template v-if="selectedTree">
                            <span class="text-base sm:text-xl text-black dark:text-white">
                                Showing photos for tree
                                <strong class="text-brand-500">#{{ selectedTree.id }}</strong>
                                <span v-if="selectedTree.species_label" class="text-gray-600 dark:text-gray-400">
                                    ({{ selectedTree.species_label }})
                                </span>
                            </span>
                            <span v-if="tableData.total > 0" class="text-sm text-gray-500 dark:text-gray-400">
                                {{ tableData.total }} {{ tableData.total === 1 ? 'photo' : 'photos' }} total
                            </span>

                        </template>

                        <!-- Explore mode -->
                        <template v-else>
                            <span class="text-base sm:text-xl text-black dark:text-white">
                                Browsing <strong class="text-brand-500">random photos</strong>
                            </span>
                            <span v-if="tableData.total > 0" class="text-sm text-gray-500 dark:text-gray-400">
                                {{ tableData.total }} {{ tableData.total === 1 ? 'photo' : 'photos' }} total
                            </span>
                        </template>
                    </div>

                    <!-- GRID -->
                    <div v-if="tableData.data.length > 0">
                        <div class="grid gap-3 sm:gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                            <!-- Add Photo Card (tree mode only, first page only) -->
                            <div v-if="selectedTree && tableData.current_page === 1 && tableData.data.length !== 0"
                                class="flex flex-col justify-center items-center min-h-[200px] sm:min-h-[300px]
                               text-brand-500 
                               bg-brand-500/5 dark:bg-brand-700/10
                               rounded-xl sm:rounded-2xl 
                               border-2 border-brand-500/30 dark:border-brand-700/50 border-dashed
                               hover:bg-brand-500/10 dark:hover:bg-brand-700/20
                               hover:border-brand-500
                               cursor-pointer transition-all duration-200
                               active:scale-95" @click="openCreateForm"
                                v-has-permission="{ props: $page.props, permissions: ['photos.create'] }">
                                <i class="pi pi-plus text-3xl sm:text-4xl mb-2"></i>
                                <span class="text-sm sm:text-base font-medium">Add Photos</span>
                            </div>

                            <!-- Photo Cards -->
                            <div v-for="image in tableData.data" :key="image.id" class="w-full relative group cursor-pointer rounded-xl sm:rounded-2xl overflow-hidden
                               shadow-sm hover:shadow-md transition-shadow duration-200"
                                @click="selectPhoto(image.id)">
                                <!-- Optional badge to show tree for explore mode -->
                                <div v-if="!selectedTree"
                                    class="absolute top-3 right-3 bg-black/70 text-white text-xs px-2 py-1 rounded-full z-10">
                                    Tree #{{ image.tree_id }}
                                </div>

                                <!-- Image -->
                                <img :src="image.url" class="w-full h-[200px] sm:h-[300px] object-cover"
                                    :alt="`Photo #${image.id}`" loading="lazy" />

                                <!-- Selection Checkmark -->
                                <div v-if="selected.has(image.id)" class="absolute top-3 left-3 bg-brand-500 text-white rounded-full w-7 h-7 
                                    flex items-center justify-center shadow-lg z-10 animate-fade-in">
                                    <i class="pi pi-check text-sm font-bold"></i>
                                </div>

                                <!-- Hover Overlay with Actions -->
                                <div :class="selected.has(image.id) ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                    class="absolute inset-0 bg-linear-to-t from-black/80 via-black/40 to-transparent
                                    transition-opacity duration-200 flex items-end justify-end p-3">
                                    <div v-if="!selected.has(image.id)" class="flex gap-2">
                                        <Button class="w-10! h-10! p-0! min-w-0! rounded-full! bg-white/90! dark:bg-gray-800/90! 
                                            text-gray-700! dark:text-gray-200! hover:bg-white! dark:hover:bg-gray-700!
                                            border-0! shadow-lg" size="small" @click.stop="openPreview(image)"
                                            icon="pi pi-eye" v-tooltip.top="'Preview'"
                                            v-has-permission="{ props: $page.props, permissions: ['photos.view'] }" />
                                        <Button class="w-10! h-10! p-0! min-w-0! rounded-full! bg-white/90! dark:bg-gray-800/90! 
                                            text-gray-700! dark:text-gray-200! hover:bg-white! dark:hover:bg-gray-700!
                                            border-0! shadow-lg" size="small" @click.stop="openEditForm(image)"
                                            icon="pi pi-pencil" v-tooltip.top="'Edit'"
                                            v-has-permission="{ props: $page.props, permissions: ['photos.edit'] }" />
                                        <Button class="w-10! h-10! p-0! min-w-0! rounded-full! bg-error-500/90! 
                                            text-white! hover:bg-error-600!
                                            border-0! shadow-lg" size="small" @click.stop="onDeleteClick(image.id)"
                                            icon="pi pi-trash" v-tooltip.top="'Delete'"
                                            v-has-permission="{ props: $page.props, permissions: ['photos.delete'] }" />
                                    </div>
                                </div>

                                <!-- Photo Info Badge (optional, shows on hover) -->
                                <div v-if="image.caption && !selected.has(image.id)"
                                    class="absolute bottom-0 left-0 right-0 p-3 bg-linear-to-t from-black/80 to-transparent
                                    opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                    <p class="text-white text-xs sm:text-sm line-clamp-2">
                                        {{ image.caption }}
                                    </p>
                                </div>

                                <!-- Processing Badge -->
                                <div v-if="image.status === 'processing'" class="absolute top-3 right-3 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium
                                    flex items-center gap-1">
                                    <i class="pi pi-spin pi-spinner"></i>
                                    Processing
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No photos for this tree (this is the one that was failing for you) -->
                    <div v-if="selectedTree && tableData.data.length === 0" class="text-center py-12">
                        <i class="pi pi-images text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <p class="text-lg text-gray-500 mb-4">
                            No photos found for this tree.
                        </p>
                        <Button icon="pi pi-plus" label="Add First Photo" @click="openCreateForm"
                            v-has-permission="{ props: $page.props, permissions: ['photos.create'] }" />
                    </div>
                </div>
            </template>

            <!-- Pagination -->
            <div v-if="tableData && tableData.links && tableData.data.length !== 0 && selectedTree" class="mt-8 flex flex-col items-center gap-4 sm:flex-row sm:justify-between 
                    pt-6 border-t border-gray-200 dark:border-gray-700">
                <!-- Per-page selector -->
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        Photos per page:
                    </span>
                    <Dropdown v-model="perPage" :options="perPageOptions" optionLabel="label" optionValue="value"
                        class="w-20" @change="onPerPageChange" />
                </div>

                <!-- Pagination links -->
                <nav class="inline-flex flex-wrap gap-1 sm:gap-2 justify-center">
                    <Link v-for="link in tableData.links" :key="link.label + (link.url ?? '')" :href="link.url || '#'"
                        class="px-3 py-2 text-xs sm:text-sm rounded-lg border transition-all duration-150" :class="[
                            link.active
                                ? 'bg-brand-500 text-white border-brand-500 font-medium shadow-sm'
                                : link.url
                                    ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-brand-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700'
                                    : 'bg-gray-100 text-gray-400 border-gray-200 dark:bg-gray-800 dark:text-gray-500 dark:border-gray-700 cursor-not-allowed'
                        ]" v-html="link.label" />
                </nav>
            </div>
        </div>
    </ComponentCard>
    <PhotoForm v-if="formVisible" v-model:visible="formVisible" routeResource="photos" :action="formAction"
        :dataRow="formRow" :treeId="initialTreeId" @updated="reloadTable" @created="reloadTable" />
    <PhotoPreview v-model:visible="previewVisible" :photo="previewPhoto" />
</template>

<script setup>
import ComponentCard from '@/Components/Common/ComponentCard.vue'
import FormField from '@/Components/Primitives/FormField.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed, onUnmounted, reactive, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PhotoForm from './Partials/PhotoForm.vue'
import { useCrudOperations } from '@/Composables/useCrudOperations'
import PhotoPreview from './Partials/PhotoPreview.vue'
import { useDateParser } from '@/Composables/useDateParser'

defineOptions({
    layout: AuthenticatedLayout,
})

const page = usePage()


const props = defineProps({
    tableData: {
        type: Object,
        default: null,
    },
    selectedTree: {
        type: Object,
        default: null,
    },
    initialTreeId: {
        type: Number,
        default: null,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    speciesOptions: {
        type: Array,
        default: () => [],
    },
    neighborhoodOptions: {
        type: Array,
        default: () => [],
    },
});

const perPage = ref(props.tableData?.per_page ?? 10);

const { parseDate } = useDateParser();

const speciesFilterOptions = computed(() => {
    return (props.speciesOptions || []).map(s => ({
        ...s,
        label: `${s.common_name} (${s.latin_name}) (${s.photos_count ?? 0})`,
    }))
})

const neighborhoodFilterOptions = computed(() => {
    return (props.neighborhoodOptions || []).map(n => ({
        ...n,
        label: `${n.name} (${n.photos_count ?? 0})`,
    }))
})

const filters = reactive({
    tree_id: props.filters.tree_id ?? props.initialTreeId ?? null,
    search: props.filters.search ?? '',
    neighborhood_id: props.filters.neighborhood_id ?? null,
    species_id: props.filters.species_id ?? null,
    date_from: parseDate(props.filters.date_from),
    date_to: parseDate(props.filters.date_to),
    sort: props.filters.sort ?? 'recent',
});

const perPageOptions = [
    { label: '5', value: 5 },
    { label: '10', value: 10 },
    { label: '25', value: 25 },
    { label: '50', value: 50 },
    { label: '100', value: 100 },
]

const sortOptions = [
    { label: 'Most Recent', value: 'recent' },
    { label: 'Oldest First', value: 'oldest' },
    { label: 'Tree ID ↑', value: 'tree_id_asc' },
    { label: 'Tree ID ↓', value: 'tree_id_desc' },
];

const toDateString = (value) => {
    if (!value) return undefined
    // YYYY-MM-DD
    if (value instanceof Date) {
        return value.toISOString().slice(0, 10)
    }
    return value // if somehow already string
}

const buildQuery = () => {
    return {
        tree_id: filters.tree_id || undefined,
        search: filters.search || undefined,
        neighborhood_id: filters.neighborhood_id || undefined,
        species_id: filters.species_id || undefined,
        date_from: toDateString(filters.date_from),
        date_to: toDateString(filters.date_to),
        sort: filters.sort || undefined,
        per_page: perPage.value,
    }
}

const onPerPageChange = () => {
    router.get(
        route('photos.index'),
        buildQuery(),
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
};

const resetFilters = () => {
    filters.tree_id = null;
    filters.search = '';
    filters.neighborhood_id = null;
    filters.species_id = null;
    filters.date_from = null;
    filters.date_to = null;
    filters.sort = 'recent';

    loadPhotos();
};


const polling = ref(false)
let pollTimer = null

const { deleteOne, massDelete } = useCrudOperations('photos');
const selected = ref(new Set())

// --- photos form state ---
const formVisible = ref(false);
const formAction = ref('');      // 'Create' or 'Edit'
const formRow = ref(null);       // current row

// --- preview state ---
const previewVisible = ref(false)
const previewPhoto = ref(null)


const hasProcessingPhotos = () => {
    if (!props.tableData || !Array.isArray(props.tableData.data)) return false
    return props.tableData.data.some(photo => photo.status === 'processing')
}

const startPolling = () => {
    if (polling.value) return
    polling.value = true

    pollTimer = setInterval(() => {
        // If nothing is processing anymore, stop polling
        if (!hasProcessingPhotos()) {
            stopPolling()
            return
        }

        router.reload({
            only: ['tableData'],
            preserveState: true,
            preserveScroll: true,
        })
    }, 3000) // every 3s – tweak as you like
}

const stopPolling = () => {
    polling.value = false
    if (pollTimer) {
        clearInterval(pollTimer)
        pollTimer = null
    }
}

onUnmounted(() => {
    stopPolling()
})

watch(
    () => props.tableData,
    () => {
        if (hasProcessingPhotos()) {
            startPolling()
        } else {
            stopPolling()
        }
    },
    { immediate: true }
)

const openPreview = (photo) => {
    previewPhoto.value = photo
    previewVisible.value = true
}

const openCreateForm = () => {
    formRow.value = null;
    formAction.value = 'Create';
    formVisible.value = true;
};

const openEditForm = (row) => {
    formRow.value = row;
    formAction.value = 'Edit';
    formVisible.value = true;
};

// optional: reload table when form finishes
const reloadTable = () => {
    router.reload({ only: ['tableData'] });
};

const onDeleteClick = (id) => {
    deleteOne(id, () => {
        // table reload is internal to the component
        reloadTable();
    });
}

const onMassDeleteClick = () => {
    const items = Array.from(selected.value);
    massDelete(items, () => {
        selected.value = new Set();
        reloadTable();
    });
};

const selectPhoto = (photoId) => {
    if (!selected.value) {
        selected.value = new Set()
    }

    const set = selected.value

    if (set.has(photoId)) {
        set.delete(photoId)
    } else {
        set.add(photoId)
    }

    // Reassign to trigger reactivity (important)
    selected.value = new Set(set)

}

const loadPhotos = () => {
    // You *can* allow empty tree_id now because we have global search,
    // but if you want to force a filter, you can keep the guard.
    router.get(
        route('photos.index'),
        buildQuery(),
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
};

</script>
<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: scale(0.8);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fade-in 0.2s ease-out;
}

.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>