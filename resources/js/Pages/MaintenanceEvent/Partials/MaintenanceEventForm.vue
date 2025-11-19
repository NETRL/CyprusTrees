<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Maintenance Event Details" @show="initForm"
        @update:visible="emit('update:visible', $event)" class="dark:bg-gray-900! select-none">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <!-- Tree -->
            <div class="col-span-12">
                <FormField component="Dropdown" filter v-model="formData.tree_id" :displayErrors="displayErrors" label="Name" name="tree_id" 
                :options="treeOptions"  optionLabel="label" optionValue="value"/>
            </div>
            <!-- Type -->
            <div class="col-span-12">
                <FormField component="Dropdown" filter v-model="formData.type_id" :displayErrors="displayErrors" label="Type" name="type_id" 
                 :options="typeOptions"  optionLabel="label" optionValue="value"/>
            </div>
            <!-- Performed By -->
            <div class="col-span-6">
                <FormField component="Dropdown" filter v-model="formData.performed_by" :displayErrors="displayErrors" label="Performed By"
                    name="performed_by"  :options="userOptions"  optionLabel="label" optionValue="value"/>
            </div>
            <!-- Performed At -->
            <div class="col-span-6">
                <FormField component="Calendar" v-model="formData.performed_at" :displayErrors="displayErrors" label="Performed At"
                    name="performed_at" />
            </div>
            <!-- Quantity -->
            <div class="col-span-12">
                <FormField v-model="formData.quantity" :displayErrors="displayErrors" label="Quantity"
                    name="quantity" />
            </div>
            <!-- Cost -->
            <div class="col-span-12">
                <FormField v-model="formData.cost" :displayErrors="displayErrors" label="Cost" name="cost" />
            </div>
            <!-- Notes -->
            <div class="col-span-12">
                <FormField v-model="formData.notes" :displayErrors="displayErrors" label="Notes" name="quantity" />
            </div>
        </form>

        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="closeForm" />
            <Button :label="action" class="p-button-text" icon="pi pi-check" @click="submit" />
        </template>
    </Dialog>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import FormField from '@/Components/Primitives/FormField.vue'
import { useDateParser } from '@/Composables/useDateParser'

// props & emits
const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    dataRow: {
        type: Object,
        default: null,
    },
    action: {
        type: String,
        default: '',
    },
    routeResource: {
        type: String,
        required: true,
    },
    trees: {
        type: Array,
         default: () => [],
    },
    types: {
        type: Array,
         default: () => [],
    },
    users: {
        type: Array,
         default: () => [],
    },
})

const emit = defineEmits(['update:visible'])

const { parseDate } = useDateParser();

const treeOptions = computed(() =>
    props.trees.map(index => ({
        value: index.id,
        label:
            index.address
                ? `#${index.id} – ${index.address}`
                : index.species?.common_name
                    ? `#${index.id} – ${index.species.common_name} (${index.lat}, ${index.lon})`
                    : `#${index.id} – (${index.lat}, ${index.lon})`,
    }))
)

const typeOptions = computed(() =>
    (props.types ?? []).map(index => {
        const name = index.name ?? '';

        const label = name

        return {
            value: index.type_id,
            label,
        };
    })
);


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


// state
const formData = reactive({
    event_id: null,
    tree_id: null,
    type_id: null,
    performed_by: null,
    performed_at: new Date(),
    quantity: '',
    cost: '',
    notes: '',
})

const displayErrors = ref(false)

// methods
const closeForm = () => {
    emit('update:visible', false)
}

const initForm = () => {
    const row = props.dataRow

    displayErrors.value = false

    formData.event_id = row?.event_id ?? null
    formData.tree_id = row?.tree_id ?? null
    formData.type_id = row?.type_id ?? null
    formData.performed_by = row?.performed_by ?? null
    formData.performed_at = parseDate(row?.performed_at)
    formData.quantity = row?.quantity ?? ''
    formData.cost = row?.cost ?? ''
    formData.notes = row?.notes ?? ''
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
        router.patch(route(props.routeResource + '.update', formData.event_id), { ...formData }, {
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
