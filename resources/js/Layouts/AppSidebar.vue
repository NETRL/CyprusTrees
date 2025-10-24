<template>
  <aside :class="[
    'fixed mt-16 flex flex-col lg:mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200',
    {
      'lg:w-[290px]': isExpanded || isMobileOpen || isHovered,
      'lg:w-[90px]': !isExpanded && !isHovered,
      'translate-x-0 w-[290px]': isMobileOpen,
      '-translate-x-full': !isMobileOpen,
      'lg:translate-x-0': true,
    },
  ]" @mouseenter="!isExpanded && (isHovered = true)" @mouseleave="isHovered = false">
    <!-- Sidebar Logo  -->
    <div :class="[
      'py-8 flex',
      !isExpanded && !isHovered ? 'lg:justify-center' : 'justify-start',
    ]">
      <Link :href="route('/')">
      <img v-if="isExpanded || isHovered || isMobileOpen" class="dark:hidden" src="@assets/images/logo/logo.svg"
        alt="Logo" width="150" height="40" />
      <img v-if="isExpanded || isHovered || isMobileOpen" class="hidden dark:block"
        src="@assets/images/logo/logo-dark.svg" alt="Logo" width="150" height="40" />
      <img v-else src="@assets/images/logo/logo-icon.svg" alt="Logo" width="32" height="32" />
      </Link>
    </div>

    <!-- Sidebar Categories - Scrollable Area -->
    <div class="flex-1 flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
      <nav class="mb-6">
        <div class="flex flex-col gap-4">
          <!-- Single group for all nav items -->
          <div>
            <h2 :class="[
              'mb-4 text-xs uppercase flex leading-[20px] text-gray-400 whitespace-nowrap overflow-hidden transition-opacity duration-300',
              !isExpanded && !isHovered ? 'lg:justify-center' : 'justify-start',
            ]">
              <template v-if="isExpanded || isHovered || isMobileOpen">
                Menu
              </template>
              <HorizontalDots v-else />
            </h2>

            <!-- Items list -->
            <ul class="flex flex-col">
              <SidebarNavItem v-for="(navlink, index) in navLinks" :key="navlink.id" :item="navlink" :group-index="0"
                :item-index="index" />
            </ul>
          </div>
        </div>
      </nav>
    </div>

    <transition enter-active-class="transition-opacity duration-1100"
      leave-active-class="transition-opacity duration-100 overflow-hidden" enter-from-class="opacity-0" enter-to-class="opacity-100"
      leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="isExpanded || isHovered || isMobileOpen" class="flex-shrink-0">
        <SidebarWidget />
      </div>
    </transition>
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