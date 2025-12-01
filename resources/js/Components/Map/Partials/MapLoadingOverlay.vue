<template>
  <Transition enter-active-class="transition-opacity duration-500 ease-out"
    leave-active-class="transition-opacity duration-700 ease-in" enter-from-class="opacity-0"
    leave-to-class="opacity-0">
    <div v-if="isLoading" class="absolute inset-0 z-50 flex items-center justify-center bg-gray-900">
      <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-800 to-emerald-950/30 animate-pulse-slow">
      </div>

      <div class="absolute inset-0 opacity-20 pointer-events-none">
        <div class="grid grid-cols-4 grid-rows-4 h-full w-full">
          <div v-for="i in 16" :key="i" class="border border-emerald-500/20"></div>
        </div>
      </div>

      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div v-for="(marker, index) in skeletonMarkers" :key="index"
          class="absolute w-2 h-2 bg-emerald-400/40 rounded-full animate-ping-slow" :style="{
            left: marker.x + '%',
            top: marker.y + '%',
            animationDelay: `${index * 150}ms`,
            animationDuration: '3s'
          }"></div>
      </div>

      <div class="relative z-10 px-6">
        <div
          class="bg-gray-800/80 backdrop-blur-xl border border-gray-700/50 shadow-2xl rounded-3xl p-8 max-w-sm w-full mx-auto">
          <div class="flex flex-col items-center gap-6">

            <div class="relative">
              <div class="absolute inset-0 bg-emerald-500/30 blur-xl rounded-full animate-pulse"></div>
              <div
                class="relative bg-gradient-to-b from-gray-700 to-gray-800 rounded-full p-6 border border-emerald-500/20 ring-4 ring-emerald-500/10">
                <svg class="w-12 h-12 text-emerald-400 animate-bounce-slow" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
              </div>
            </div>

            <div class="text-center space-y-2 w-64">
              <h3 class="text-2xl font-bold text-white tracking-tight">
                Nicosia Trees
              </h3>
              <div class="flex flex-col h-6 justify-center">
                <p
                  class="text-emerald-400/80 text-xs font-bold uppercase tracking-widest animate-pulse transition-all duration-300">
                  {{ loadingMessage }}
                </p>
              </div>
            </div>

            <div class="w-full max-w-[12rem] h-1.5 bg-gray-700/50 rounded-full overflow-hidden">
              <div class="h-full w-full bg-gradient-to-r from-emerald-600 via-emerald-400 to-teal-300 animate-progress">
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';


defineProps({
  isLoading: Boolean
})

// --- State ---
const loadingMessage = ref('Initializing map...');
const skeletonMarkers = ref([]);
let messageInterval = null;

// --- Helpers ---
const generateSkeletonMarkers = () => {
  const markers = [];
  for (let i = 0; i < 25; i++) {
    markers.push({
      x: Math.random() * 90 + 5,
      y: Math.random() * 90 + 5,
    });
  }
  return markers;
};

// --- Lifecycle ---
onMounted(() => {
  skeletonMarkers.value = generateSkeletonMarkers();

  // 1. Cycle Messages (Template 2 Logic)
  const messages = [
    'Initializing map...',
    'Connecting to satellite...',
    'Fetching tree data...',
    'Rendering markers...',
    'Calibrating view...',
  ];

  let index = 0;
  messageInterval = setInterval(() => {
    index++;
    loadingMessage.value = messages[index % messages.length];
  }, 800); // Change text every 800ms
});

onBeforeUnmount(() => {
  if (messageInterval) clearInterval(messageInterval);
});
</script>

<style scoped>
/* Icon Bounce */
@keyframes bounce-slow {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-6px);
  }
}

.animate-bounce-slow {
  animation: bounce-slow 3s ease-in-out infinite;
}

/* Background Pulse */
@keyframes pulse-slow {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.7;
  }
}

.animate-pulse-slow {
  animation: pulse-slow 4s ease-in-out infinite;
}

/* Map Dot Ping */
@keyframes ping-slow {
  0% {
    transform: scale(1);
    opacity: 0.4;
  }

  75%,
  100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animate-ping-slow {
  animation: ping-slow 3s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Indeterminate Progress Bar (Sliding Gradient) */
@keyframes progress {
  0% {
    transform: translateX(-100%);
  }

  100% {
    transform: translateX(100%);
  }
}

.animate-progress {
  animation: progress 1.5s ease-in-out infinite;
}
</style>