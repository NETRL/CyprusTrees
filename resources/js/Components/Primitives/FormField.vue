<template>
    <label v-if="label" :for="name"
        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400 pointer-events-none select-none">
        {{ label }}
        <span v-if="required" class="text-error-500">*</span>
    </label>
    <InputText v-if="component === 'InputText'" :id="name"
        :class="[shouldDisplayErrors ? 'invalid-focus-outline' : '', inputTextStyle]" v-bind="$attrs" />
    <Password v-else-if="component === 'Password'" :id="name"
        :class="[shouldDisplayErrors ? 'invalid-focus-outline' : '', passwordStyle]" v-bind="$attrs"
        inputClass="w-full border-transparent! focus:border-brand-300! focus:outline-hidden! focus:ring-1! focus:ring-brand-500/60! rounded-lg! text-sm! text-gray-800! placeholder:text-gray-400! dark:text-white/90! dark:placeholder:text-white/30! dark:focus:border-brand-800!">
        >
        <template #header>
            <h3>Pick a password</h3>
        </template>
        <template #footer>
            <Divider />
            <p class="mt-2">Suggestions</p>
            <ul class="pl-2 ml-2 mt-0" style="line-height: 1.5">
                <li>At least one lowercase</li>
                <li>At least one uppercase</li>
                <li>At least one numeric</li>
                <li>Minimum 8 characters</li>
            </ul>
        </template>
    </Password>
    <Dropdown v-else-if="component === 'Dropdown'" :id="name"
        :class="[shouldDisplayErrors ? 'invalid-focus-outline' : '', inputBase]" v-bind="$attrs" />
    <MultiSelect v-else-if="component === 'MultiSelect'" :id="name"
        :class="[shouldDisplayErrors ? 'invalid-focus-outline' : 'items-center', inputBase, 'custom-multiselect']"
        v-bind="$attrs" :maxSelectedLabels="1" panelClass="custom-multiselect-panel" />

    <InputNumber v-else-if="component === 'Number'" :id="name"
        :class="[shouldDisplayErrors ? 'invalid-focus-outline' : '', inputBase]" v-bind="$attrs"
        inputClass="w-full border-transparent! focus:border-brand-300! focus:outline-hidden! focus:ring-1! focus:ring-brand-500/60! rounded-lg! text-sm! text-gray-800! placeholder:text-gray-400! dark:text-white/90! dark:placeholder:text-white/30! dark:focus:border-brand-800!" />
    <Textarea v-else-if="component === 'Textarea'" :id="name"
        :class="[shouldDisplayErrors ? 'invalid-focus-outline' : '', defaultStyle]" v-bind="$attrs" />
    <FileUpload v-else-if="component === 'File'" :id="name"
        :class="[shouldDisplayErrors ? 'invalid-focus-outline' : '', defaultStyle]" style="width:130px;"
        v-bind="$attrs" />
    <Calendar v-else-if="component === 'Calendar'" :id="name"
        :class="[shouldDisplayErrors ? 'invalid-focus-outline' : '', inputBase]" showIcon showTime hourFormat="24"
        locale="en-GB" v-bind="$attrs"
        inputClass="w-full border-y-transparent! border-l-transparent! border-r-gray-700! focus:border-brand-300! focus:outline-hidden! focus:ring-1! focus:ring-brand-500/60! text-sm!text-gray-800! placeholder:text-gray-400! dark:text-white/90! dark:placeholder:text-white/30! dark:focus:border-brand-800!" />
    <InputSwitch v-else-if="component === 'InputSwitch'" :id="name"
        :class="[shouldDisplayErrors ? 'invalid-focus-outline' : '', defaultStyle]" v-bind="$attrs" />
    <Checkbox v-else-if="component === 'Checkbox'" :id="name"
        :class="[shouldDisplayErrors ? 'invalid-focus-outline' : '',]" v-bind="$attrs" />
    <CustomSlider v-else-if="component === 'CustomSlider'" :id="name" v-bind="$attrs" />
    <InputError v-if="shouldDisplayErrors" :id="name + '-help'" class="mt-2" :message="$page.props.errors[name]" />
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import CustomSlider from '@/Components/Primitives/CustomSlider.vue';

const page = usePage();

const props = defineProps({
    label: String,
    name: String,
    required: {
        type: Boolean,
        default: false
    },
    displayErrors: {
        type: Boolean,
        default: true
    },
    component: {
        type: String,
        default: 'InputText'
    }
});

const shouldDisplayErrors = computed(() => {
    const errors = page?.props?.errors ?? {}
    return !!props.displayErrors && !!props.name && !!errors[props.name]
})

const inputBase = 'h-11! w-full! rounded-lg! border! border-gray-300! bg-transparent! text-sm! text-gray-800! shadow-theme-xs! placeholder:text-gray-400! focus:border-brand-300! focus:outline-hidden! focus:ring-1! focus:ring-brand-500/60! dark:border-gray-700!  dark:text-white/90! dark:placeholder:text-white/30! dark:focus:border-brand-800!'

const inputTextStyle = inputBase + 'px-4! py-2.5!'
const passwordStyle = inputBase + 'bg-none!'
const defaultStyle = inputBase + 'py-2.5! pl-4! pr-11!'
</script>