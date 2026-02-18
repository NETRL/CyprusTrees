<!-- MapPanelContent.vue -->
<template>
    <EventPanel v-if="ui.activePanel === MAP_PANELS.EVENTS" :selectedTree="selected"/>
    <MapTreeForm v-else-if="ui.activePanel === MAP_PANELS.TREE_FORM" :visible="formVisible" @update:visible="emit('update:formVisible', $event)" routeResource="trees"
        :action="formAction" :markerLatLng="markerLatLng" :dataRow="selected" />
    <NeighborhoodCardContent v-else-if="ui.activePanel === MAP_PANELS.NEIGHBORHOOD"
        :activeNeighborhood="selectedNeighborhood" :stats="neighborhoodStats || {}"
        @clearSelection="emit('clearSelection')" />
    <TreeCardContent v-else-if="ui.activePanel === MAP_PANELS.TREE" :hovered="hovered" :selected="selected"
        :isHovered="isHovered" :isSelected="isSelected" @editClick="emit('editClick')"
        @clearSelection="emit('clearSelection')" />
</template>

<script setup>
import { useMapUiState, MAP_PANELS } from '@/Lib/Map/useMapUiState'
import TreeCardContent from '@/Components/Map/Partials/TreeCardContent.vue'
import MapTreeForm from '@/Components/Map/Partials/MapTreeForm.vue'
import NeighborhoodCardContent from '@/Components/Map/Partials/NeighborhoodCardContent.vue'
import EventPanel from '@/Components/Map/Partials/EventPanel.vue'

defineProps({
    hovered: { type: Object, default: null },
    selected: { type: Object, default: null },
    markerLatLng: { type: Object, default: null },
    selectedNeighborhood: { type: Object, default: null },
    neighborhoodStats: { type: Object, default: null },
    formAction: { type: String, default: '' },
    isHovered: { type: Boolean, default: false },
    isSelected: { type: Boolean, default: false },
    formVisible: { type: Boolean, default: false },
})

const emit = defineEmits(['update:formVisible', 'editClick', 'clearSelection'])
const { ui } = useMapUiState()
</script>