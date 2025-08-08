<script setup>
import axiosInstance from "@/lib/axios.js";
import router from "@/router";
import { AxiosError } from "axios";
import { reactive, ref } from "vue";

const isLoading = ref(false);

const form = reactive({
  email: "",
});

const errors = reactive({
  email: [],
});

const statusMessage = ref("");

const submitForgotPassword = async (payload) => {
  isLoading.value = true;
  statusMessage.value = "";
  errors.email = [];

  try {
    await axiosInstance.get("/sanctum/csrf-cookie", {
      baseURL: "http://localhost:8000",
    });

    await axiosInstance.post("/forgot-password", payload);

    statusMessage.value = "Link untuk reset password telah dikirim ke email Anda.";
  } catch (e) {
    if (e instanceof AxiosError && e.response?.status === 422) {
      errors.email = e.response.data.errors.email || [];
    } else {
      statusMessage.value = "Terjadi kesalahan. Silakan coba lagi.";
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="h-screen flex items-center justify-center bg-cover bg-center relative" style="background-image: url('/background.jpeg')">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>

    <!-- Forgot Password Card -->
    <div class="relative z-10 bg-white rounded shadow-md max-w-md w-full p-8 flex flex-col items-center border border-gray-200">
      <!-- Logo and Heading -->
      <div class="mb-6 text-center">
        <img src="/logo.png" alt="Logo" class="w-28 mx-auto mb-2" />
        <h2 class="text-xl font-semibold text-gray-800 tracking-tight">SERVICE DESK</h2>
        <p class="text-sm text-gray-500">We Have Adopted Information Technology Infrastructure Library</p>
      </div>

      <!-- Forgot Password Form -->
      <form @submit.prevent="submitForgotPassword(form)" class="w-full space-y-4">
        <!-- Email Field -->
        <div class="relative">
          <input
            type="email"
            id="email"
            v-model="form.email"
            placeholder="Alamat E-Mail Korporat Anda"
            class="w-full p-3 pl-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300"
            required
          />
          <span class="absolute left-3 top-3.5 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </span>
          <span v-for="error in errors.email" :key="error" class="text-red-500 text-xs mt-1 block">{{ error }}</span>
        </div>

        <!-- Success / Error Message -->
        <div v-if="statusMessage" class="text-sm text-center text-green-600">
          {{ statusMessage }}
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
          <span>{{ isLoading ? "Mengirim..." : "Kirim Link Reset Password" }}</span>
        </button>

        <!-- Back to Login -->
        <div class="text-center">
          <button @click="router.push('/login')" class="text-sm text-blue-600 hover:underline">
            Sudah ingat password? Masuk
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