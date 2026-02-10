<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Health Assessment Details" @show="initForm"
        @update:visible="emit('update:visible', $event)" class="dark:bg-gray-900! select-none">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <!-- Tree -->
            <div class="col-span-12">
                <FormField component="Dropdown" filter v-model="formData.tree_id" :displayErrors="displayErrors"
                    label="Tree" name="tree_id" :options="treeOptions" optionLabel="label" optionValue="value" />
            </div>
            <!-- Assessed By -->
            <div class="col-span-6">
                <FormField component="Dropdown" filter v-model="formData.assessed_by" :displayErrors="displayErrors"
                    label="Assessed By" name="assessed_by" :options="userOptions" optionLabel="label"
                    optionValue="value" />
            </div>
            <!-- Assessed At -->
            <div class="col-span-6">
                <FormField component="Calendar" v-model="formData.assessed_at" :displayErrors="displayErrors"
                    label="Assessed At" name="assessed_at" />
            </div>
            <!-- Health Status -->
            <div class="col-span-6">
                <FormField component="Dropdown" v-model="formData.health_status" :displayErrors="displayErrors"
                    label="Health Status" name="health_status" :options="healthStatusOptions" optionLabel="label"
                    optionValue="value" />
            </div>
            <!-- Pests Diseased -->
            <div class="col-span-6">
                <FormField v-model="formData.pests_diseases" :displayErrors="displayErrors" label="Pests / Diseases"
                    name="pests_diseases" />
            </div>
            <!-- Risk Score -->
            <div class="col-span-6">
                <FormField component="Number" v-model="formData.risk_score" :displayErrors="displayErrors"
                    @input="formData.risk_score = $event.value" label="Risk Score (0-1)" name="risk_score"
                    inputId="minmax-buttons" mode="decimal" showButtons :min="0" :max="1" :step="0.1" fluid />
            </div>
            <!-- Actions Recommended -->
            <div class="col-span-6">
                <FormField v-model="formData.actions_recommended" :displayErrors="displayErrors"
                    label="Actions Recommended" name="actions_recommended" />
            </div>
        </form>

        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="closeForm" />
            <Button :label="action" class="p-button-text" icon="pi pi-check" @click="submit" />
        </template>
    </Dialog>
</template>

<script setup>
import { computed, reactive, ref, toRef } from 'vue'
import { router } from '@inertiajs/vue3'
import FormField from '@/Components/Primitives/FormField.vue'
import { useDateParser } from '@/Composables/useDateParser'
import { useTreeOptions } from '@/Composables/useTreeOptions'

// props & emits
const props = defineProps({
    visible: { type: Boolean, default: false, },
    dataRow: { type: Object, default: null, },
    action: { type: String, default: '', },
    routeResource: { type: String, required: true, },
    trees: { type: Array, default: () => [], },
    users: { type: Array, default: () => [], },
    healthStatus: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:visible'])

const { parseDate } = useDateParser();

const treeOptions = useTreeOptions(toRef(props, 'trees'))

const userOptions = computed(() => (props.users ?? []).map(index => {
    const firstName = index.first_name ?? '';
    const lastName = index.last_name ?? '';
    const roles = Array.isArray(index.roles) ? index.roles : [];

    const roleNames = roles.length ? roles.map(r => r.name).join(', ') : 'No role';

    const label = `${firstName} ${lastName} (${roleNames})`

    return {
        value: index.id,
        label,
    };
}))

const healthStatusOptions = computed(() => props.healthStatus)

// state
const formData = reactive({
    assessment_id: null,
    tree_id: null,
    assessed_by: null,
    assessed_at: new Date(),
    health_status: '',
    pests_diseases: '',
    risk_score: null,
    actions_recommended: '',

})

const displayErrors = ref(false)

// methods
const closeForm = () => {
    emit('update:visible', false)
}

const initForm = () => {
    const row = props.dataRow

    displayErrors.value = false

    formData.assessment_id = row?.assessment_id ?? null
    formData.tree_id = row?.tree_id ?? null
    formData.type_id = row?.type_id ?? null
    formData.assessed_by = row?.assessed_by ?? null
    formData.assessed_at = parseDate(row?.assessed_at)
    formData.health_status = row?.health_status ?? ''
    formData.pests_diseases = row?.pests_diseases ?? ''
    formData.risk_score = row?.risk_score ?? null
    formData.actions_recommended = row?.actions_recommended ?? ''
}

const submit = () => {
    if (props.action === 'Create') {
        router.post(route(props.routeResource + '.store'), { ...formData }, {
            preserveScroll: true,
            onSuccess: () => {
                closeForm()
            },
            onFinish: () => {
                displayErrors.value = true
            },
        })
    } else if (props.action === 'Edit') {
        router.patch(route(props.routeResource + '.update', formData.assessment_id), { ...formData }, {
            preserveScroll: true,
            onSuccess: () => {
                closeForm()
            },
            onFinish: () => {
                displayErrors.value = true
            },
        })
    }
}
</script>
