<template>
  <div class="relative" ref="dropdownRef">
    <button
      class="group flex items-center gap-2 rounded-lg border border-transparent p-1 transition-all hover:border-slate-200 hover:bg-slate-50 dark:hover:border-slate-700 dark:hover:bg-slate-800"
      @click.prevent="toggleDropdown">
      <div
        class="h-9 w-9 overflow-hidden rounded-lg ring-2 ring-white transition-all group-hover:ring-brand-100 dark:ring-slate-900">
        <img src="https://i.pravatar.cc/100" alt="User" class="h-full w-full object-cover" />
      </div>
      <div class="hidden pr-2 text-left lg:block">
        <p class="text-xs font-bold text-slate-900 dark:text-white leading-tight">
          {{ $page.props.auth.user?.first_name }} {{ $page.props.auth.user?.last_name }}
        </p>
        <!-- <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">Admin Account</p> -->
      </div>
      <ChevronDownIcon
        :class="['h-4 w-4 text-slate-400 transition-transform duration-200', { 'rotate-180': dropdownOpen }]" />
    </button>

    <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100">
      <div v-if="dropdownOpen"
        class="absolute right-0 mt-3 w-64 origin-top-right rounded-xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between border-b border-slate-100 p-3 pb-4 dark:border-slate-700/50">
          <div class="flex flex-col">
            <span class="text-sm font-bold text-slate-900 dark:text-white">
              {{ $page.props.auth.user?.first_name }} {{ $page.props.auth.user?.last_name }}
            </span>
            <span class="text-xs text-slate-500 truncate w-32">
              {{ $page.props.auth.user.email }}
            </span>
          </div>
          <ThemeToggler />
        </div>

        <ul class="space-y-1 py-2">
          <li v-for="item in menuItems" :key="item.href">
            <UserMenuNavLink :item="item" :active="route().current(item.href)" @click="toggleDropdown"
              class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-slate-700/50" />
          </li>
        </ul>

        <div class="mt-1 border-t border-slate-100 pt-1 dark:border-slate-700/50">
          <UserMenuNavLink :item="logOutItem" :method="'post'"
            class="flex w-full items-center rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20" />
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { UserCircleIcon, ChevronDownIcon, LogoutIcon, ReportsIcon } from '@/Icons'
import { ref, onMounted, onUnmounted } from 'vue'
import UserMenuNavLink from './UserMenuNavLink.vue'
import ThemeToggler from '@/Components/Common/ThemeToggler.vue'
import ReportTypeIcon from '@/Icons/ReportTypeIcon.vue'

const dropdownOpen = ref(false)
const dropdownRef = ref(null)

const menuItems = [
  { href: 'profile.edit', icon: UserCircleIcon, text: 'My profile' },
  { href: 'reports.index', icon: ReportsIcon, text: 'My reports' },
  { href: 'events.index', icon: ReportsIcon, text: 'My events' },
  // { href: '/', icon: SettingsIcon, text: 'Account settings' },
  // { href: '/', icon: InfoCircleIcon, text: 'Support' },
]

const logOutItem = { href: 'logout', icon: LogoutIcon, text: 'Sign out' }


const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
