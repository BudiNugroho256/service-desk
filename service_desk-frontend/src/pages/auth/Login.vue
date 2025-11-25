<script setup>
import axiosInstance from "@/lib/axios.js";
import router from "@/router";
import { AxiosError } from "axios";
import { reactive, inject, ref } from "vue";

const setUser = inject("setUser");
const isLoading = ref(false);

const form = reactive({
  email: "",
  password: "",
});

const errors = reactive({
  email: [],
  password: [],
});

const login = async () => {
  isLoading.value = true;

  try {
    // Always hit CSRF endpoint first for Sanctum
    await axiosInstance.get("/sanctum/csrf-cookie", {
      baseURL: "http://localhost:8000",
    });

    // 👇 Map your custom fields to what Laravel expects
    const payload = {
      email: form.email,
      password: form.password,
    };

    const response = await axiosInstance.post("/login", payload);
    localStorage.setItem("token", response.data.token);
    axiosInstance.defaults.headers.common["Authorization"] = `Bearer ${response.data.token}`;

    const userResponse = await axiosInstance.get("/user");
    const user = userResponse.data;

    // 🔥 ADD THIS BLOCK BELOW:
    const userRoles = user.roles?.map(r => r.name) || [];
    // if (userRoles.includes("End User")) {
    //   // Call logout to clear session
    //   await axiosInstance.post("/logout");

    //   // Remove token and auth header
    //   localStorage.removeItem("token");
    //   delete axiosInstance.defaults.headers.common["Authorization"];

    //   errors.email = ["Akses ditolak. End User tidak diperbolehkan mengakses aplikasi ini"];
    //   return;
    // }


    setUser(user);
    router.push("/tickets");
  } catch (e) {
    if (e instanceof AxiosError && e.response?.status === 422) {
      const resErrors = e.response.data.errors;
      errors.email = resErrors.email || [];
      errors.password = resErrors.password || [];
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

    <!-- Login Card -->
    <div class="relative z-10 bg-white rounded shadow-md max-w-md w-full p-8 flex flex-col items-center border border-gray-200">
      <!-- Logo and Heading -->
      <div class="mb-6 text-center">
        <img src="/logo.png" alt="Logo" class="w-28 mx-auto mb-2" />
        <h2 class="text-xl font-semibold text-gray-800 tracking-tight">SERVICE DESK</h2>
        <p class="text-sm text-gray-500">We Have Adopted Information Technology Infrastructure Library</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="login(form)" class="w-full space-y-4">
        <!-- Email Field -->
        <div class="relative">
          <input
            type="email"
            v-model="form.email"
            placeholder="Alamat E-Mail Korporat Anda"
            class="w-full p-3 pl-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300"
            required
          />
          <span class="absolute left-3 top-3.5 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm2 6H6a2 2 0 00-2 2v1h16v-1a2 2 0 00-2-2z" />
            </svg>
          </span>
          <span v-for="error in errors.email" :key="error" class="text-red-500 text-xs mt-1 block">{{ error }}</span>
        </div>

        <!-- Password Field -->
        <div class="relative">
          <input
            type="password"
            v-model="form.password"
            placeholder="************"
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

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg flex items-center justify-center disabled:bg-gray-400 disabled:cursor-not-allowed">
          <svg v-if="isLoading" class="w-5 h-5 text-white animate-spin mr-2" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0"></path>
          </svg>
          <span>{{ isLoading ? "Logging in..." : "Login" }}</span>
        </button>

        <!-- Forgot Password -->
        <!-- <div class="w-full text-center mt-2">
          <button @click="router.push('/forgot-password')" class="text-sm text-blue-600 hover:underline">
            Lupa Password?
          </button>
        </div> -->
      </form>
    </div>

    <div class="absolute bottom-8 w-full text-center z-10">
      <div class="text-xs text-white">
        © 2025 - Bagian Teknologi Informasi, <span class="text-blue-500">PT Hutama Karya (Persero)</span>
      </div>
    </div>
  </div>
</template>