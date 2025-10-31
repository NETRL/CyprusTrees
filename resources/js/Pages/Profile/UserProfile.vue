<template>
    <PageBreadcrumb :pageTitle="currentPageTitle" />

    <ComponentCard :title="'Profile'">
      <profile-card />
      <personal-info-card />
      <address-card />
      <delete-user-card />
    </ComponentCard>
</template>

<script setup>
import Authenticated from '@/Layouts/AuthenticatedLayout.vue'
import PageBreadcrumb from '@/Components/Common/PageBreadcrumb.vue'
import { ref } from 'vue'
import ProfileCard from '@/Pages/Profile/Partials/ProfileCard.vue'
import PersonalInfoCard from '@/Pages/Profile/Partials/PersonalInfoCard.vue'
import AddressCard from '@/Pages/Profile/Partials/AddressCard.vue'
import DeleteUserCard from '@/Pages/Profile/Partials/DeleteUserCard.vue'
import ComponentCard from '@/Components/Common/ComponentCard.vue'

defineOptions({
  layout: Authenticated
})



const currentPageTitle = ref('User Profile')
</script>
