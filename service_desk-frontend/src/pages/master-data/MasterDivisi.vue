<template>
  <div class="pt-16 ml-64 p-6">
    <div class="bg-white shadow overflow-hidden">
      
      <!-- Header -->
      <div class="bg-red-500 text-white text-lg font-semibold px-6 py-3">
        Divisi Management
      </div>

      <!-- Controls -->
      <div class="flex flex-col md:flex-row md:justify-between md:items-center px-6 py-4 gap-4">
        <!-- Left: Tambah User Button -->
        <div class="flex-shrink-0">
          <button @click="openCreate" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded font-medium">
            + Tambah Divisi
          </button>
        </div>

        <!-- Center: Entries + Search -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 justify-center mx-auto">
          <div class="text-sm">
            Show
            <select v-model="perPage" @change="fetchDivisions" class="border border-gray-300 px-2 py-1 mx-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-300">
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

        <!-- Right: Back Button -->
        <div class="flex-shrink-0">
          <button @click="$router.back()" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-4 py-2 rounded font-medium">
            Back
          </button>
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
              <tr v-for="(divisi, index) in divisions" :key="divisi.id_divisi" :class="[index % 2 === 0 ? 'bg-gray-50' : 'bg-white', 'hover:bg-gray-100']">
                <td class="px-4 py-3 border border-gray-300 truncate">{{ (page - 1) * perPage + index + 1  }}</td>
                <td class="px-4 py-3 border border-gray-300 truncate">{{ divisi.nama_divisi }}</td>
                <td class="px-4 py-3 border border-gray-300 truncate">{{ divisi.kode_divisi }}</td>
                <td class="px-4 py-3 border border-gray-300 truncate">{{ divisi.divisi_alias }}</td>
                <td class="px-4 py-3 border border-gray-300 truncate">{{ divisi.lantai_divisi }}</td>
                <td class="px-4 py-3 border border-gray-300 space-x-1 w-36">
                  <button @click="openModal(divisi, 'edit')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">Edit</button>
                  <button @click="deleteDivisi(divisi.id_divisi)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Delete</button>
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

          <button
            v-for="p in totalPages"
            :key="p"
            @click="page = p"
            class="px-3 py-1 border border-gray-300"
            :class="page === p ? 'bg-blue-500 text-white' : 'hover:bg-gray-100 text-gray-700'"
          >
            {{ p }}
          </button>

          <button @click="page++" :disabled="page >= totalPages" class="px-3 py-1 border border-gray-300" :class="page >= totalPages ? 'text-gray-400 bg-gray-100' : 'hover:bg-gray-100 text-gray-700'">Next</button>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="showModal" class="fixed backdrop-brightness-50 inset-0 z-100 flex items-center justify-center">
        <div class="bg-white w-full max-w-md rounded shadow-lg p-6">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold">
              {{ modalMode === 'edit' ? 'Edit Divisi' : 'Tambah Divisi' }}
            </h2>
            <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4">
            <!-- Nama Divisi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Divisi</label>
              <input
                v-model="form.nama_divisi"
                type="text"
                class="w-full border border-gray-300 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300"
                required
              />
            </div>

            <!-- Kode Divisi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kode Divisi</label>
              <input
                v-model="form.kode_divisi"
                type="text"
                class="w-full border border-gray-300 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300"
                required
              />
            </div>

            <!-- Divisi Alias -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Divisi Alias</label>
              <input
                v-model="form.divisi_alias"
                type="text"
                class="w-full border border-gray-300 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300"
                required
              />
            </div>


            <!-- Lantai Divisi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Lantai</label>
              <input
                v-model="form.lantai_divisi"
                type="number"
                class="w-full border border-gray-300 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300"
                required
              />
            </div>

            <!-- Buttons -->
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
import { ref, onMounted, watch, computed, inject } from 'vue'
import axiosInstance from '@/lib/axios'

const globalLoading = inject('globalLoading')
const divisions = ref([])
const showModal = ref(false)
const modalMode = ref('create')
const sortKey = ref(null)
const sortDirection = ref(null)

const form = ref({
  nama_divisi: '',
  kode_divisi: '',
  divisi_alias: '',
  lantai_divisi: ''
});

const page = ref(1)
const perPage = ref(10)
const total = ref(0)
const search = ref('')
let searchDebounce = null

const columns = [
  { key: 'id_divisi', label: 'No' },
  { key: 'nama_divisi', label: 'Nama Divisi' },
  { key: 'kode_divisi', label: 'Kode Divisi' },
  { key: 'divisi_alias', label: 'Divisi Alias' },
  { key: 'lantai_divisi', label: 'Lantai Divisi' },
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

const fetchDivisions = async (useGlobalLoader = true) => {
  if (useGlobalLoader) globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/divisions', {
      params: {
        page: page.value,
        per_page: perPage.value,
        search: search.value,
        sort_by: sortKey.value,
        sort_dir: sortDirection.value
      }
    });
    divisions.value = data.data;
    total.value = data.total;
  } catch (error) {
    console.error('Error fetching divisions:', error);
  } finally {
    if (useGlobalLoader) globalLoading.value = false;
  }
};

const openCreate = () => {
  modalMode.value = 'create';
  form.value = {
    nama_divisi: '',
    kode_divisi: '',
    divisi_alias: '',
    lantai_divisi: ''
  };
  showModal.value = true;
};

const openModal = (divisi, mode) => {
  modalMode.value = mode;
  form.value = { ...divisi };
  showModal.value = true;
}

const submitForm = async () => {
  globalLoading.value = true;
  try {
    if (modalMode.value === 'edit') {
      await axiosInstance.put(`/divisions/${form.value.id_divisi}`, form.value);
    } else {
      await axiosInstance.post('/divisions', form.value);
    }
    showModal.value = false;
    await fetchDivisions();
  } catch (error) {
    console.error('Error submitting division:', error);
  } finally {
    globalLoading.value = false;
  }
};


const deleteDivisi = async (id) => {
  if (!confirm('Apakah anda yakin ingin menghapus divisi ini?')) return;
  globalLoading.value = true;
  try {
    await axiosInstance.delete(`/divisions/${id}`);
    await fetchDivisions();
  } catch (error) {
    console.error('Error deleting division:', error);
  } finally {
    globalLoading.value = false;
  }
};


const totalPages = computed(() => Math.ceil(total.value / perPage.value))

const handleSearch = () => {
  clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => {
    page.value = 1
    fetchDivisions(false)
  }, 500)
}

onMounted(fetchDivisions)
watch([page, perPage], fetchDivisions)

watch([sortKey, sortDirection], () => {
  page.value = 1;
  fetchDivisions();
});

</script>
