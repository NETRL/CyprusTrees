<template>
  <dashboard>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <ComponentCard :title="'Profile'">
      <profile-card />
      <personal-info-card />
      <address-card />
      <delete-user-card />
    </ComponentCard>
  </dashboard>
</template>

<script setup>
import Dashboard from '@/Pages/Dashboard.vue'
import PageBreadcrumb from '@/Components/Common/PageBreadcrumb.vue'
import { ref } from 'vue'
import ProfileCard from '@/Pages/Profile/Partials/ProfileCard.vue'
import PersonalInfoCard from '@/Pages/Profile/Partials/PersonalInfoCard.vue'
import AddressCard from '@/Pages/Profile/Partials/AddressCard.vue'
import DeleteUserCard from '@/Pages/Profile/Partials/DeleteUserCard.vue'
import ComponentCard from '@/Components/Common/ComponentCard.vue'
const currentPageTitle = ref('User Profile')
</script>
