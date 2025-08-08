<template>
  <div class="pt-16 ml-64 p-6">
    <div class="bg-white shadow overflow-hidden">

      <!-- Header -->
      <div class="bg-red-500 text-white text-lg font-semibold px-6 py-3">
        Permissions Management
      </div>

      <!-- Controls -->
      <div class="flex flex-col md:flex-row md:justify-between md:items-center px-6 py-4 gap-4">
        <!-- Create Permission -->
        <div class="flex-shrink-0">
          <button @click="openCreate" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded font-medium">
            + Tambah Permission
          </button>
        </div>

        <!-- Search + Entries -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 justify-center mx-auto">
          <div class="text-sm">
            Show
            <select v-model="perPage" @change="fetchPermissions" class="border border-gray-300 px-2 py-1 mx-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-300">
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
            entries
          </div>

          <div class="relative">
            <input
              type="text"
              v-model="search"
              @input="handleSearch"
              placeholder="Search"
              class="border border-gray-300 pl-8 pr-2 py-1 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300"
            />
            <svg class="absolute left-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="p-5">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left text-gray-800">
            <thead class="bg-gray-900 text-white text-xs uppercase">
              <tr>
                <th
                  v-for="column in columns"
                  :key="column.key"
                  @click="toggleSort(column.key)"
                  class="px-4 py-3 whitespace-nowrap border border-gray-300 cursor-pointer"
                >
                  <div class="flex justify-between items-center w-full">
                    <span>{{ column.label }}</span>
                    <span>
                      <div class="flex flex-col items-end pl-5">
                        <template v-if="sortKey === column.key">
                          <span
                            :class="[
                              'w-0 h-0 border-l-4 border-r-4 border-b-4',
                              sortDirection === 'asc' ? 'border-transparent border-b-white' : 'border-transparent border-b-gray-600'
                            ]"
                          ></span>
                          <span
                            :class="[
                              'w-0 h-0 border-l-4 border-r-4 border-t-4 mt-1',
                              sortDirection === 'desc' ? 'border-transparent border-t-white' : 'border-transparent border-t-gray-600'
                            ]"
                          ></span>
                        </template>
                        <template v-else>
                          <!-- Neutral state (both dimmed) -->
                          <span class="w-0 h-0 border-l-4 border-r-4 border-b-4 border-transparent border-b-gray-500"></span>
                          <span class="w-0 h-0 border-l-4 border-r-4 border-t-4 mt-1 border-transparent border-t-gray-500"></span>
                        </template>
                      </div>
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(permission, index) in permissions" :key="permission.id" :class="[index % 2 === 0 ? 'bg-gray-50' : 'bg-white', 'hover:bg-gray-100']">
                <td class="px-4 py-3 border border-gray-300">{{ (page - 1) * perPage + index + 1 }}</td>
                <td class="px-4 py-3 border border-gray-300">{{ permission.name }}</td>
                <td class="px-4 py-3 border border-gray-300 space-x-1">
                  <button @click="openEdit(permission)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">Edit</button>
                  <button @click="deletePermission(permission.id)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex justify-between items-center px-6 py-4 text-sm text-gray-600">
        <div>
            Showing {{ (page - 1) * perPage + 1 }} to {{ Math.min(page * perPage, total) }} of {{ total }} entries
        </div>
        <div class="flex space-x-1">
          <button @click="page--" :disabled="page === 1" class="px-3 py-1 border border-gray-300" :class="page === 1 ? 'text-gray-400 bg-gray-100' : 'hover:bg-gray-100 text-gray-700'">Previous</button>

          <span v-for="(p, i) in visiblePages" :key="`page-${i}`">
            <button v-if="p !== '...'" @click="page = p" class="px-3 py-1 border border-gray-300"
              :class="p === page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100 text-gray-700'">
              {{ p }}
            </button>
            <span v-else>
              <input v-if="jumpPage === i" v-model="jumpTarget" @keydown.enter="goToJumpPage" @blur="jumpPage = null"
                type="number" min="1" :max="totalPages"
                class="w-14 px-1 py-1 border text-center text-sm" placeholder="Go" />
              <button v-else @click="activateJump(i)" class="px-3 py-1 border border-gray-300 text-gray-600 hover:bg-gray-100">
                ...
              </button>
            </span>
          </span>

          <button @click="page++" :disabled="page >= totalPages" class="px-3 py-1 border border-gray-300"
            :class="page >= totalPages ? 'text-gray-400 bg-gray-100' : 'hover:bg-gray-100 text-gray-700'">Next</button>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 backdrop-brightness-50 z-100 flex items-center justify-center">
        <div class="bg-white w-full max-w-lg max-h-[90vh] rounded shadow-lg p-6 overflow-y-auto">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold">
              {{ modalMode === 'edit' ? 'Edit Permission' : 'Create Permission' }}
            </h2>
            <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Permission Name</label>
              <input v-model="form.name" type="text"
                class="w-full border border-gray-300 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300"
              />
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t mt-4">
              <button type="button" @click="showModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
              <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Submit</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, inject } from 'vue'
