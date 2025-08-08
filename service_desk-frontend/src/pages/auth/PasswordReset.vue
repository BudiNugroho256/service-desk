<script setup>
import axiosInstance from "@/lib/axios.js";
import router from "@/router";
import { reactive, ref, inject, onMounted } from "vue";
import { useRoute } from "vue-router";
import { AxiosError } from "axios";

const route = useRoute();
const setUser = inject("setUser");

const isLoading = ref(false);
const token = ref("");
const email = ref("");

const form = reactive({
  email: "",
  password: "",
  password_confirmation: "",
  token: "",
});

const errors = reactive({
  email: [],
  password: [],
  password_confirmation: [],
});

onMounted(() => {
  token.value = route.params.token || route.query.token;
  email.value = route.query.email;

  form.email = email.value;
  form.token = token.value;
});

const resetPassword = async (payload) => {
  isLoading.value = true;

  await axiosInstance.get("/sanctum/csrf-cookie", {
    baseURL: "http://localhost:8000",
  });

  errors.email = [];
  errors.password = [];
  errors.password_confirmation = [];

  try {
    await axiosInstance.post("/reset-password", payload);
    alert("Password berhasil direset! Silakan login.");
    router.push("/login");
  } catch (e) {
    if (e instanceof AxiosError && e.response?.status === 422) {
      errors.email = e.response.data.errors.email || [];
      errors.password = e.response.data.errors.password || [];
      errors.password_confirmation = e.response.data.errors.password_confirmation || [];
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="h-screen flex items-center justify-center bg-cover bg-center relative" style="background-image: url('/background.jpeg')">
    <!-- Dark overlay -->
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>

    <!-- Reset Password Card -->
    <div class="relative z-10 bg-white rounded shadow-md max-w-md w-full p-8 flex flex-col items-center border border-gray-200">
      <!-- Logo and Heading -->
      <div class="mb-6 text-center">
        <img src="/logo.png" alt="Logo" class="w-28 mx-auto mb-2" />
        <h2 class="text-xl font-semibold text-gray-800 tracking-tight">SERVICE DESK</h2>
        <p class="text-sm text-gray-500">Reset Password Anda</p>
      </div>

      <!-- Reset Password Form -->
      <form @submit.prevent="resetPassword(form)" class="w-full space-y-4">
        <!-- Email Field -->
        <div class="relative">
          <input
            type="email"
            id="email"
            v-model="form.email"
            placeholder="Alamat Email"
            class="w-full p-3 pl-10 border border-gray-300 rounded-lg text-sm bg-gray-100 focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300"
            readonly
          />
          <span class="absolute left-3 top-3.5 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </span>
          <span v-for="error in errors.email" :key="error" class="text-red-500 text-xs mt-1 block">{{ error }}</span>
        </div>

        <!-- New Password Field -->
        <div class="relative">
          <input
            type="password"
            id="password"
            v-model="form.password"
            placeholder="Password Baru"
            class="w-full p-3 pl-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300"
            required
          />
          <span class="absolute left-3 top-3.5 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.104 0 2 .896 2 2v1a2 2 0 01-4 0v-1c0-1.104.896-2 2-2zm6-3V7a6 6 0 00-12 0v1a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V10a2 2 0 00-2-2zm-8-1a4 4 0 118 0v1H10V7z" />
            </svg>
          </span>
          <span v-for="error in errors.password" :key="error" class="text-red-500 text-xs mt-1 block">{{ error }}</span>
        </div>

        <!-- Confirm Password Field -->
        <div class="relative">
          <input
            type="password"
            id="password_confirmation"
            v-model="form.password_confirmation"
            placeholder="Konfirmasi Password"
            class="w-full p-3 pl-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300"
            required
          />
          <span class="absolute left-3 top-3.5 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l7 4v6c0 5.25-3.92 9.74-7 11-3.08-1.26-7-5.75-7-11V6l7-4z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
            </svg>
          </span>
          <span v-for="error in errors.password_confirmation" :key="error" class="text-red-500 text-xs mt-1 block">{{ error }}</span>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg flex items-center justify-center disabled:bg-gray-400 disabled:cursor-not-allowed">
          <svg v-if="isLoading" class="w-5 h-5 text-white animate-spin mr-2" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0" />
          </svg>
          <span>{{ isLoading ? "Resetting..." : "Reset Password" }}</span>
        </button>

        <!-- Back to Login -->
        <div class="text-center">
          <button @click="router.push('/login')" class="text-sm text-blue-600 hover:underline">
            Kembali ke Login
          </button>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="absolute bottom-8 w-full text-center z-10">
      <div class="text-xs text-white">
        © 2025 - Bagian Teknologi Informasi, <span class="text-blue-500">PT Hutama Karya (Persero)</span>
      </div>
    </div>
  </div>
</template>