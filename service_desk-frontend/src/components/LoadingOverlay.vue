<template>
  <div v-if="visible" class="fixed inset-0 backdrop-brightness-50 flex items-center justify-center z-120">
    <!-- Simple spinning loader -->
    <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
  </div>
</template>

<script setup>
defineProps({
  visible: {
    type: Boolean,
    default: false
  }
});
</script>

<style scoped>
/* No additional CSS needed because we use Tailwind's animate-spin */
</style>
