<template>
  <slot></slot>
</template>

<script setup>
import { useMapUiStateProvider } from '@/Lib/Map/useMapUiState';

useMapUiStateProvider()
</script>
