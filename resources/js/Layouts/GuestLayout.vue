<template>
    <Toast :breakpoints="{'920px': {width: '100%', right: '0', left: '0'}}" position="bottom-right"/>
  <div class="min-h-screen">
    <main>
      <slot></slot>
    </main>
  </div>
</template>

<script setup>
import { useCustomToast } from '@/Composables/useCustomToast';

useCustomToast();

</script>
