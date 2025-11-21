<template>

    <Head title="MapView.vue" />
    <div class="min-h-dvh h-dvh flex flex-col">
        <!-- <AppSidebar /> -->
        <Backdrop />
        <header class="shrink-0 relative ">
            <AppHeader :with-sidebar="false" />
        </header>

        <main class="flex-1 relative overflow-hidden">
            <MapLibreView/>
        </main>


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