import axiosInstance from '@/lib/axios'

const globalLoading = inject('globalLoading');
const permissions = ref([]);
const page = ref(1);
const perPage = ref(10);
const total = ref(0);
const search = ref('');
const jumpPage = ref(null);
const jumpTarget = ref('');
const sortKey = ref(null);
const sortDirection = ref(null);
let searchDebounce = null;

const showModal = ref(false);
const modalMode = ref('create');
const form = ref({ name: '' });

const columns = [
  { key: 'id', label: 'No' },
  { key: 'name', label: 'Name' },
  { key: 'action', label: 'Action' },
]

const toggleSort = (key) => {
  if (sortKey.value !== key) {
    sortKey.value = key
    sortDirection.value = 'asc'
  } else if (sortDirection.value === 'asc') {
    sortDirection.value = 'desc'
  } else if (sortDirection.value === 'desc') {
    sortKey.value = null
    sortDirection.value = null
  } else {
    sortDirection.value = 'asc'
  }
}

const fetchPermissions = async (useGlobalLoader = true) => {
  if (useGlobalLoader) globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/permissions', {
      params: {
        page: page.value,
        per_page: perPage.value,
        search: search.value,
        sort_by: sortKey.value,
        sort_direction: sortDirection.value,
      }
    });
    permissions.value = data.data;
    total.value = data.total;
  } catch (error) {
    console.error('Error fetching permissions:', error);
  } finally {
    if (useGlobalLoader) globalLoading.value = false;
  }
};

const openCreate = () => {
  modalMode.value = 'create';
  form.value = { name: '' };
  showModal.value = true;
};

const openEdit = async (permission) => {
  modalMode.value = 'edit';
  form.value = { name: permission.name, id: permission.id };
  showModal.value = true;
};

const submitForm = async () => {
  globalLoading.value = true;
  try {
    if (modalMode.value === 'edit') {
      await axiosInstance.put(`/permissions/${form.value.id}`, { name: form.value.name });
    } else {
      await axiosInstance.post('/permissions', { name: form.value.name });
    }
    showModal.value = false;
    await fetchPermissions();
  } catch (error) {
    console.error('Error saving permission:', error);
  } finally {
    globalLoading.value = false;
  }
};

const deletePermission = async (id) => {
  if (!confirm('Are you sure you want to delete this permission?')) return;

  globalLoading.value = true;
  try {
    await axiosInstance.delete(`/permissions/${id}`);
    await fetchPermissions();
  } catch (error) {
    console.error('Error deleting permission:', error);
  } finally {
    globalLoading.value = false;
  }
};



const totalPages = computed(() => Math.ceil(total.value / perPage.value));

const visiblePages = computed(() => {
  const totalP = totalPages.value;
  const current = page.value;
  const pages = [];
  if (totalP <= 7) {
    for (let i = 1; i <= totalP; i++) pages.push(i);
  } else {
    if (current <= 4) pages.push(1, 2, 3, 4, 5, '...', totalP);
    else if (current >= totalP - 3) pages.push(1, '...', totalP - 4, totalP - 3, totalP - 2, totalP - 1, totalP);
    else pages.push(1, '...', current - 1, current, current + 1, '...', totalP);
  }
  return pages;
});

const handleSearch = () => {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    page.value = 1;
    fetchPermissions(false);
  }, 500);
};

const activateJump = (dotIndex) => {
  jumpPage.value = dotIndex;
  jumpTarget.value = '';
};

const goToJumpPage = () => {
  const p = parseInt(jumpTarget.value);
  if (!isNaN(p) && p >= 1 && p <= totalPages.value) {
    page.value = p;
    jumpPage.value = null;
  }
};

onMounted(fetchPermissions);

watch([page, perPage], fetchPermissions);
watch([sortKey, sortDirection], () => {
  page.value = 1;
  fetchPermissions();
});
</script>
