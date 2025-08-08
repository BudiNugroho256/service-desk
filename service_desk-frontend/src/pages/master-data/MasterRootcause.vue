<template>
  <div class="pt-16 ml-64 p-6">
    <div class="bg-white shadow overflow-hidden">
      <!-- Header -->
      <div class="bg-red-500 text-white text-lg font-semibold px-6 py-3">
        Rootcause Management
      </div>

      <!-- Controls -->
      <div class="flex flex-col md:flex-row md:justify-between md:items-center px-6 py-4 gap-4">
        <!-- Left: Tambah User Button -->
        <div class="flex-shrink-0">
          <button @click="openCreate" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded font-medium">
            + Tambah Rootcause
          </button>
        </div>

        <!-- Center: Entries + Search -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 justify-center mx-auto">
          <div class="text-sm">
            Show
            <select v-model="perPage" @change="fetchRootcauses" class="border border-gray-300 px-2 py-1 mx-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-300">
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
                <tr v-for="(rootcause, index) in rootcauses" :key="rootcause.id_rootcause" :class="[index % 2 === 0 ? 'bg-gray-50' : 'bg-white', 'hover:bg-gray-100']">
                  <td class="px-4 py-3 border border-gray-300 truncate">{{ (page - 1) * perPage + index + 1 }}</td>
                  <td class="px-4 py-3 border border-gray-300 truncate">{{ rootcause.group_layanan && rootcause.nama_layanan ? rootcause.group_layanan + ' - ' + rootcause.nama_layanan : rootcause.group_layanan || rootcause.nama_layanan || '-' }}</td>
                  <td class="px-4 py-3 border border-gray-300 truncate">{{ rootcause.nama_rootcause }}</td>
                  <td class="px-4 py-3 border border-gray-300 truncate">{{ rootcause.rootcause_description || '-' }}</td>
                  <td class="px-4 py-3 border border-gray-300 space-x-1 truncate">
                    <button @click="openModal(rootcause, 'edit')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">Edit</button>
                    <button @click="deleteRootcause(rootcause.id_rootcause)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Delete</button>
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
              {{ modalMode === 'edit' ? 'Edit Rootcause' : 'Tambah Rootcause' }}
            </h2>
            <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4">

            <!-- Group Layanan - Nama Layanan -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Layanan</label>
              <div class="w-fit border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300">
                <Multiselect
                  v-model="form.id_layanan"
                  :options="activeLayananOptions"
                  :track-by="'id_layanan'"
                  label="nama_layanan" 
                  :custom-label="customLayananLabel"
                  :multiple="false"
                  :searchable="true"
                  :close-on-select="true"
                  :allow-empty="false"
                  placeholder="Pilih Layanan"
                  class="text-sm"
                />
              </div>
            </div>

            <!-- Rootcause -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Rootcause</label>
              <input v-model="form.nama_rootcause" type="text" class="w-full border border-gray-300 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300" required />
            </div>

            <!-- Deskripsi Rootcause -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Rootcause</label>
              <textarea
                v-model="form.rootcause_description"
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
import { ref, onMounted, watch, computed, nextTick, inject } from 'vue'
import axiosInstance from '@/lib/axios'
import Multiselect from 'vue-multiselect'

// === Data Stores ===
const rootcauses = ref([])
const layanans = ref([])
// const groupOptions = ref([])
// const filteredLayananOptions = ref([])
const globalLoading = inject('globalLoading')


const showModal = ref(false)
const modalMode = ref('create')

const sortKey = ref(null)
const sortDirection = ref(null)

const form = ref({
  // group_layanan: '',
  id_layanan: '',
  nama_rootcause: '',
  rootcause_description: ''
})

// === Pagination & Search ===
const page = ref(1)
const perPage = ref(10)
const total = ref(0)
const search = ref('')
let searchDebounce = null

// === Columns ===
const columns = [
  { key: 'id_rootcause', label: 'No' },
  { key: 'group_layanan', label: 'Layanan' },
  { key: 'nama_rootcause', label: 'Rootcause' },
  { key: 'rootcause_description', label: 'Diskripsi Rootcause' },
  { key: 'action', label: 'Action' },
]

