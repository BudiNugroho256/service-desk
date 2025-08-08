<template>
  <div class="pt-16 ml-64 p-6">
    <div class="bg-white shadow overflow-hidden">
      <!-- Header -->
      <div class="bg-red-500 text-white text-lg font-semibold px-6 py-3">
        Master Data Service Desk
      </div>

      <!-- Button Grid -->
      <div class="grid grid-cols-4 gap-4 p-6">
        <RouterLink
          v-for="item in menuItems"
          :key="item.label"
          :to="item.route"
          class="bg-blue-500 text-white text-sm font-medium py-2 px-4 text-center hover:bg-blue-600 transition"
        >
          {{ item.label }}
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
const menuItems = [
  { label: "Divisi", route: "/master-data/divisi" },
  { label: "Layanan", route: "/master-data/layanan" },
  { label: "Permintaan", route: "/master-data/permintaan" },
  { label: "Rootcause", route: "/master-data/rootcause" },
  { label: "Solusi", route: "/master-data/solusi" },
  { label: "Ticket Priority", route: "/master-data/ticket-priority" },
  { label: "Report", route: "/master-data/report" },
];
</script>