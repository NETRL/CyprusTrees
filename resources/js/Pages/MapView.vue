<template>

    <Head title="MapView.vue" />
    <div class="min-h-dvh h-dvh flex flex-col">
        <!-- <Backdrop  class="z-999!"/> -->
        <header class="shrink-0 relative ">
            <AppHeader>
                <template #startIcon>
                    <HeaderLogo class="max-lg:hidden" />
                </template>
            </AppHeader>
        </header>
        <main class="flex-1 relative overflow-hidden">
            <MapLibreView />
        </main>


    </div>

</template>

<script setup>
import AppSidebar from '@/Layouts/AppSidebar.vue';
import AppHeader from '@/Layouts/AppHeader.vue';
import { useSidebar } from '@/Composables/useSidebar';
import Backdrop from '@/Components/Layout/Backdrop.vue';
import MapLibreView from '@/Components/Map/MapLibreView.vue';
import MapSidebar from '@/Components/Map/Partials/MapSidebar.vue';
import HeaderLogo from '@/Components/Layout/Header/HeaderLogo.vue';



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