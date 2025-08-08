<script setup>
import { computed, ref, provide, onMounted } from "vue";
import { RouterView, useRoute } from "vue-router";
import axiosInstance from "@/lib/axios";
import WithSidebarLayout from "@/layouts/WithSidebarLayout.vue";
import WithoutSidebarLayout from "@/layouts/WithoutSidebarLayout.vue";
import LoadingOverlay from "@/components/LoadingOverlay.vue";

// 1️⃣ Define user state
const user = ref(null);
const globalLoading = ref(false);

// 2️⃣ Persist token across refresh
const token = localStorage.getItem("token");
if (token) {
  axiosInstance.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}

// 3️⃣ Get user from backend
const getUser = async () => {
  globalLoading.value = true; // Show loader at the start
  try {
    const response = await axiosInstance.get("/user");
    user.value = response.data;
  } catch (error) {
    user.value = null;
    localStorage.removeItem("token");
    delete axiosInstance.defaults.headers.common["Authorization"];
    // Optional: redirect to login
    // window.location.href = "/login";
  } finally {
    globalLoading.value = false; // Always hide loader at the end
  }
};


// 4️⃣ Set user manually (for login/logout)
const setUser = (userData) => {
  user.value = userData;
  if (!userData) {
    localStorage.removeItem("token");
    delete axiosInstance.defaults.headers.common["Authorization"];
  }
};


// 5️⃣ Fetch user data when app mounts
onMounted(getUser);

// 6️⃣ Provide user globally
provide("user", user);
provide("setUser", setUser);
provide("globalLoading", globalLoading);

// 7️⃣ Layout Logic
const route = useRoute();
const layoutComponent = computed(() => {
  if (
    route.path.startsWith("/login") ||
    route.path.startsWith("/register") ||
    route.path.startsWith("/forgot-password") ||
    route.path.startsWith("/password-reset")
  ) {
    return WithoutSidebarLayout;
  }
  return WithSidebarLayout;
});
</script>

<template>
  <div>
    <component :is="layoutComponent">
      <div class="font-figtree overflow-x-hidden overflow-y-auto h-screen">
        <RouterView />
      </div>
    </component>

    <!-- ✅ Moved outside to cover the entire screen -->
    <LoadingOverlay :visible="globalLoading" />
  </div>
</template>
