<template>
    <ComponentCard class="w-full flex-1 flex flex-col" title="Manage Tree Photos">
        <form class="flex flex-col sm:flex-row sm:flex-nowrap gap-3" @submit.prevent="loadPhotos">
            <div class="relative flex-1 sm:flex-initial">
                <FormField v-model="formData.tree_id" class="w-full sm:max-w-3xs" name="tree_id" component="Number"
                    label="Enter Tree ID" :displayErrors="true" placeholder="E.g. 123" :useGrouping="false"
                    @input="formData.tree_id = $event.value" />
            </div>
            <Button class="text-nowrap h-10 sm:h-10! sm:mt-auto" icon="pi pi-sync" label="Load Photos" type="submit"
                :disabled="formData.tree_id === null || formData.tree_id === ''"
                v-has-permission="{ props: $page.props, permissions: ['photos.view'] }" />

            <Button v-if="selected.size > 0" v-has-permission="{ props: $page.props, permissions: ['photos.delete'] }"
                severity="danger" class="text-nowrap h-10 sm:h-10! sm:mt-auto" icon="pi pi-trash" label="Delete"
                @click="onMassDeleteClick" />

        </form>
        <div class="justify-start mt-6">
            <template v-if="!tableData">
                <p class="text-lg sm:text-xl text-gray-500">
                    Enter the ID of a tree and click "Load Photos" to view its photos.
                </p>
            </template>
            <template v-else-if="!selectedTree && props.initialTreeId">
                <p class="text-lg sm:text-xl text-gray-500">
                    The tree with ID: {{ props.initialTreeId }} does not exist.
                </p>
            </template>
            <template v-else>
                <div v-if="selectedTree">
                    <div class="mb-4">
                        <span class="text-base sm:text-xl text-black dark:text-white ">
                            Showing photos for tree <strong>#{{ selectedTree.id }}</strong>
                            ({{ selectedTree.species_label ?? selectedTree.id }}).
                        </span>
                    </div>
                    <div class="grid gap-3 sm:gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        <div v-if="props.tableData.current_page == 1" class="flex flex-col justify-center items-center min-h-[200px] sm:min-h-[300px]
                        text-brand-500 
                        bg-brand-500/5 dark:bg-brand-700/10
                        rounded-xl sm:rounded-2xl 
                        border border-brand-700 dark:border-brand-700 border-dashed
                        hover:bg-brand-500/10 dark:hover:bg-brand-700/20
                        hover:border-brand-500
                        cursor-pointer transition
                        active:scale-95" @click="openCreateForm">
                            <i class="pi pi-plus text-2xl sm:text-3xl"></i>
                            <span class="mt-2 text-sm sm:text-base">Add Photo</span>
                        </div>
                        <div v-for="image in props.tableData.data" :key="image.id"
                            class="w-full relative group cursor-pointer" @click="selectPhoto(image.id)">
                            <!-- Image -->
                            <img :src="image.url"
                                class="w-full h-[200px] sm:h-[300px] rounded-xl sm:rounded-2xl object-cover"
                                :alt="`Photo #${image.id}`" />
                            <div :class="selected.has(image.id) ? 'opacity-100' : 'group-hover:opacity-100'"
                                class="absolute inset-0 bg-white/70 dark:bg-black/70 rounded-xl sm:rounded-2xl opacity-0 transition-opacity duration-200 flex items-end justify-end p-3">
                                <!-- Top-left selection indicator -->
                                <div v-if="selected.has(image.id)"
                                    class="absolute top-2 left-2 bg-brand-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg">
                                    ✓
                                </div>
                                <!-- Option buttons -->
                                <div v-else class="flex flex-col gap-2">
                                    <Button
                                        class="border-transparent! bg-gray-500! hover:bg-gray-600! w-9! h-9! p-0! min-w-0! opacity-80 text-white"
                                        size="small" @click.stop="openEditForm(image)" icon="pi pi-pencil"
                                        v-has-permission="{ props: $page.props, permissions: ['photos.edit'] }" />
                                    <Button
                                        class="border-transparent! bg-gray-500! hover:bg-gray-600! w-9! h-9! p-0! min-w-0! opacity-80 text-white"
                                        size="small" @click="onDeleteClick(image.id)" icon="pi pi-trash"
                                        v-has-permission="{ props: $page.props, permissions: ['photos.delete'] }" />
                                    <Button
                                        class="border-transparent! bg-gray-500! hover:bg-gray-600! w-9! h-9! p-0! min-w-0! opacity-80 text-white"
                                        size="small" @click.stop="openPreview(image)" icon="pi pi-eye"
                                        v-has-permission="{ props: $page.props, permissions: ['photos.view'] }" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </template>
            <!-- Pagination -->
            <div v-if="props.tableData && props.tableData.links && props.tableData.links.length > 0 && selectedTree"
                class="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
                <!-- Per-page selector -->
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                        Photos per page:
                    </span>
                    <Dropdown v-model="perPage" :options="perPageOptions" optionLabel="label" optionValue="value"
                        class="w-32" @change="onPerPageChange" />
                </div>

                <!-- Pagination links -->
                <nav class="inline-flex flex-wrap gap-1 sm:gap-2">
                    <Link v-for="link in props.tableData.links" :key="link.label + (link.url ?? '')"
                        :href="link.url || '#'"
                        class="px-2 py-1 sm:px-3 sm:py-1.5 text-xs sm:text-sm rounded-md border transition" :class="[
                            link.active
                                ? 'bg-brand-500 text-white border-brand-500'
                                : link.url
                                    ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800'
                                    : 'bg-gray-100 text-gray-400 border-gray-200 dark:bg-gray-800 dark:text-gray-500 dark:border-gray-700 cursor-default'
                        ]" v-html="link.label" />
                </nav>
            </div>

        </div>
    </ComponentCard>
    <PhotoForm v-if="formVisible" v-model:visible="formVisible" routeResource="photos" :action="formAction"
        :dataRow="formRow" :treeId="props.initialTreeId" @updated="reloadTable" @created="reloadTable" />
    <PhotoPreview v-model:visible="previewVisible" :photo="previewPhoto" />
