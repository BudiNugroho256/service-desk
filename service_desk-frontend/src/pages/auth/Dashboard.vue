<script setup>
import { inject } from "vue";
import router from "@/router"; // Import router

const user = inject("user"); // Get user data from App.vue
</script>

<template>
  <div class="h-screen flex flex-col items-center justify-center bg-cover bg-center relative" style="background-image: url('/dashboard.jpeg')">
    <div class="absolute inset-0 bg-black opacity-50"></div>

    <!-- Exit (X) Button -->
    <div class="absolute top-8 right-8 z-10">
      <button @click="router.push('/')" class="text-white hover:text-gray-300">
        <!-- X Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Card -->
    <div class="relative bg-white rounded-lg shadow-lg max-w-lg w-full p-8 flex flex-col items-center">
      <!-- Logo -->
      <div class="mb-6">
        <img src="/logo.png" alt="Logo" class="w-32 mx-auto mb-2" />
        <h2 class="text-center text-gray-800 text-lg font-semibold">SERVICE DESK</h2>
        <p class="text-xs text-gray-500 text-center">We Have Adopted Information Technology Infrastructure Library</p>
      </div>

      <!-- Dashboard Content -->
      <div class="w-full text-center">
        <h1 class="text-2xl text-gray-800 font-semibold mb-4">Dashboard</h1>
          <div v-if="user">
            <p class="text-lg text-gray-700 mb-2">Welcome back, <span class="font-semibold">{{ user.nama_user }}</span></p>
            <p class="text-sm text-gray-500 mb-1">{{ user.email }}</p>
            <p class="text-sm text-gray-500 mb-1">NIK: <span class="font-medium">{{ user.nik_user }}</span></p>
            <p class="text-sm text-gray-500 mb-1">Role: <span class="font-medium">{{ user.role_user }}</span></p>
            <p class="text-sm text-gray-500">Divisi: 
            <span class="font-medium">
                {{ user.divisi?.nama_divisi ?? 'Tidak tersedia' }} 
                (Lantai {{ user.divisi?.lantai_divisi ?? '-' }})
            </span>
            </p>
          </div>
        <div v-else>
          <p class="text-lg text-gray-900">Loading user data...</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-8 text-xs text-gray-400 text-center">
        © 2025 - Bagian Teknologi Informasi, <span class="text-blue-600">PT Hutama Karya (Persero)</span>
      </div>
    </div>
  </div>
</template>