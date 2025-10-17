<template>
    <label :for="id"
        class="flex items-start text-sm font-normal text-gray-700  select-none dark:text-gray-400">
        <div class="relative">
            <!-- Hidden actual checkbox input -->
            <input type="checkbox" :value="value" v-model="proxyChecked" :id="id" :name="name" class="sr-only" />
            <!-- Custom visible box -->
            <div :class="proxyChecked
                ? 'border-brand-500 bg-brand-500'
                : 'bg-transparent border-gray-300 dark:border-gray-700'
                " class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] cursor-pointer">
                <span :class="proxyChecked ? '' : 'opacity-0'">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
        </div>
        <!-- Label content (slot for description text) -->
        <slot></slot>
    </label>
</template>

<script setup>
import { computed } from 'vue';

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