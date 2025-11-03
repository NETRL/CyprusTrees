<template>
    <Head title="Welcome" />
    <div class="min-h-screen xl:flex">
        <!-- <AppSidebar /> -->
        <Backdrop />
        <div class="flex-1 transition-all duration-300 ease-in-out">
            <AppHeader :with-sidebar="false" />
            <div class="">
                 <MapLibreView />
            </div>
        </div>

     
    </div>

</template>

<script setup>
import AppSidebar from '@/Layouts/AppSidebar.vue';
import AppHeader from '@/Layouts/AppHeader.vue';
import { useSidebar } from '@/Composables/useSidebar';
import Backdrop from '@/Components/Layout/Backdrop.vue';
import MapLibreView from '@/Components/Map/MapLibreView.vue';



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