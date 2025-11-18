<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="Planting Event Details" @show="initForm"
        @update:visible="emit('update:visible', $event)" class="dark:bg-gray-900!">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
            <!-- Tree -->
            <div class="col-span-12">
                <FormField component="Dropdown" v-model="formData.tree_id" :displayErrors="displayErrors" label="Tree"
                    name="tree_id" :options="treeOptions" optionLabel="label" optionValue="value" filter />
            </div>
            <!-- Campaign -->
            <div class="col-span-12">
                <FormField component="Dropdown" v-model="formData.campaign_id" :displayErrors="displayErrors"
                    label="Campaign" name="campaign_id" :options="campaignOptions" optionLabel="label"
                    optionValue="value" filter />
            </div>
            <!-- Planted By -->
            <div class="col-span-6">
                <FormField component="Dropdown" v-model="formData.planted_by" :displayErrors="displayErrors"
                    label="Planted By" name="planted_by" :options="userOptions" optionLabel="label" optionValue="value"
                    filter />
            </div>
            <!-- Planted At -->
            <div class="col-span-6">
                <FormField component="Calendar" v-model="formData.planted_at" :displayErrors="displayErrors"
                    label="Planted At" name="planted_at" />
            </div>
            <!-- Method -->
            <div class="col-span-12">
                <FormField v-model="formData.method" :displayErrors="displayErrors" label="Method" name="method" />
            </div>
            <!-- Notes -->
            <div class="col-span-12">
                <FormField v-model="formData.notes" :displayErrors="displayErrors" label="Notes" name="notes" />
            </div>

        </form>

        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="closeForm" />
            <Button :label="action" class="p-button-text" icon="pi pi-check" @click="submit" />
        </template>
    </Dialog>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import FormField from '@/Components/Primitives/FormField.vue'
import { useDateFormatter } from '@/Composables/useDateFormatter'

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
    campaigns: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    }
})

const { formatDate } = useDateFormatter();

const emit = defineEmits(['update:visible'])

// state
const formData = reactive({
    id: null,
    tree_id: null,
    campaign_id: null,
    planted_by: null,
    planted_at: null,
    method: '',
    notes: '',
})

const displayErrors = ref(false)

const treeOptions = computed(() =>
    props.trees.map(tree => ({
        value: tree.id,
        label:
            tree.address
                ? `#${tree.id} – ${tree.address}`
                : tree.species?.common_name
                    ? `#${tree.id} – ${tree.species.common_name} (${tree.lat}, ${tree.lon})`
                    : `#${tree.id} – (${tree.lat}, ${tree.lon})`,
    }))
)

const campaignOptions = computed(() =>
    (props.campaigns ?? []).map(c => {
        const start = c.start_date ?? '';
        const end = c.end_date ?? null;
        const sponsor = c.sponsor ?? null;

        const label = sponsor
            ? `${c.name} (${sponsor}) — ${formatDate(start)} → ${formatDate(end) ?? 'Ongoing'}`
            : `${c.name} — ${formatDate(start)} → ${formatDate(end) ?? 'Ongoing'}`;

        return {
            value: c.id,
            label,
        };
    })
);

const userOptions = computed(() => (props.users ?? []).map(u => {
    const firstName = u.first_name ?? '';
    const lastName = u.last_name ?? '';
    const roles = Array.isArray(u.roles) ? u.roles : [];

    const roleNames = roles.length ? roles.map(r => r.name).join(', ') : 'No role';

    const label = `${firstName} ${lastName} (${roleNames})`

    return {
        value: u.id,
        label,
    };
}))

// methods
const closeForm = () => {
    emit('update:visible', false)
}

const initForm = () => {
    displayErrors.value = false

    formData.id = props.dataRow?.id ?? null
    formData.tree_id = props.dataRow?.tree_id ?? null
    formData.campaign_id = props.dataRow?.campaign_id ?? null
    formData.planted_at = props.dataRow?.planted_at ?? null
    formData.planted_by = props.dataRow?.planted_by ?? null
    formData.method = props.dataRow?.method ?? ''
    formData.notes = props.dataRow?.notes ?? ''
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
        router.patch(route(props.routeResource + '.update', formData.id), { ...formData }, {
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