// === Computed Pagination ===
const totalPages = computed(() => {
  const totalNum = Number(total.value)
  const perPageNum = Number(perPage.value)
  return isFinite(totalNum) && isFinite(perPageNum) && perPageNum > 0
    ? Math.ceil(totalNum / perPageNum)
    : 1
})

// === Sorting Toggle ===
const toggleSort = (key) => {
  if (sortKey.value !== key) {
    sortKey.value = key
    sortDirection.value = 'asc'
  } else if (sortDirection.value === 'asc') {
    sortDirection.value = 'desc'
  } else {
    sortKey.value = null
    sortDirection.value = null
  }
}

// === API Call: Rootcauses ===
const fetchRootcauses = async (useGlobalLoader = true) => {
  if (useGlobalLoader) globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/rootcauses', {
      params: {
        page: page.value,
        per_page: perPage.value,
        search: search.value,
        sort_by: sortKey.value,
        sort_dir: sortDirection.value
      }
    });
    rootcauses.value = data.data || [];
    total.value = data.total || 0;
  } catch (err) {
    console.error('Error fetching rootcauses:', err);
    rootcauses.value = [];
    total.value = 0;
  } finally {
    if (useGlobalLoader) globalLoading.value = false;
  }
};


const fetchLayanans = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/layanans', { params: { page: 1, per_page: 100 } });
    layanans.value = data.data;
  } catch (err) {
    console.error('Error fetching layanans:', err);
    layanans.value = [];
  } finally {
    globalLoading.value = false;
  }
};


const customLayananLabel = (option) => {
  if (!option) return '-'
  return `${option.group_layanan || ''} - ${option.nama_layanan || ''}`
}

const activeLayananOptions = computed(() =>
  layanans.value.filter(l => l.status_layanan === 'Aktif')
)


// === Modal Open/Create/Edit ===
const openCreate = () => {
  modalMode.value = 'create'
  form.value = {
    id_layanan: '',
    nama_rootcause: '',
    rootcause_description: ''
  }
  showModal.value = true
}

const openModal = async (item, mode) => {
  modalMode.value = mode

  // Set initial form values
  form.value = {
    id_layanan: null,
    nama_rootcause: item.nama_rootcause,
    rootcause_description: item.rootcause_description,
    id_rootcause: item.id_rootcause,
  }

  // Wait for DOM and reactive updates to settle
  await nextTick()

  // Then safely assign the selected layanan object
  form.value.id_layanan = layanans.value.find(
    l => l.id_layanan === item.id_layanan
  ) || null


  showModal.value = true
}


// === Submit Form ===
const submitForm = async () => {
  globalLoading.value = true;
  try {
    const payload = {
      id_layanan: form.value.id_layanan?.id_layanan,
      nama_rootcause: form.value.nama_rootcause,
      rootcause_description: form.value.rootcause_description
    };
    if (modalMode.value === 'edit') {
      await axiosInstance.put(`/rootcauses/${form.value.id_rootcause}`, payload);
    } else {
      await axiosInstance.post('/rootcauses', payload);
    }
    showModal.value = false;
    await fetchRootcauses();
  } catch (err) {
    console.error('Error saving rootcause:', err);
  } finally {
    globalLoading.value = false;
  }
};


// === Delete Rootcause ===
const deleteRootcause = async (id) => {
  if (!confirm('Are you sure you want to delete this rootcause?')) return;
  
  globalLoading.value = true;
  try {
    await axiosInstance.delete(`/rootcauses/${id}`);
    await fetchRootcauses();
  } catch (err) {
    console.error('Error deleting rootcause:', err);
  } finally {
    globalLoading.value = false;
  }
};


// === Debounced Search ===
const handleSearch = () => {
  clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => {
    page.value = 1
    fetchRootcauses(false)
  }, 500)
}

// === Lifecycle ===
onMounted(() => {
  fetchRootcauses()
  fetchLayanans()
})

// === Watchers ===
watch([page, perPage], fetchRootcauses)

watch([sortKey, sortDirection], () => {
  page.value = 1
  fetchRootcauses()
})
</script>
