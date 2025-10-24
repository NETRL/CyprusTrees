<script setup>
import AppSidebar from '@/Layouts/AppSidebar.vue';
import AppHeader from '@/Layouts/AppHeader.vue';
import { useSidebar } from '@/Composables/useSidebar';
import Backdrop from '@/Components/Layout/Backdrop.vue';


const darkMode = 'class';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const { isExpanded, isHovered } = useSidebar()


</script>

<template>
    <Head title="Welcome" />
    <div class="min-h-screen xl:flex" >
        <AppSidebar />
        <Backdrop/>
        <div class="flex-1 transition-all duration-300 ease-in-out"
            :class="[isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]']">
            <AppHeader/>
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                <slot></slot>
            </div>
        </div>
    </div>

</template>
