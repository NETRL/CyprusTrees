<template>
  <div class="relative" ref="dropdownRef">
    <button
      class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-brand-100 hover:text-brand-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-brand-800 dark:hover:text-white"
      @click="toggleDropdown">
      <span :class="{ hidden: !notifying, flex: notifying }"
        class="absolute right-0 top-0.5 z-1 h-2 w-2 rounded-full bg-orange-400">
        <span class="absolute inline-flex w-full h-full bg-orange-400 rounded-full opacity-75 -z-1 animate-ping"></span>
      </span>
      <BellIcon />
    </button>

    <!-- Dropdown Start -->
    <div v-if="dropdownOpen"
      class="absolute right-60 mt-[17px] flex h-auto w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark sm:w-[361px] lg:right-0">
      <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800">
        <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">Notification</h5>

        <button @click="closeDropdown" class="text-gray-500 dark:text-gray-400">
          <CloseIcon />
        </button>
      </div>

      <ul class="flex flex-col h-auto overflow-y-auto custom-scrollbar">
        <li v-for="n in notifications" :key="n.id" @click.prevent="handleItemClick(n)">
          <a class="flex gap-3 rounded-lg border-b border-gray-100 p-3 px-4.5 py-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/5"
            href="#">
            <div class="block w-full">
              <span class=" mb-1.5 block text-theme-sm text-gray-500 dark:text-gray-400">
                <span class="font-medium text-gray-800 dark:text-white/90">
                  {{ n.data?.title ?? 'Notification' }}
                </span>
                <span class="block">
                  {{ n.data?.message ?? '' }}
                </span>
              </span>

              <span class="flex items-center gap-2 text-gray-500 text-theme-xs dark:text-gray-400">
                <span>{{ n.data?.type_label ?? 'General' }}</span>
                <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                <span>{{ formatDate(n.created_at) }}</span>
                <span v-if="!n.read_at" class="ml-auto inline-flex h-2 w-2 rounded-full bg-orange-400"></span>
              </span>
            </div>
          </a>
        </li>

      </ul>

      <Link to="#"
        class="mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/3 dark:hover:text-gray-200"
        @click="handleViewAllClick">
        View All Notification
      </Link>
    </div>
    <!-- Dropdown End -->
  </div>
</template>
<script setup>
import { useDateFormatter } from '@/Composables/useDateFormatter'
import { BellIcon, CloseIcon } from '@/Icons'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, onUnmounted } from 'vue'

const { formatDate } = useDateFormatter()

const page = usePage()

const dropdownOpen = ref(false)
const dropdownRef = ref(null)

const notifPayload = computed(() => page.props.notifications || { unread_count: 0, latest: [] })
const unreadCount = computed(() => notifPayload.value.unread_count || 0)
const notifying = computed(() => unreadCount.value > 0)

const notifications = computed(() => notifPayload.value.latest || [])

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

// mark as read + go
const handleItemClick = (n) => {
  // optimistic close
  closeDropdown()

  router.post(
    route('notifications.read', n.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        const url = n.data?.url
        if (url) router.visit(url, { preserveScroll: true })
      },
    }
  )
}

const handleViewAllClick = (event) => {
  event.preventDefault()
  closeDropdown()
  router.visit(route('notifications.index'))
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>
