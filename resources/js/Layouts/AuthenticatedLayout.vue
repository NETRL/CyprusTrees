<template>
    <Toast :breakpoints="{'920px': {width: '100%', right: '0', left: '0'}}" position="bottom-right"/>
    <ConfirmDialog></ConfirmDialog>
    <div class="min-h-screen">
        <AppSidebar />
        <Backdrop/>
        <div class="min-h-screen flex flex-col transition-all duration-300 ease-in-out"
            :class="[isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]']">
            <AppHeader/>
            <div class="p-4 mx-auto md:p-6 w-full flex-1 flex flex-col">
                <slot/>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppSidebar from '@/Layouts/AppSidebar.vue';
import AppHeader from '@/Layouts/AppHeader.vue';
import { useSidebar } from '@/Composables/useSidebar';
import Backdrop from '@/Components/Layout/Backdrop.vue';
import { useCustomToast } from '@/Composables/useCustomToast';


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

useCustomToast();



</script>


