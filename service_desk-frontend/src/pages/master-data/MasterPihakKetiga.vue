<template>
  <div class="pt-16 ml-64 p-6">
    <div class="bg-white shadow overflow-hidden">
      <!-- Header -->
      <div class="bg-red-500 text-white text-lg font-semibold px-6 py-3">
        Pihak Ketiga Management
      </div>

      <!-- Controls -->
      <div class="flex flex-col md:flex-row md:justify-between md:items-center px-6 py-4 gap-4">
        <!-- Left: Tambah User Button -->
        <div class="flex-shrink-0">
          <button @click="openCreate" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded font-medium">
            + Tambah Pihak Ketiga
          </button>
        </div>

        <!-- Center: Entries + Search -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 justify-center mx-auto">
          <div class="text-sm">
            Show
            <select v-model="perPage" @change="fetchPihakKetiga" class="border border-gray-300 px-2 py-1 mx-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-300">
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
                <tr
                v-for="pihakketiga, index) in items"
                :key="pihakketiga.id_pihak_ketiga"
                :class="[index % 2 === 0 ? 'bg-gray-50' : 'bg-white', 'hover:bg-gray-100']"
                >
                    <td class="px-4 py-3 border border-gray-300 truncate">
                        {{ (page - 1) * perPage + index + 1 }}
                    </td>

                    <td class="px-4 py-3 border border-gray-300 truncate">
                        {{ pihakketiga.nama_perusahaan }}
                    </td>

                    <td class="px-4 py-3 border border-gray-300 truncate">
                        {{ pihakketiga.perusahaan_description }}
                    </td>

                    <td class="px-4 py-3 border border-gray-300 space-x-1 w-36">
                        <button
                        @click="openModal(pihakketiga, 'edit')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs"
                        >
                        Edit
                        </button>
                        <button
                        @click="deletePihakKetiga(pihakketiga.id_pihak_ketiga)"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs"
                        >
                        Delete
                        </button>
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
              {{ modalMode === 'edit' ? 'Edit Pihak Ketiga' : 'Tambah Pihak Ketiga' }}
            </h2>
            <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4">
            <!-- Nama Pihak Ketiga -->
            <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pihak Ketiga</label>
            <input
                v-model="form.nama_perusahaan"
                type="text"
                class="w-full border border-gray-300 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300"
                required
            />
            </div>

            <!-- Deskripsi Pihak Ketiga -->
            <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Pihak Ketiga</label>
            <textarea
                v-model="form.perusahaan_description"
                rows="3"
                class="w-full border border-gray-300 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300 resize-y"
            ></textarea>
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
const items = ref([])
const showModal = ref(false)
const modalMode = ref('create')
const sortKey = ref(null)
const sortDirection = ref(null)

const form = ref({
  nama_perusahaan: '',
  perusahaan_description: '',
})

const page = ref(1)
const perPage = ref(10)
const total = ref(0)
const search = ref('')
let searchDebounce = null

const columns = [
  { key: 'id_pihak_ketiga', label: 'No' },
  { key: 'nama_perusahaan', label: 'Nama Perusahaan' },
  { key: 'perusahaan_description', label: 'Deskripsi Perusahaan' },
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

const fetchPihakKetiga = async (useGlobalLoader = true) => {
  if (useGlobalLoader) globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/pihak-ketiga', {
      params: {
        page: page.value,
        per_page: perPage.value,
        search: search.value,
        sort_by: sortKey.value,
        sort_dir: sortDirection.value
      }
    });
    items.value = data.data;
    total.value = data.total;
  } catch (error) {
    console.error('Error fetching pihak ketiga:', error);
  } finally {
    if (useGlobalLoader) globalLoading.value = false;
  }
};

// const fetchLayanans = async () => {
//   const { data } = await axiosInstance.get('/layanans', {
//     params: {
//       page: page.value,
//       per_page: perPage.value,
//       search: search.value,
//       sort_by: sortKey.value,
//       sort_dir: sortDirection.value
//     }
//   })
//   layanans.value = data.data
//   total.value = data.total
// }

const openCreate = () => {
  modalMode.value = 'create'
  form.value = {
    nama_perusahaan: '',
    perusahaan_description: '',
  }
  showModal.value = true
}

const openModal = (pihakketiga, mode) => {
  modalMode.value = mode
  form.value = { ...pihakketiga }
  showModal.value = true
}

const submitForm = async () => {
  globalLoading.value = true;
  try {
    if (modalMode.value === 'edit') {
      await axiosInstance.put(`/pihak-ketiga/${form.value.id_pihak_ketiga}`, form.value);
    } else {
      await axiosInstance.post('/pihak-ketiga', form.value);
    }
    showModal.value = false;
    await fetchPihakKetiga();
  } catch (error) {
    console.error('Error saving pihak ketiga:', error);
  } finally {
    globalLoading.value = false;
  }
};

const deletePihakKetiga = async (id) => {
  if (!confirm('Apakah anda yakin ingin menghapus pihak ketiga ini?')) return;

  globalLoading.value = true;
  try {
    await axiosInstance.delete(`/pihak-ketiga/${id}`);
    await fetchPihakKetiga();
  } catch (error) {
    console.error('Error deleting pihak ketiga:', error);
  } finally {
    globalLoading.value = false;
  }
};

const getStars = (nilai) => {
  const n = Number(nilai) || 0
  const max = 5
  const filled = '★'.repeat(Math.min(n, max))
  const empty = '☆'.repeat(Math.max(max - n, 0))
  return filled + empty
}


const totalPages = computed(() => Math.ceil(total.value / perPage.value))

const handleSearch = () => {
  clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => {
    page.value = 1
    fetchPihakKetiga(false)
  }, 500)
}

onMounted(() => { fetchPihakKetiga() })

watch([page, perPage], fetchPihakKetiga)

watch([sortKey, sortDirection], () => {
  page.value = 1;
  fetchPihakKetiga();
});

</script>