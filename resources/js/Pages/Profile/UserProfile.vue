<template>
  <dashboard>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <div
      class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6"
    >
      <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-7">Profile</h3>
      <profile-card />
      <personal-info-card />
      <address-card />
      <delete-user-card />
      
    </div>
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
const currentPageTitle = ref('User Profile')
</script>
