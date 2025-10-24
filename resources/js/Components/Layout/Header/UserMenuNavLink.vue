<template>
    <Link :href="route(item.href)" :method="method" :class="itemClasses" >
    <component :is="item.icon" :class="iconClasses"  />
    {{ item.text }}
    </Link>
</template>


<script setup>
import { computed } from 'vue';
const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    active: {
        type: Boolean
    },
    method: {
        type: String,
        default: 'get'
    }
});


const itemClasses = computed(() =>
    props.active
        ? 'menu-item group menu-item-active'
        : 'menu-item group menu-item-inactive dark:text-gray-500! dark:hover:text-gray-400!'
);

const iconClasses = computed(() =>
    props.active
        ? 'menu-item-icon-active'
        : 'menu-item-icon-inactive dark:text-gray-500! dark:hover:text-gray-400!'
);


</script>