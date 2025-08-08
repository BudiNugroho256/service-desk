<template>
  <div class="bg-white text-gray-900 rounded-lg p-4 flex flex-col items-center relative">
    <img :src="app.icon" alt="" class="w-12 h-12 mb-2">
    <div class="font-semibold text-sm">{{ app.name }}</div>
    <div class="text-xs text-gray-800">{{ app.sub }}</div>
    <div class="absolute top-2 right-2 text-green-400">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
  </div>
</template>

<script setup>
defineProps({
  app: Object
})
</script>
