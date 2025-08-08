<script setup>
import axiosInstance from "@/lib/axios.js";
import router from "@/router";
import { AxiosError } from "axios";
import { reactive, ref, inject, onMounted } from "vue";

const setUser = inject("setUser");
const isLoading = ref(false);
const divisions = ref([]);

const form = reactive({
  nama_user: "",
  email: "",
  password: "",
  password_confirmation: "",
  nik_user: "",
  role_user: "",
  id_divisi: "",
});

const errors = reactive({
  nama_user: [],
  email: [],
  password: [],
  password_confirmation: [],
  nik_user: [],
  role_user: [],
  id_divisi: [],
});

const fetchDivisions = async () => {
  try {
    const response = await axiosInstance.get("/divisions");
    divisions.value = response.data;
  } catch (error) {
    console.error("Failed to load divisions:", error);
  }
};

const register = async (payload) => {
  isLoading.value = true;

  await axiosInstance.get("/sanctum/csrf-cookie", {
    baseURL: "http://localhost:8000",
  });

  Object.keys(errors).forEach((key) => (errors[key] = []));

  try {
    const response = await axiosInstance.post("/register", payload);
    localStorage.setItem("token", response.data.token);
    axiosInstance.defaults.headers.common["Authorization"] = `Bearer ${response.data.token}`;

    const userResponse = await axiosInstance.get("/user");
    setUser(userResponse.data);

    router.push("/dashboard");
  } catch (e) {
    if (e instanceof AxiosError && e.response?.status === 422) {
      const resErrors = e.response.data.errors;
      Object.keys(resErrors).forEach((key) => {
        errors[key] = resErrors[key];
      });
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchDivisions();
});
</script>

<template>
  <div class="h-screen flex items-center justify-center bg-cover bg-center relative" style="background-image: url('/background.jpeg')">
    <div class="absolute inset-0 bg-black opacity-50"></div>

    <div class="absolute top-8 left-8 z-10">
      <button @click="router.push('/')" class="text-sm text-white hover:underline">
        ← Kembali ke Beranda
      </button>
    </div>

    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-3xl p-10 flex flex-col justify-between">
      <div class="text-center mb-6">
        <img src="/logo.png" alt="Logo" class="w-32 mx-auto mb-2" />
        <h2 class="text-gray-800 text-xl font-semibold">SERVICE DESK</h2>
        <p class="text-xs text-gray-500">We Have Adopted Information Technology Infrastructure Library</p>
      </div>

      <form @submit.prevent="register(form)" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Nama -->
        <div>
          <input type="text" v-model="form.nama_user" placeholder="Nama" class="w-full p-3 border border-gray-300 rounded" />
          <span v-for="error in errors.nama_user" :key="error" class="text-red-500 text-xs italic">{{ error }}</span>
        </div>

        <!-- Role -->
        <div>
          <select v-model="form.role_user" class="w-full p-3 border border-gray-300 rounded">
            <option disabled value="">Pilih Role</option>
            <option value="Admin">Admin</option>
            <option value="Petugas IT">Petugas IT</option>
            <option value="End User">End User</option>
          </select>
          <span v-for="error in errors.role_user" :key="error" class="text-red-500 text-xs italic">{{ error }}</span>
        </div>

        <!-- Email -->
        <div>
          <input type="email" v-model="form.email" placeholder="Email Korporat" class="w-full p-3 border border-gray-300 rounded" />
          <span v-for="error in errors.email" :key="error" class="text-red-500 text-xs italic">{{ error }}</span>
        </div>

        <!-- NIK -->
        <div>
          <input type="text" v-model="form.nik_user" placeholder="NIK" class="w-full p-3 border border-gray-300 rounded" />
          <span v-for="error in errors.nik_user" :key="error" class="text-red-500 text-xs italic">{{ error }}</span>
        </div>

        <!-- Password -->
        <div>
          <input type="password" v-model="form.password" placeholder="Password" class="w-full p-3 border border-gray-300 rounded" />
          <span v-for="error in errors.password" :key="error" class="text-red-500 text-xs italic">{{ error }}</span>
        </div>

        <!-- Confirm Password -->
        <div>
          <input type="password" v-model="form.password_confirmation" placeholder="Konfirmasi Password" class="w-full p-3 border border-gray-300 rounded" />
          <span v-for="error in errors.password_confirmation" :key="error" class="text-red-500 text-xs italic">{{ error }}</span>
        </div>

        <!-- Division -->
        <div class="md:col-span-2">
          <div class="relative">
            <select
              v-model="form.id_divisi"
              class="w-full p-3 border border-gray-300 rounded max-h-52 overflow-y-auto"
              size="1"
            >
            <option disabled value="">Pilih Divisi</option>
              <option
                v-for="division in divisions"
                :key="division.id_divisi"
                :value="division.id_divisi"
              >
                {{ division.nama_divisi }} (Lantai {{ division.lantai_divisi }})
              </option>
            </select>
          </div>
          <span
            v-for="error in errors.id_divisi"
            :key="error"
            class="text-red-500 text-xs italic"
          >
            {{ error }}
          </span>
        </div>

        <!-- Submit Button -->
        <div class="md:col-span-2">
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded flex items-center justify-center disabled:bg-gray-400 disabled:cursor-not-allowed">
            <svg v-if="isLoading" class="w-5 h-5 text-white animate-spin mr-2" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0"></path>
            </svg>
            <span>{{ isLoading ? "Registering..." : "Register" }}</span>
          </button>
        </div>

        <!-- Login Link -->
        <div class="md:col-span-2 text-center">
          <p class="text-sm">
            <button @click="router.push('/login')" class="text-blue-600 hover:underline ml-1">Sudah punya akun? Masuk</button>
          </p>
        </div>
      </form>

      <div class="mt-6 text-xs text-gray-400 text-center">
        © 2025 - Bagian Teknologi Informasi, <span class="text-blue-600">PT Hutama Karya (Persero)</span>
      </div>
    </div>
  </div>
</template>