<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true"
        :style="{ width: '520px', maxHeight: '80%' }" :visible="visible" header="Planted Tree Details"
        @show="initForm" @update:visible="emit('update:visible', $event)" class="dark:bg-gray-900! select-none mx-2">
        <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">

            <div class="col-span-12">
                <FormField v-model="formData.id" :displayErrors="displayErrors" label="Planting ID"
                    name="id" disabled/>
            </div>

            <div class="col-span-12">
                <FormField v-model="formData.tree_id" :displayErrors="displayErrors" label="Tree ID" name="tree_id"
                    disabled/>
            </div>

            <div class="col-span-6">
                <FormField component="Dropdown" v-model="formData.planted_by" :displayErrors="displayErrors"
                    label="Planted By" name="planted_by" :options="userOptions" optionLabel="label" optionValue="value"
                    filter />
            </div>

            <div class="col-span-6">
                <FormField component="Calendar" v-model="formData.planted_at" :displayErrors="displayErrors"
                    label="Planted At" name="planted_at" showTime />
            </div>

            <div class="col-span-12">
                <FormField v-model="formData.planting_method" :displayErrors="displayErrors" label="Planting Method" name="planting_method" />
            </div>

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
import { reactive, ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import FormField from "@/Components/Primitives/FormField.vue";
import { useDateFormatter } from "@/Composables/useDateFormatter";
import { useDateParser } from "@/Composables/useDateParser";


const props = defineProps({
    visible: { type: Boolean, default: false },
    dataRow: { type: Object, default: null },
    action: { type: String, default: "" },
    routeResource: { type: String, required: true },

    users: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:visible", "updated"]);

const displayErrors = ref(false);
const { formatDate } = useDateFormatter();
const { parseDate } = useDateParser();

watch(() => props.dataRow, v => {
    console.log(v)

})

const formData = reactive({
    id: null,
    tree_id: null,
    planted_by: null,
    planted_at: null,
    planting_method: "",
    notes: "",
});

const userOptions = computed(() =>
    (props.users ?? []).map((u) => {
        const firstName = u.first_name ?? "";
        const lastName = u.last_name ?? "";
        const roles = Array.isArray(u.roles) ? u.roles : [];
        const roleNames = roles.length ? roles.map((r) => r.name).join(", ") : "No role";
        return { value: u.id, label: `${firstName} ${lastName} (${roleNames})` };
    })
);

const closeForm = () => emit("update:visible", false);

const initForm = () => {
    const row = props.dataRow;
    displayErrors.value = false;

    formData.id = row?.id ?? null;
    formData.tree_id = row?.tree_id ?? null;
    formData.planted_by = row?.planted_by ?? null;
    formData.planted_at = parseDate(row?.planted_at);
    formData.planting_method = row?.planting_method ?? "";
    formData.notes = row?.notes ?? "";
};

const submit = () => {
    const payload = { ...formData };
    router.patch(route(props.routeResource + ".update", formData.id), payload, {
        preserveScroll: true,
        onSuccess: () => {
            emit("updated");
            closeForm();
        },
        onFinish: () => (displayErrors.value = true),
    });
};
</script>
