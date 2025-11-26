<template>
  <slot></slot>
</template>

<script setup>
import { useSidebarProvider } from '@/Composables/useSidebar'

useSidebarProvider()
</script>
