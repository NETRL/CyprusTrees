<template>
  <li>
    <!-- Item with submenu (has children) -->
    <button 
      v-if="hasChildren"
      v-has-permission="{props: $page.props, permissions: item.permissions?.split('|')}"
      @click="toggleSubmenu(groupIndex, itemIndex)" 
      :class="[
        'menu-item group w-full',
        {
          'menu-item-active': isSubmenuOpen(groupIndex, itemIndex),
          'menu-item-inactive': !isSubmenuOpen(groupIndex, itemIndex),
        },
        !isExpanded && !isHovered? 'lg:justify-center' : 'lg:justify-start',
      ]"
    >
      <!-- Row Icons -->
      <span :class="[
        isSubmenuOpen(groupIndex, itemIndex)
          ? 'menu-item-icon-active'
          : 'menu-item-icon-inactive',
      ]">
        <i :class="item.icon"></i>
      </span>
      
      <!-- Row Names -->
      <span v-if="isExpanded || isHovered || isMobileOpen" class="menu-item-text whitespace-nowrap overflow-hidden transition-opacity duration-300">
        {{ item.name }}
      </span>
      
      <ChevronDownIcon 
        v-if="isExpanded || isHovered || isMobileOpen" 
        :class="[
          'ml-auto w-5 h-5 transition-transform duration-200',
          {
            'rotate-180 text-brand-500': isSubmenuOpen(groupIndex, itemIndex),
          },
        ]" 
      />
    </button>
    
    <!-- Item without submenu -->
    <SidebarNavLink
      v-else
      v-has-permission="{props: $page.props, permissions: item.permissions?.split('|')}"
      :item="item"
      :class="!isExpanded && !isHovered? 'lg:justify-center' : 'lg:justify-start'"
    />
    
    <!-- Sub items list -->
    <transition 
      @enter="startTransition" 
      @after-enter="endTransition" 
      @before-leave="startTransition"
      @after-leave="endTransition"
    >
      <div v-show="isSubmenuOpen(groupIndex, itemIndex) && (isExpanded || isHovered || isMobileOpen)">
        <ul class="mt-2 space-y-1 ml-9">
          <li v-for="subItem in item.children" :key="subItem.id">
            <SidebarNavLink
              v-has-permission="{props: $page.props, permissions: subItem.permissions?.split('|')}"
              :item="subItem"
              :is-sub-item="true"
            />
          </li>
        </ul>
      </div>
    </transition>
  </li>
</template>

<script setup>
import { computed } from 'vue';
import { ChevronDownIcon } from "@/icons";
import SidebarNavLink from '@/Components/Layout/Sidebar/SidebarNavLink.vue';
import { useSidebar } from '@/Composables/useSidebar';

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  groupIndex: {
    type: Number,
    required: true
  },
  itemIndex: {
    type: Number,
    required: true
  }
});

const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();

const hasChildren = computed(() => {
  return props.item.children && props.item.children.length > 0;
});

const toggleSubmenu = (groupIndex, itemIndex) => {
  const key = `${groupIndex}-${itemIndex}`;
  openSubmenu.value = openSubmenu.value === key ? null : key;
};

const isSubmenuOpen = (groupIndex, itemIndex) => {
  const key = `${groupIndex}-${itemIndex}`;
  
  // Check if this submenu is explicitly opened
  if (openSubmenu.value === key) {
    return true;
  }
  
  // Check if any child route is active
  if (props.item.children && props.item.children.length > 0) {
    return props.item.children.some((subItem) => isActive(subItem.route_name));
  }
  
  return false;
};

const isActive = (routeName) => {
  if (!routeName) return false;
  if (typeof route === 'function' && route().current) {
    return route().current(routeName);
  }
  return false;
};

const startTransition = (el) => {
  el.style.height = "auto";
  const height = el.scrollHeight;
  el.style.height = "0px";
  el.offsetHeight; // force reflow
  el.style.height = height + "px";
};

const endTransition = (el) => {
  el.style.height = "";
};
</script>