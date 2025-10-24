<template>
  <component 
    :is="componentType"
    :href="linkHref"
    :class="linkClasses"
  >
    <span :class="iconClasses">
      <i :class="item.icon"></i>
    </span>
    
    <span v-if="isExpanded || isHovered || isMobileOpen" class="menu-item-text whitespace-nowrap overflow-hidden transition-opacity duration-300">
      {{ item.name }}
    </span>
    
    <!-- Badges for sub items -->
    <span v-if="isSubItem" class="flex items-center gap-1 ml-auto">
      <span 
        v-if="item.new" 
        :class="[
          'menu-dropdown-badge',
          {
            'menu-dropdown-badge-active': isActive(item.route_name),
            'menu-dropdown-badge-inactive': !isActive(item.route_name),
          },
        ]"
      >
        new
      </span>
      <span 
        v-if="item.pro" 
        :class="[
          'menu-dropdown-badge',
          {
            'menu-dropdown-badge-active': isActive(item.route_name),
            'menu-dropdown-badge-inactive': !isActive(item.route_name),
          },
        ]"
      >
        pro
      </span>
    </span>
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useSidebar } from '@/Composables/useSidebar';

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  isSubItem: {
    type: Boolean,
    default: false
  }
});

const { isExpanded, isMobileOpen, isHovered } = useSidebar();

const componentType = computed(() => {
  // If external link, use 'a' tag, otherwise use Inertia Link
  return props.item.external ? 'a' : Link;
});

const linkHref = computed(() => {
  // If no route_name, return '#' to avoid errors
  if (!props.item.route_name) {
    return '#';
  }
  
  // If external, use route_name as-is
  if (props.item.external) {
    return props.item.route_name;
  }
  
  // Check if route exists before trying to generate it
  try {
    if (typeof route === 'function' && route().has && route().has(props.item.route_name)) {
      return route(props.item.route_name);
    }
  } catch (e) {
    console.warn(`Route '${props.item.route_name}' not found, using '#' instead`);
  }
  
  // If route doesn't exist, return '#'
  return '#';
});

const isActive = (routeName) => {
  if (!routeName) return false;
  if (typeof route === 'function' && route().current) {
    return route().current(routeName);
  }
  return false;
};

const linkClasses = computed(() => {
  const hasValidRoute = props.item.route_name && linkHref.value !== '#';
  
  if (props.isSubItem) {
    return [
      'menu-dropdown-item',
      {
        'menu-dropdown-item-active': isActive(props.item.route_name),
        'menu-dropdown-item-inactive': !isActive(props.item.route_name),
        'opacity-50 cursor-not-allowed': !hasValidRoute,
      },
    ];
  }
  
  return [
    'menu-item group',
    {
      'menu-item-active': isActive(props.item.route_name),
      'menu-item-inactive': !isActive(props.item.route_name),
      'opacity-50 cursor-not-allowed': !hasValidRoute,
    },
  ];
});

const iconClasses = computed(() => {
  if (props.isSubItem) {
    return ''; // No special icon classes for sub items
  }
  
  return [
    isActive(props.item.route_name)
      ? 'menu-item-icon-active'
      : 'menu-item-icon-inactive',
  ];
});
</script>