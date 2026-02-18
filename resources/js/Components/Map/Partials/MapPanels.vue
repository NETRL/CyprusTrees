<template>
    <!-- Desktop Card -->
    <aside :class="[
        'max-lg:hidden absolute right-2 top-2 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-2xl transition-all duration-300 ease-in-out z-50',
        'max-h-[calc(100vh-7rem)] overflow-hidden w-[420px] flex flex-col',
        {
            'opacity-100 translate-x-0 pointer-events-auto': isPanelOpen,
            'opacity-0 translate-x-4 pointer-events-none': !isPanelOpen,
        },
    ]">
        <MapTreeForm v-if="ui.activePanel === MAP_PANELS.TREE_FORM" v-model:visible="formVisible" routeResource="trees"
            :action="formAction" :markerLatLng="markerLatLng" :dataRow="props.selected" />
        <NeighborhoodCardContent v-else-if="ui.activePanel === MAP_PANELS.NEIGHBORHOOD"
            :activeNeighborhood="selectedNeighborhood" :stats="neighborhoodStats || {}"
            @clearSelection="emit('clearSelection')" />
        <TreeCardContent v-else-if="ui.activePanel === MAP_PANELS.TREE" :hovered="props.hovered"
            :selected="props.selected" :isHovered="isHovered" :isSelected="isSelected" @editClick="onEditClick"
            @clearSelection="emit('clearSelection')" />
    </aside>

    <!-- Mobile Bottom Sheet -->
    <BottomSheet v-model:state="treeSheetState" :showFab="false" :showBackdrop="false" :heightRatio="0.75">
        <div class="flex flex-col h-full px-5 pt-4 pb-1 w-full sm:items-center">
            <MapTreeForm v-if="ui.activePanel === MAP_PANELS.TREE_FORM" v-model:visible="formVisible"
                routeResource="trees" :action="formAction" :markerLatLng="markerLatLng" :dataRow="props.selected" />
            <NeighborhoodCardContent v-else-if="ui.activePanel === MAP_PANELS.NEIGHBORHOOD"
                :activeNeighborhood="selectedNeighborhood" :stats="neighborhoodStats || {}"
                @clearSelection="emit('clearSelection')" />
            <TreeCardContent v-else-if="ui.activePanel === MAP_PANELS.TREE" :hovered="props.hovered"
                :selected="props.selected" :isHovered="isHovered" :isSelected="isSelected" @editClick="onEditClick"
                @clearSelection="emit('clearSelection')" />
        </div>
    </BottomSheet>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useMapUiState, MAP_PANELS, MAP_MODES } from '@/Lib/Map/useMapUiState'
import TreeCardContent from '@/Components/Map/Partials/TreeCardContent.vue'
import BottomSheet from '@/Components/Map/Partials/BottomSheet.vue'
import MapTreeForm from '@/Components/Map/Partials/MapTreeForm.vue'
import NeighborhoodCardContent from '@/Components/Map/Partials/NeighborhoodCardContent.vue'

const emit = defineEmits(['cancelCreate', 'clearSelection'])

const props = defineProps({
    hovered: { type: Object, default: null },
    selected: { type: Object, default: null },
    markerLatLng: { type: Object, default: null },
    pinClickFlag: { type: Number, default: 0 },
    selectedNeighborhood: { type: Object, default: null },
    neighborhoodStats: { type: Object, default: null },
})

const { ui, isPanelOpen, openPanel, closePanel } = useMapUiState()

// ---- Local Form State ----
const formVisible = ref(false)
const formAction = ref('');      // 'Create' or 'Edit'
const isEditing = ref(false)

const isHovered = computed(() => props.hovered !== null && ui.activePanel !== MAP_PANELS.NEIGHBORHOOD)
const isSelected = computed(() => props.selected !== null)
const isCreating = computed(() => props.markerLatLng !== null)

const isPlanting = computed(() => ui.activeMode === MAP_MODES.PLANTING) //TODO?

// Panel routing, react to props and toggle composable
watch(
    () => props.selectedNeighborhood,
    (val) => {
        if ((val && !isSelected.value) && (!isEditing.value && !isCreating.value)) openPanel(MAP_PANELS.NEIGHBORHOOD)
        else if (!val && ui.activePanel === MAP_PANELS.NEIGHBORHOOD) closePanel()
    }
)

// when hovered or selected tree feature toggle the form.
watch(
    [isSelected, isHovered],
    ([selected, hovered]) => {
        if ((selected || hovered) && (!isEditing.value && !isCreating.value)) {
            openPanel(MAP_PANELS.TREE)
        } else if (!isEditing.value && !isCreating.value && !props.markerLatLng) {
            // only close if nothing else is holding the tree form panel open
            if (ui.activePanel === MAP_PANELS.TREE_FORM) closePanel()
        }
    }
)

// when we enter/exit create mode, toggle the form
watch(
    () => props.markerLatLng,
    (val) => {
        if (val) {
            formVisible.value = true
            formAction.value = 'Create'
            openPanel(MAP_PANELS.TREE_FORM)
            treeSheetState.value = 'mid'
        } else {
            formVisible.value = false
        }
    }
)

watch(
    () => props.pinClickFlag,
    () => {
        // only reopen if we’re in create mode (pin exists) and sheet is closed
        if (props.markerLatLng && treeSheetState.value === 'closed') {
            treeSheetState.value = 'mid'
        }
    }
)

watch(
    formVisible, (v) => {
        if (!v) {
            emit('cancelCreate')
            isEditing.value = false
            // if the data have been cleaned up close the panel
            if (!isSelected.value && !isHovered.value) closePanel()
        }
    }
)

// ---- Mobile Sheet ----
const treeSheetState = ref('closed')

watch(
    isPanelOpen,
    (open) => { treeSheetState.value = open ? 'mid' : 'closed' },
    { immediate: true }
)

// ---- Actions ----

const onEditClick = () => {
    if (!props.selected) return
    if (props.markerLatLng) emit('cancelCreate')
    isEditing.value = true
    formVisible.value = true
    formAction.value = 'Edit'
    openPanel(MAP_PANELS.TREE_FORM)
}
</script>
