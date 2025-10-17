<template>
  <Link to="/" :class="{'lg:hidden' : withSidebar}">
    <img class="dark:hidden" src="@assets/images/logo/logo.svg" alt="Logo" />
    <img class="hidden dark:block" src="@assets/images/logo/logo-dark.svg" alt="Logo" />
  </Link>
</template>

<script setup>

defineProps({
  withSidebar: Boolean
})

</script>
