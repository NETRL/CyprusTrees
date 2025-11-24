<template>
    <aside :class="[
        'absolute inset-y-0 left-0 h-full py-5 flex flex-col lg:mt-0 bg-white/90 dark:text-white dark:bg-gray-900/90 dark:border-gray-800 text-gray-900 transition-all duration-300 ease-in-out z-50 border-r border-gray-200',
        {
                // Desktop behaviour

                'lg:w-[390px] px-5': isExpanded || isMobileOpen || isHovered,
                'lg:w-0': !isExpanded && !isHovered,
                'lg:translate-x-0': true,

                // Mobile behaviour
                'translate-x-0 w-screen': isMobileOpen,   //  full width on mobile when open
                '-translate-x-full': !isMobileOpen,       // hidden off-screen when closed
        },
    ]" @mouseenter="!isExpanded && (isHovered = true)" @mouseleave="isHovered = false">

        <!-- Sidebar Categories - Scrollable Area -->
        <div class="flex-1 flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
            <nav class="mb-6">
                <div class="flex flex-col gap-4">
                    <span>Some very very very very very very very very very very very very very very very very very very very very long text </span>
                </div>
            </nav>
        </div>


    </aside>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from '@inertiajs/vue3';
import { HorizontalDots } from "@/icons";
import SidebarWidget from '@/Components/Layout/Sidebar/SidebarWidget.vue';
import SidebarNavItem from '@/Components/Layout/Sidebar/SidebarNavItem.vue';
import { useSidebar } from '@/Composables/useSidebar';

const page = usePage();
const { isExpanded, isMobileOpen, isHovered } = useSidebar();

// Get navigation links from page props
const navLinks = computed(() => page.props.navbar || []);
</script>