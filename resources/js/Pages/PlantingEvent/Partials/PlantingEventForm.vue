<template>
  <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '520px', maxHeight: '80%' }"
    :visible="visible" header="Planting Event Details" @show="initForm" @update:visible="emit('update:visible', $event)"
    class="dark:bg-gray-900! select-none mx-2">
    <form class="grid grid-cols-12 w-full gap-3" @submit.prevent="submit">
      <div class="col-span-12">
        <FormField component="Dropdown" v-model="formData.status" :displayErrors="displayErrors" label="Status"
          name="status" :options="statusOptions" optionLabel="label" optionValue="value" />
      </div>

      <div class="col-span-12">
        <FormField component="Dropdown" v-model="formData.campaign_id" :displayErrors="displayErrors" label="Campaign"
          name="campaign_id" :options="campaignOptions" optionLabel="label" optionValue="value" filter />
      </div>

      <div class="col-span-12">
        <FormField component="Dropdown" v-model="formData.neighborhood_id" :displayErrors="displayErrors"
          label="Neighborhood" name="neighborhood_id" :options="neighborhoodOptions" optionLabel="label"
          optionValue="value" filter />
      </div>

      <div class="col-span-12">
        <FormField component="Dropdown" v-model="formData.assigned_to" :displayErrors="displayErrors"
          label="Assigned To" name="assigned_to" :options="userOptions" optionLabel="label" optionValue="value"
          filter />
      </div>

      <div class="col-span-6">
        <FormField component="Calendar" v-model="formData.started_at" :displayErrors="displayErrors" label="Started At"
          name="started_at" showTime />
      </div>

      <div class="col-span-6">
        <FormField component="Calendar" v-model="formData.completed_at" :displayErrors="displayErrors"
          label="Completed At" name="completed_at" showTime />
      </div>

      <div class="col-span-12 border-y border-gray-100 dark:border-gray-800 py-3 my-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
            Geographic Location
          </span>
          <Button label="Open Map Picker" icon="pi pi-map" class="p-button-sm p-button-secondary p-button-text"
            @click="show = true" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="p-field">
            <small class="block text-gray-500 mb-1">Latitude</small>
            <InputText v-model="formData.lat" class="w-full p-inputtext-sm" readonly placeholder="0.000000" />
          </div>
          <div class="p-field">
            <small class="block text-gray-500 mb-1">Longitude</small>
            <InputText v-model="formData.lon" class="w-full p-inputtext-sm" readonly placeholder="0.000000" />
          </div>
        </div>

        <!-- <div v-if="formData.address"
          class="mt-2 text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
          <i class="pi pi-check-circle" />
          <span class="truncate">{{ formData.address }}</span>
        </div> -->
      </div>

      <div class="col-span-12">
        <FormField v-model="formData.target_tree_count" :displayErrors="displayErrors" label="Target Tree Count"
          name="target_tree_count" />
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

  <LocationPickerModal v-model:visible="show" country-codes="cy" @selected="onSelected"/>

</template>

<script setup>
import { reactive, ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import FormField from "@/Components/Primitives/FormField.vue";
import { useDateFormatter } from "@/Composables/useDateFormatter";
import { useDateParser } from "@/Composables/useDateParser";
import LocationPickerModal from "@/Components/LocationPickerModal.vue";



const show = ref(false)

const form = reactive({
  address: '',
  lat: null,
  lon: null,
})

function onSelected(p) {
  // formData.address = p.address
  formData.lat = p.lat
  formData.lon = p.lon
}

const props = defineProps({
  visible: { type: Boolean, default: false },
  dataRow: { type: Object, default: null },
  action: { type: String, default: "" },
  routeResource: { type: String, required: true },

  campaigns: { type: Array, default: () => [] },
  neighborhoods: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  statusOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:visible", "created", "updated"]);

const displayErrors = ref(false);
const { formatDate } = useDateFormatter();
const { parseDate } = useDateParser();

const formData = reactive({
  planting_id: null,
  campaign_id: null,
  neighborhood_id: null,
  assigned_to: null,
  status: "draft",
  started_at: null,
  completed_at: null,
  lat: null,
  lon: null,
  target_tree_count: null,
  notes: "",
});

const campaignOptions = computed(() =>
  (props.campaigns ?? []).map((c) => {
    const start = c.start_date ?? "";
    const end = c.end_date ?? null;
    const sponsor = c.sponsor ?? null;

    const label = sponsor
      ? `${c.name} (${sponsor}) — ${formatDate(start)} → ${formatDate(end) ?? "Ongoing"}`
      : `${c.name} — ${formatDate(start)} → ${formatDate(end) ?? "Ongoing"}`;

    return { value: c.id, label };
  })
);

const neighborhoodOptions = computed(() =>
  (props.neighborhoods ?? []).map((n) => ({
    value: n.id,
    label: n.name,
  }))
);

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

  formData.planting_id = row?.planting_id ?? null;
  formData.campaign_id = row?.campaign_id ?? null;
  formData.neighborhood_id = row?.neighborhood_id ?? null;
  formData.assigned_to = row?.assigned_to.id ?? null;

  formData.status = row?.status ?? "draft";
  formData.started_at = parseDate(row?.started_at);
  formData.completed_at = parseDate(row?.completed_at);

  formData.lat = row?.lat ?? null;
  formData.lon = row?.lon ?? null;

  formData.target_tree_count = row?.target_tree_count ?? null;
  formData.notes = row?.notes ?? "";
};

const submit = () => {
  const payload = { ...formData };

  if (props.action === "Create") {
    router.post(route(props.routeResource + ".store"), payload, {
      preserveScroll: true,
      onSuccess: () => {
        emit("created");
        closeForm();
      },
      onFinish: () => (displayErrors.value = true),
    });
  } else if (props.action === "Edit") {
    router.patch(route(props.routeResource + ".update", formData.planting_id), payload, {
      preserveScroll: true,
      onSuccess: () => {
        emit("updated");
        closeForm();
      },
      onFinish: () => (displayErrors.value = true),
    });
  }
};
</script>
