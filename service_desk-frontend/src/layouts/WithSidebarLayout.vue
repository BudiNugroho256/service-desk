<script setup>
import Sidebar from "@/components/Sidebar.vue";
import Navbar from "@/components/Navbar.vue";
</script>

<template>
  <div>
    <!-- Navbar -->
    <Navbar/>

    <!-- Sidebar -->
    <Sidebar/>
    
    <!-- Main Content -->
    <main>
      <div class="bg-gray-50 w-screen h-screen">
        <slot />
      </div>
    </main>
  </div>
</template>