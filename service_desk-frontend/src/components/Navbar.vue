<script setup>
import { inject, ref, onMounted, onUnmounted, computed, watch } from "vue";
import { RouterLink, useRoute } from "vue-router";
import axiosInstance from '@/lib/axios';
import router from '@/router';

const user = inject("user");
const setUser = inject("setUser");

const route = useRoute();
const noSidebarRoutes = ["/login", "/register"];

const showSidebar = computed(() => !noSidebarRoutes.includes(route.path));

const notifications = ref([]);
const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length);
const showDropdown = ref(false);

const dropdownRef = ref(null);

const fetchNotifications = async () => {
  const res = await axiosInstance.get('/notifications');
  notifications.value = res.data;
};

const logout = async () => {
  try {
    await axiosInstance.post('/logout');
    localStorage.removeItem('token');
    delete axiosInstance.defaults.headers.common['Authorization'];
    setUser(null);
    router.push('/login');
  } catch (error) {
    console.error(error);
  }
};

const handleNotificationClick = async (note) => {
  try {
    // Optimistically mark as read by index, not direct object
    const index = notifications.value.findIndex(n => n.id === note.id);
    if (index !== -1) notifications.value[index].read_at = new Date().toISOString();

    await axiosInstance.post(`/notifications/${note.id}/mark-read`);

    // Use id_ticket_type if available
    const ticketId = note.id_ticket_type || note.id_ticket;

    // Delay hiding dropdown AFTER navigation starts
    router.push(`/tickets/${ticketId}`).then(() => {
      showDropdown.value = false;
    });
  } catch (error) {
    console.error("Failed to mark notification as read:", error);
  }
};

const deleteNotification = async (note) => {
  try {
    await axiosInstance.delete(`/notifications/${note.id}`);
    await fetchNotifications(); // Refresh list
  } catch (err) {
    console.error("Failed to delete notification", err);
  }
};

const markAllAsRead = async () => {
  await axiosInstance.post('notifications/mark-read');
  await fetchNotifications();
};

const clearInbox = async () => {
  await axiosInstance.delete('/notifications/clear');
  await fetchNotifications();
};

onMounted(() => {
  const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
      showDropdown.value = false;
    }
  };

  document.addEventListener('click', handleClickOutside);

  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
  });
});

let pollingInterval;

onMounted(() => {
  fetchNotifications(); // Initial load

  // Setup polling
  pollingInterval = setInterval(fetchNotifications, 15000); // every 15s

  const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
      showDropdown.value = false;
    }
  };

  document.addEventListener('click', handleClickOutside);

  onUnmounted(() => {
    clearInterval(pollingInterval); // stop polling
    document.removeEventListener('click', handleClickOutside);
  });
});

watch(showDropdown, (isOpen) => {
  if (isOpen) {
    fetchNotifications(); // Fetch immediately
    pollingInterval = setInterval(fetchNotifications, 15000);
  } else {
    clearInterval(pollingInterval);
  }
});

</script>

<template>
  <header 
    :class="[
      'z-80 flex items-center justify-between bg-white shadow px-6 py-2 fixed top-0 transition-all duration-300 font: font-figtree',
      showSidebar ? 'left-64 w-[calc(100%-16rem)]' : 'left-0 w-full'
    ]"
  >
    <div class="w-full max-w-screen-xl mx-auto flex items-center justify-between">
      
      <!-- Left: Logo -->
      <div class="flex items-center space-x-2">
        <img src="/logo.png" class="h-8" alt="Logo" />
        <span class="font-bold text-lg">SERVICE · DESK</span>
      </div>

      <!-- Right: Notification + Auth Buttons -->
      <div class="flex items-center space-x-4">
        <!-- Notification Bell (SVG) -->
        <div class="relative cursor-pointer" ref="dropdownRef" @click="showDropdown = !showDropdown">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span v-if="unreadCount" class="absolute -top-1 -right-2 bg-red-500 text-white text-xs px-1 rounded-full">
            {{ unreadCount }}
          </span>

          <!-- Dropdown -->
          <div v-if="showDropdown" class="absolute right-0 mt-2 w-96 bg-white border border-gray-300 shadow-lg rounded z-50">
            <div class="px-4 py-2 border-b flex justify-between">
              <span class="font-semibold">Notifications</span>
              <button class="text-xs text-blue-500 cursor-pointer" @click.stop="markAllAsRead">Mark All as Read</button>
            </div>
            <ul class="max-h-64 overflow-y-auto divide-y">
                <li 
                  v-for="note in notifications" 
                  :key="note.id"
                  :class="[
                    'relative group px-4 py-2 hover:bg-gray-50 cursor-pointer',
                    note.read_at ? 'bg-white text-gray-400' : 'bg-blue-50 text-gray-800 font-medium'
                  ]"
                >

                <div @click="handleNotificationClick(note)">
                  <p class="text-xs">
                    {{ note.notification_message }}
                  </p>
                  <p class="text-xs" :class="note.read_at ? 'text-gray-400' : 'text-gray-600'">{{ note.created_at }}</p>
                </div>

                <!-- Delete icon (only shows on hover) -->
                <button 
                  @click.stop="deleteNotification(note)"
                  title="Delete Notification"
                  class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition text-gray-400 hover:text-red-500 cursor-pointer"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </li>
            </ul>
            <div class="px-4 py-2 border-t text-right">
              <button @click.stop="clearInbox" class="text-xs text-red-500 cursor-pointer">Empty Inbox</button>
            </div>
          </div>
        </div>

        <!-- Auth Buttons -->
        <template v-if="user">
          <button 
            @click="logout" 
            class="flex items-center space-x-2 text-gray-800 hover:text-black transition cursor-pointer"
          >
        <!-- Power Icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v9m5.657-4.657a8 8 0 11-11.314 0" />
          </svg>
            <span>Logout</span>
          </button>
        </template>

        <template v-else>
          <RouterLink 
            to="/login" 
            class="flex items-center space-x-2 text-gray-800 hover:text-black transition cursor-pointer"
          >
            <span>Login →</span>
          </RouterLink>
        </template>

      </div>
    </div>
  </header>
</template>