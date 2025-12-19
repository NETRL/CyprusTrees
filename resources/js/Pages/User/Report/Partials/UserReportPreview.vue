<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Report Details" @update:visible="emit('update:visible', $event)"
        class="dark:bg-gray-900! select-none">
        <div class="grid grid-cols-12 w-full gap-3">
            <div class="col-span-6">
                <FormField v-model="dataRow.report_type_id" label="Report Id" disabled/>
            </div>
            <div class="col-span-6">
                <FormField v-model="dataRow.type_label" label="Type" disabled/>
            </div>
            <div class="col-span-12">
                <FormField v-model="dataRow.tree_label" label="Tree" class="select-none" disabled/>
            </div>
            <div class="col-span-6">
                <FormField v-model="dataRow.lat" label="Latitude" disabled/>
            </div>

            <div class="col-span-6">
                <FormField v-model="dataRow.lon" label="Longitude" disabled/>
            </div>
            <div class="col-span-12">
                <FormField component="Textarea" v-model="dataRow.description" label="Description" disabled/>
            </div>

            <div class="col-span-12">
                <FormField v-model="dataRow.status" label="Status" disabled/>
            </div>
            <div class="col-span-6">
                <FormField v-model="createdAt" label="Created At" disabled/>
            </div>
            <div class="col-span-6">
                <FormField v-model="resolvedAt" label="Resolved At" disabled/>
            </div>

        </div>
        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Close" @click="closeForm" />
        </template>
    </Dialog>
</template>

<script setup>
    
import FormField from '@/Components/Primitives/FormField.vue'
import { useDateFormatter } from '@/Composables/useDateFormatter';
import { useDateParser } from '@/Composables/useDateParser'
import { computed } from 'vue'

// props & emits
const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    dataRow: {
        type: Object,
        default: null,
    }
})

const createdAt = computed(() => formatDate(props.dataRow?.created_at));
const resolvedAt = computed(() => formatDate(props.dataRow?.resolved_at) ?? '-');

console.log(props.dataRow)

const emit = defineEmits(['update:visible'])

const { formatDate } = useDateFormatter();

// methods
const closeForm = () => {
    emit('update:visible', false)
}
</script>
