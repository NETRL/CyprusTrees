<template>
    <ComponentCard class="w-full flex-1 flex flex-col" title="Manage Tree Photos">
        <form class="space-x-3 flex-nowrap max-sm:space-y-3" @submit.prevent="loadPhotos">
            <FormField v-model="formData.tree_id" class="max-w-3xs" name="tree_id" component="Number"
                label="Enter Tree ID" type="text" :displayErrors="true" placeholder="E.g. 123" :useGrouping="false" />

            <Button class="text-nowrap h-10!" icon="pi pi-sync" label="Load Photos" type="submit"
                :disabled="!formData.tree_id" />
        </form>

        <div class="flex justify-start align-middle mt-6">
            <template v-if="!tableData">
                <p class="text-xl text-gray-500">
                    Enter the ID of a tree and click “Load Photos” to view its photos.
                </p>
            </template>

            <template v-if="!selectedTree && formData.tree_id">
                <p class="text-xl text-gray-500">
                    The tree with ID: {{ formData.tree_id }} does not exist.
                </p>
            </template>

            <template v-else>
                <div>

                    <p class="mb-2 text-xl text-gray-600" v-if="selectedTree">
                        Showing photos for tree <strong>#{{ selectedTree.id }}</strong>
                        ({{ selectedTree.species_label ?? selectedTree.id }}).
                    </p>

                    <div class="m-5">

                        <div class="flex flex-col justify-center items-center w-60 h-[300px]
                        text-brand-500 
                        bg-brand-500/5 dark:bg-brand-700/10
                        rounded-2xl 
                        border border-brand-700 dark:border-brand-700 border-dashed
                        hover:bg-brand-500/10 dark:hover:bg-brand-700/20
                        hover:border-brand-500
                        cursor-pointer transition">
                            <i class="pi pi-plus"></i>
                            <span class="mt-2">Add Photo</span>
                        </div>
                    </div>

                </div>

            </template>
        </div>
    </ComponentCard>
</template>

<script setup>
import ComponentCard from '@/Components/Common/ComponentCard.vue'
import FormField from '@/Components/Primitives/FormField.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ReusableDataTable from '@/Components/ReusableDataTable.vue'
import { computed, reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

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

const formData = reactive({
    tree_id: page.props.initialTreeId ?? null,
})

const loadPhotos = () => {
    if (!formData.tree_id) return

    router.get(
        route('photos.index'),
        { tree_id: formData.tree_id },
        {
            preserveState: true,
            replace: true,
            onSuccess: () => {
                console.log(props.tableData)
                console.log(props.selectedTree)
                console.log(props.initialTreeId)

            }
        }
    )
}
</script>