</template>

<script setup>
import ComponentCard from '@/Components/Common/ComponentCard.vue'
import FormField from '@/Components/Primitives/FormField.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed, reactive, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PhotoForm from './Partials/PhotoForm.vue'
import { useCrudOperations } from '@/Composables/useCrudOperations'
import PhotoPreview from './Partials/PhotoPreview.vue'
import { MultiSelect } from 'primevue'

defineOptions({
    layout: AuthenticatedLayout,
})

const page = usePage()


const props = defineProps({
    tableData: {
        type: Object,
        default: null
    },
    selectedTree: {
        type: Object,
        default: null
    },
    initialTreeId: {
        type: Number,
        default: null
    },
})


const perPage = ref(props.tableData?.per_page ?? 10)

const perPageOptions = [
    { label: '5', value: 5 },
    { label: '10', value: 10 },
    { label: '25', value: 25 },
    { label: '50', value: 50 },
    { label: '100', value: 100 },
]

const onPerPageChange = () => {
    if (!formData.tree_id) return

    router.get(
        route('photos.index'),
        {
            tree_id: formData.tree_id,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    )
}

const { deleteOne, massDelete } = useCrudOperations('photos');
const selected = ref(new Set())

// --- photos form state ---
const formVisible = ref(false);
const formAction = ref('');      // 'Create' or 'Edit'
const formRow = ref(null);       // current row

// --- preview state ---
const previewVisible = ref(false)
const previewPhoto = ref(null)


const formData = reactive({
    tree_id: page.props.initialTreeId ?? null,
})

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
        refreshData();
    });
}

const onMassDeleteClick = () => {
    const items = Array.from(selected.value);
    massDelete(items, () => {
        selected.value = new Set();
        refreshData();
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

    // Reassign to trigger reactivity (important!)
    selected.value = new Set(set)

    console.log('Selected:', [...selected.value])
}

const onAfterDelete = () => {
    reloadTable();
};

const loadPhotos = () => {
    if (!formData.tree_id) return

    router.get(
        route('photos.index'),
        {
            tree_id: formData.tree_id,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    )
}
</script>
