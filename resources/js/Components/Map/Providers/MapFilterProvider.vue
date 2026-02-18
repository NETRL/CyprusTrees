<template>
  <slot></slot>
</template>

<script setup>
import { useMapFilterProvider } from '@/Composables/useMapFilter';

useMapFilterProvider()
</script>
