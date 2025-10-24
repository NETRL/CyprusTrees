<template>
    <label :for="id" class="flex items-start text-sm font-normal text-gray-700  select-none dark:text-gray-400">
        <div class="relative">
            <!-- Hidden actual checkbox input -->
            <input type="checkbox" :value="value" v-model="proxyChecked" :id="id" :name="name" class="sr-only" />
            <!-- Custom visible box -->
            <div :class="proxyChecked
                ? 'border-brand-500 bg-brand-500'
                : 'bg-transparent border-gray-300 dark:border-gray-700'
                " class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] cursor-pointer">
                <span :class="proxyChecked ? '' : 'opacity-0'">
                    <MarkIcon />
                </span>
            </div>
        </div>
        <!-- Label content (slot for description text) -->
        <slot></slot>
    </label>
    <InputError v-if="displayErrors" class="mt-2" :message="$page.props.errors[name]" />

</template>

<script setup>
import { MarkIcon } from '@/icons';
import { computed } from 'vue';
import InputError from '@/Components/InputError.vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    id: {
        type: String,
        required: false,
    },
    name: {
        type: String,
        required: false,
    },
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
    displayErrors: {
        type: Boolean,
        default: true
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>