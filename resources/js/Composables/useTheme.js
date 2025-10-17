import { ref, computed, watchEffect, onMounted, onUnmounted } from 'vue';

const STORAGE_KEY = 'theme';
const themeRef = ref('system'); // 'light' | 'dark' | 'system'
let mediaQuery;

export function useTheme() {
  const isDark = computed(() => {
    if (themeRef.value === 'dark') return true;
    if (themeRef.value === 'light') return false;
    // 'system'
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
  });

  function applyTheme() {
    const root = document.documentElement;
    const dark = isDark.value;
    root.classList.toggle('dark', dark);
  }

  function setTheme(next) {
    themeRef.value = next;
    localStorage.setItem(STORAGE_KEY, next);
    applyTheme();
  }

  function toggleTheme() {
    // Cycle through light → dark → system → light
    const order = ['light', 'dark', 'system'];
    const currentIndex = order.indexOf(themeRef.value);
    const next = order[(currentIndex + 1) % order.length];
    setTheme(next);
  }

  // Watch system preference changes only in 'system' mode
  function handleMediaChange() {
    if (themeRef.value === 'system') {
      applyTheme();
    }
  }

  onMounted(() => {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (['light', 'dark', 'system'].includes(saved)) {
      themeRef.value = saved;
    } else {
      themeRef.value = 'system';
    }

    mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', handleMediaChange);

    // Apply theme correctly after reactivity settles
    requestAnimationFrame(applyTheme);
  });

  onUnmounted(() => {
    if (mediaQuery) {
      mediaQuery.removeEventListener('change', handleMediaChange);
    }
  });

  watchEffect(applyTheme);

  return { theme: themeRef, isDark, setTheme, toggleTheme };
}
