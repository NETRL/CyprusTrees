<template>
  <ThemeProvider>
    <SidebarProvider>
      <slot></slot>
    </SidebarProvider>
  </ThemeProvider>
</template>

<script setup lang="ts">
import ThemeProvider from '@/Components/Layout/ThemeProvider.vue'
import SidebarProvider from '@/Components/Layout/Sidebar/SidebarProvider.vue'
</script>
