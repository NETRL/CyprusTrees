<template>
  <button type="button" @click.prevent="toggleTheme" :title="`Theme: ${theme}`" aria-label="Toggle theme" class="relative inline-flex items-center justify-center h-11 w-11 rounded-full border bg-white text-gray-500 transition-colors
           hover:bg-brand-100 hover:text-brand-700 border-gray-200
           dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-brand-800 dark:hover:text-white">
    <!-- Light -->
    <SunIcon v-if="theme === 'light'" />
    <!-- Dark -->
    <MoonIcon v-else-if="theme === 'dark'" />
    <!-- System -->
    <SystemIcon v-else />
    <span class="sr-only">Toggle theme</span>
  </button>
</template>

<script setup>
import { useTheme } from '@/Composables/useTheme';
import { MoonIcon, SunIcon, SystemIcon } from '@/icons';
const { theme, toggleTheme } = useTheme();
</script>
