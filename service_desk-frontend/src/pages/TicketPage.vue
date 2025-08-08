<template>
  <div class="pt-16 ml-64 p-6">
    <div class="bg-white shadow overflow-hidden p-5">

      <!-- Controls -->
      <div class="flex flex-col md:flex-row md:justify-center md:items-center gap-4 px-6 py-4">
        <!-- Status Filter -->
          <div class="relative" ref="statusDropdown">
            Status:
            <button @click.stop="showStatusDropdown = !showStatusDropdown" class="border border-gray-300 px-2 py-1 mx-1 text-sm cursor-pointer">
              {{ selectedStatuses.length > 0 ? selectedStatuses.join(', ') : 'Default (Open, On Progress, Closed)' }}
            </button>

            <div v-if="showStatusDropdown" class="absolute bg-white border border-gray-300 mt-1 z-10 p-2 rounded shadow w-48">
              <div v-for="status in ticketStatusOptions" :key="status" class="flex items-center">
                <input type="checkbox" :value="status" v-model="selectedStatuses" class="mr-2" />
                <label>{{ status }}</label>
              </div>
              <div class="flex justify-between mt-2">
                <button class="text-sm text-blue-600 cursor-pointer hover:text-blue-700" @click="applyStatusFilter">Apply</button>
                <button class="text-sm text-red-500 cursor-pointer hover:text-red-600" @click="clearStatusFilter">Clear</button>
              </div>
            </div>
          </div>
        <div>
          Show
          <select v-model="perPage" 
            @change="fetchTickets" 
            class="border border-gray-300 px-2 py-1 mx-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300 cursor-pointer">
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
            class="border border-gray-300 pl-8 pr-2 py-1 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300"
          />
          <svg class="absolute left-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full h-full text-sm text-left text-gray-800">
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
              v-for="(ticket, index) in tickets"
              :key="ticket.id_ticket"
              :class="[
                index % 2 === 0 ? 'bg-gray-50' : 'bg-white',
                'hover:bg-gray-100'
              ]"
            >
              <td class="px-4 py-3 border border-gray-300 font-medium text-blue-600 truncate">
                <router-link
                  :to="`/tickets/${ticket.id_ticket_type || ticket.id_ticket}`"
                  class="hover:underline"
                >
                  {{ ticket.id_ticket_type || ticket.id_ticket }}
                </router-link>
              </td>
              <td class="px-4 py-3 border border-gray-300 font-semibold truncate" :class="ticket.status_overdue === 'Overdue' ? 'text-red-500' : 'text-green-600'">
                {{ ticket.status_overdue }}
              </td>
              <td class="px-4 py-3 border border-gray-300 truncate text-red-600">{{ ticket.due_date }}</td>
              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.ticket_title }}</td>

              <!-- Ticket Type -->
              <!-- <td class="px-4 py-3 border border-gray-300">
                <Multiselect
                  v-model="ticket.ticket_type"
                  :options="ticketTypeOptions"
                  :multiple="false"
                  :searchable="false"
                  :close-on-select="true"
                  :allow-empty="false"
                  placeholder="Pilih Tipe"
                  @update:modelValue="(val) => handleTicketTypeChange(ticket, val)"
                  class="custom-multiselect text-sm font-medium"
                  :class="typeColorClass(ticket.ticket_type)"
                />
              </td> -->
              <td class="px-4 py-3 border border-gray-300 text-sm font-medium truncate" :class="typeColorClass(ticket.ticket_type)">{{ ticket.ticket_type }}</td>

              <!-- Layanan Dropdown -->
              <!-- <td class="px-4 py-3 border border-gray-300">
                <Multiselect
                  v-model="ticket.id_layanan"
                  :options="activeLayananOptions"
                  :track-by="'id_layanan'"
                  label="nama_layanan"
                  :custom-label="customLayananLabel"
                  :multiple="false"
                  :searchable="true"
                  :close-on-select="true"
                  :allow-empty="false"
                  placeholder="Pilih Layanan"
                  @update:modelValue="(val) => handleLayanans(ticket, val)"
                  class="custom-multiselect text-sm font-medium"
                />
              </td> -->
              
              <td class="px-4 py-3 border border-gray-300 text-sm truncate">{{ customLayananLabel(ticket.id_layanan) }}</td>
              
              <!-- <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.group_layanan }}</td> -->

              <!-- Status Dropdown -->
              <!-- <td class="px-4 py-3 border border-gray-300">
                <Multiselect
                  v-model="ticket.ticket_status"
                  :options="ticketStatusOptions"
                  :multiple="false"
                  :searchable="false"
                  :close-on-select="true"
                  :allow-empty="false"
                  placeholder="Pilih Status"
                  @update:modelValue="(val) => handleTicketStatusChange(ticket, val)"
                  class="custom-multiselect text-sm font-medium"
                  :class="statusColorClass(ticket.ticket_status)"
                />
              </td> -->

              <td class="px-4 py-3 border border-gray-300 text-sm font-medium truncate" :class="statusColorClass(ticket.ticket_status)">{{ ticket.ticket_status }}</td>

              <!-- <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.tingkat_priority }}</td> -->

              <!-- Prioritas -->
              <!-- <td class="px-4 py-3 border border-gray-300">
                <Multiselect
                  v-model="ticket.id_ticket_priority"
                  :options="priorities"
                  :track-by="'id_ticket_priority'"
                  label="tingkat_priority"
                  :custom-label="customPriorityLabel"
                  :multiple="false"
                  :searchable="false"
                  :close-on-select="true"
                  :allow-empty="false"
                  placeholder="Pilih Prioritas"
                  @update:modelValue="(val) => handlePriorities(ticket, val)"
                  class="custom-multiselect text-sm font-medium"
                  :class="priorityColorClass(ticket.tingkat_priority)"
                />
              </td> -->
              <td class="px-4 py-3 border border-gray-300 text-sm font-medium truncate" :class="priorityColorClass(ticket.tingkat_priority)">{{ customPriorityLabel(ticket.id_ticket_priority) }}</td>
              
              <!-- PIC Ticket -->
              <!-- <td class="px-4 py-3 border border-gray-300">
                <Multiselect
                  v-model="ticket.id_pic_ticket"
                  :options="users"
                  :track-by="'id_user'"
                  :label="'nama_user'"
                  :multiple="false"
                  :searchable="true"
                  :close-on-select="true"
                  :allow-empty="true"
                  placeholder="Pilih PIC Tiket"
                  @update:modelValue="(val) => handlePicChange(ticket, val)"
                  class="custom-multiselect text-sm font-medium"
                />
              </td> -->

              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.pic_tiket }}</td>
              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.nama_user }}</td>
              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.nama_divisi }}</td>

              <!-- Assigned Status -->
              <!-- <td class="px-4 py-3 border border-gray-300">
                <Multiselect
                  v-model="ticket.assigned_status"
                  :options="assignedStatusOptions"
                  :multiple="false"
                  :searchable="false"
                  :close-on-select="true"
                  :allow-empty="false"
                  placeholder="Pilih Status"
                  @update:modelValue="(val) => handleAssignedStatusChange(ticket, val)"
                  class="custom-multiselect text-sm font-medium"
                  :class="assignedColorClass(ticket.assigned_status)"
                />
              </td> -->
              <td class="px-4 py-3 border border-gray-300 text-sm font-medium truncate" :class="assignedColorClass(ticket.assigned_status)">{{ ticket.assigned_status }}</td>


              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.assigned_date }}</td>

              <!-- PIC Escalation -->
              <!-- <td class="px-4 py-3 border border-gray-300">
                <Multiselect
                  v-model="ticket.escalation_to"
                  :options="users"
                  :track-by="'id_user'"
                  :label="'nama_user'"
                  :multiple="false"
                  :searchable="true"
                  :close-on-select="true"
                  :allow-empty="true"
                  placeholder="Pilih PIC Eskalasi"
                  @update:modelValue="(val) => handleEscalation(ticket, val)"
                  class="custom-multiselect text-sm font-medium"
                />
              </td> -->
              
              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.pic_eskalasi }}</td>
              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.tanggal_eskalasi }}</td>
              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.created_on }}</td>
              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.created_by }}</td>
              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.last_updated_on }}</td>
              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.last_updated_by }}</td>
              <td class="px-4 py-3 border border-gray-300 truncate">{{ ticket.closed_date }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex justify-between items-center px-6 py-4 text-sm text-gray-600">
        <div>
          Showing {{ (page - 1) * perPage + 1 }} to
          {{ Math.min(page * perPage, total) }} of {{ total }} entries
        </div>
        <div class="flex space-x-1">
            <button
                @click="page--"
                :disabled="page === 1"
                class="px-3 py-1 border border-gray-300"
                :class="[
                    page === 1
                    ? 'text-gray-400 bg-gray-100'
                    : 'hover:bg-gray-100 text-gray-700'
                ]"
                >
                Previous
            </button>

          <span v-for="(p, i) in visiblePages" :key="`page-${i}`">
            <button
            v-if="p !== '...'"
            @click="page = p"
            class="px-3 py-1 border border-gray-300"
            :class="[
                p === page
                ? 'bg-blue-500 text-white'
                : 'hover:bg-gray-100 text-gray-700'
            ]"
            >
            {{ p }}
            </button>
            <span v-else>
              <input v-if="jumpPage === i" v-model="jumpTarget" @keydown.enter="goToJumpPage" @blur="jumpPage = null"
                type="number" min="1" :max="totalPages"
                class="w-14 px-1 py-1 border border-gray-300 text-center text-sm" placeholder="Go" />
              <button v-else @click="activateJump(i)" class="px-3 py-1 border border-gray-300 text-gray-600 hover:bg-gray-100">
                ...
              </button>
            </span>
          </span>
          <button
                @click="page++"
                :disabled="page >= totalPages"
                class="px-3 py-1 border border-gray-300"
                :class="[
                    page >= totalPages
                    ? 'text-gray-400 bg-gray-100'
                    : 'hover:bg-gray-100 text-gray-700'
                ]"
                >
                Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, onUnmounted, watch, computed, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axiosInstance from '@/lib/axios'

const globalLoading = inject('globalLoading');
const ticketTypeOptions = ['Request', 'Incident']
const ticketStatusOptions = ['Open', 'On Progress', 'Closed', 'Cancelled']
const assignedStatusOptions = ['Assigned', 'Unassigned']
const groupOptions = computed(() => {
  const set = new Set()
  layanans.value.forEach(l => set.add(l.group_layanan))
  return Array.from(set)
})

const route = useRoute();
const router = useRouter();
const tickets = ref([])
const page = ref(1)
const perPage = ref(10)
const total = ref(0)
const search = ref('')
const jumpPage = ref(null)
const jumpTarget = ref('')
const users = ref([])
const userSearch = ref('')
const priorities = ref([])
const prioritySearch = ref('')
const layanans = ref([])
const layananSearch = ref('')
const statusFilter = ref('')
const sortKey = ref(null)
const sortDirection = ref('asc')
const showStatusDropdown = ref(false)
const selectedStatuses = ref([])
const statusDropdown = ref(null)
let userFetchDebounce = null
let priorityFetchDebounce = null
let layananFetchDebounce = null
let searchDebounce = null
let ticketPollingInterval;


const totalPages = computed(() => Math.ceil(total.value / perPage.value))

const visiblePages = computed(() => {
  const totalP = totalPages.value
  const current = page.value
  const pages = []

  if (totalP <= 7) {
    for (let i = 1; i <= totalP; i++) pages.push(i)
  } else {
    if (current <= 4) {
      pages.push(1, 2, 3, 4, 5, '...', totalP)
    } else if (current >= totalP - 3) {
      pages.push(1, '...', totalP - 4, totalP - 3, totalP - 2, totalP - 1, totalP)
    } else {
      pages.push(1, '...', current - 1, current, current + 1, '...', totalP)
    }
  }

  return pages
})

const pollTickets = () => fetchTickets(false); // No global loader

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

// const sortedTickets = computed(() => {
//   if (!sortKey.value || !sortDirection.value) {
//     return tickets.value
//   }

//   return [...tickets.value].sort((a, b) => {
//     const valA = a[sortKey.value]
//     const valB = b[sortKey.value]

//     if (valA == null) return 1
//     if (valB == null) return -1

//     if (typeof valA === 'string' && typeof valB === 'string') {
//       return sortDirection.value === 'asc'
//         ? valA.localeCompare(valB)
//         : valB.localeCompare(valA)
//     }

//     return sortDirection.value === 'asc'
//       ? valA > valB ? 1 : -1
//       : valA < valB ? 1 : -1
//   })
// })

const columns = [
  { key: 'id_ticket_type', label: 'ID Tiket' },
  { key: 'status_overdue', label: 'Status Overdue' },
  { key: 'due_date', label: 'Due Date' },
  { key: 'ticket_title', label: 'Title' },
  { key: 'ticket_type', label: 'Jenis Tiket' },
  { key: 'group_layanan', label: 'Layanan' },
  { key: 'ticket_status', label: 'Status Tiket' },
  { key: 'tingkat_priority', label: 'Prioritas (Tingkat - Dampak - Urgensi)' },
  { key: 'pic_tiket', label: 'PIC Ticket' },
  { key: 'nama_user', label: 'Nama User' },
  { key: 'nama_divisi', label: 'Divisi User' },
  { key: 'assigned_status', label: 'Assigned Status' },
  { key: 'assigned_date', label: 'Assigned Date' },
  { key: 'pic_eskalasi', label: 'PIC Eskalasi' },
  { key: 'tanggal_eskalasi', label: 'Tanggal Eskalasi' },
  { key: 'created_on', label: 'Created On' },
  { key: 'created_by', label: 'Created By' },
  { key: 'last_updated_on', label: 'Last Updated On' },
  { key: 'last_updated_by', label: 'Last Updated By' },
  { key: 'closed_date', label: 'Tanggal Close' },
]

const fetchTickets = async (useGlobalLoader = true) => {
  if (useGlobalLoader) globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/tickets', {
      params: {
        page: page.value,
        search: search.value,
        per_page: perPage.value,
        sort_by: sortKey.value,
        sort_direction: sortDirection.value,
        filter_status: statusFilter.value || undefined,
      }
    });

    tickets.value = data.data.map(ticket => {
      const matchedUser = users.value.find(user => user.id_user === ticket.id_pic_ticket);
      ticket.id_pic_ticket = matchedUser || null;
      const matchedEscalation = users.value.find(user => user.id_user === ticket.escalation_to);
      ticket.escalation_to = matchedEscalation || null;
      const matchedPriority = priorities.value.find(p => p.id_ticket_priority === ticket.id_ticket_priority);
      ticket.id_ticket_priority = matchedPriority || null;
      const matchedLayanan = layanans.value.find(l => l.id_layanan === ticket.id_layanan);
      ticket.id_layanan = matchedLayanan || null;
      return ticket;
    });

    total.value = data.total;
  } catch (error) {
    console.error('Error fetching tickets:', error);
  } finally {
    if (useGlobalLoader) globalLoading.value = false;
  }
};

const fetchUsers = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/users', {
      params: {
        search: userSearch.value,
        per_page: 20,
      }
    });
    users.value = data.data;
  } catch (error) {
    console.error('Error fetching users:', error);
  } finally {
    globalLoading.value = false;
  }
};


const fetchPriorities = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/priorities', {
      params: {
        search: prioritySearch.value,
        per_page: 20,
      }
    });
    priorities.value = data.data;
  } catch (error) {
    console.error('Error fetching priorities:', error);
  } finally {
    globalLoading.value = false;
  }
};


const fetchLayanans = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/layanans', {
      params: {
        search: layananSearch.value,
        per_page: 20,
      }
    });
    layanans.value = data.data;
  } catch (error) {
    console.error('Error fetching layanans:', error);
  } finally {
    globalLoading.value = false;
  }
};


const customLayananLabel = (option) => {
  if (!option) return ''
  return `${option.group_layanan || ''} - ${option.nama_layanan || ''}`
}

const activeLayananOptions = computed(() =>
  layanans.value.filter(l => l.status_layanan === 'Aktif')
)

const customPriorityLabel = (option) => {
  if (!option) return ''
  return `${option.tingkat_priority || ''} - ${option.tingkat_dampak || ''} - ${option.tingkat_urgensi || ''}`
}

const handleTicketTypeChange = async (ticket, newValue) => {
  ticket.ticket_type = newValue
  await updateTicketType(ticket)
}

const updateTicketType = async (ticket) => {
    await axiosInstance.put(`/tickets/${ticket.id_ticket}`, {
      ticket_type: ticket.ticket_type,
      inline_update: true
    })

    fetchTickets()
}

const handleTicketStatusChange = async (ticket, newValue) => {
  ticket.ticket_status = newValue
  await updateTicketStatus(ticket)
}

const updateTicketStatus = async (ticket) => {
  await axiosInstance.put(`/tickets/${ticket.id_ticket}`, {
    ticket_status: ticket.ticket_status,
    inline_update: true
  })

  fetchTickets()
}

// const handleAssignedStatusChange = async (ticket, newValue) => {
//   ticket.assigned_status = newValue
//   await updateAssignedStatus(ticket)
// }

// const updateAssignedStatus = async (ticket) => {
//   await axiosInstance.put(`/tickets/${ticket.id_ticket}`, {
//     assigned_status: ticket.assigned_status,
//     inline_update: true
//   })

//   fetchTickets()
// }

// const handlePicChange = async (ticket, selectedUser) => {
//   console.log('Selected PIC User:', selectedUser)

//   if (selectedUser && selectedUser.id_user) {
//     ticket.id_pic_ticket = selectedUser.id_user

//     await updatePicTicket(ticket)

//     fetchTickets()
//   }
// }

// const updatePicTicket = async (ticket) => {
//   await axiosInstance.put(`/tickets/${ticket.id_ticket}`, {
//     id_pic_ticket: ticket.id_pic_ticket,
//     inline_update: true
//   })
//   fetchTickets()
// }

const handlePicChange = async (ticket, selectedUser) => {
  if (selectedUser && selectedUser.id_user) {
    ticket.id_pic_ticket = selectedUser.id_user
    ticket.assigned_status = 'Assigned' // Set status
  } else {
    ticket.id_pic_ticket = null
    ticket.assigned_status = 'Unassigned' // Clear status
  }

  await updatePicTicket(ticket)
  fetchTickets()
}

const updatePicTicket = async (ticket) => {
  await axiosInstance.put(`/tickets/${ticket.id_ticket}`, {
    id_pic_ticket: ticket.id_pic_ticket,
    assigned_status: ticket.assigned_status,  // 👈 Add this
    inline_update: true
  })
}


const handleEscalation = async (ticket, selectedUser) => {
  if (selectedUser && selectedUser.id_user) {
    ticket.escalation_to = selectedUser.id_user
  } else {
    ticket.escalation_to = null
  }

  await updateEscalation(ticket)

  fetchTickets()
}

const updateEscalation = async (ticket) => {
  await axiosInstance.put(`/tickets/${ticket.id_ticket}`, {
    escalation_to: ticket.escalation_to || null,
    inline_update: true
  })
  fetchTickets()
}

const handlePriorities = async (ticket, selectedPriority) => {
  if (selectedPriority && selectedPriority.id_ticket_priority) {
    ticket.id_ticket_priority = selectedPriority.id_ticket_priority
    await updatePriorities(ticket, selectedPriority)
    fetchTickets()
  }
}

const updatePriorities = async (ticket, selectedPriority) => {
  await axiosInstance.put(`/tickets/${ticket.id_ticket}`, {
    id_ticket_priority: selectedPriority.id_ticket_priority,
    inline_update: true
  })
}

const handleLayanans = async (ticket, selectedLayanans) => {
  if (selectedLayanans && selectedLayanans.id_layanan) {
    ticket.id_layanan = selectedLayanans.id_layanan
    await updateLayanans(ticket, selectedLayanans)
    fetchTickets()
  }
}

const updateLayanans = async (ticket, selectedLayanans) => {
  await axiosInstance.put(`/tickets/${ticket.id_ticket}`, {
    id_layanan: selectedLayanans.id_layanan,
    inline_update: true
  })
}

const handleSearch = () => {
  clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => {
    page.value = 1
    fetchTickets(false)
  }, 500)
}

const typeColorClass = (type) => ({
  'text-blue-600': type === 'Request',
  'text-red-600': type === 'Incident'
})

const statusColorClass = (status) => ({
  'text-red-600': status === 'Open',
  'text-yellow-600': status === 'On Progress',
  'text-blue-600': status === 'Closed',
  'text-black': status === 'Cancelled'
})

const assignedColorClass = (val) => ({
  'text-blue-600': val === 'Assigned',
  'text-red-600': val === 'Unassigned'
})

const priorityColorClass = (tingkat) => ({
  'text-red-700 font-bold': tingkat === 'P1',
  'text-red-600': tingkat === 'P2',
  'text-yellow-600': tingkat === 'P3',
  'text-green-600': tingkat === 'P4',
  'text-green-500': tingkat === 'P5',
})

const applyStatusFilter = () => {
  statusFilter.value = selectedStatuses.value.join(',')
  page.value = 1
  fetchTickets()
  showStatusDropdown.value = false

  // Update URL
  router.replace({ query: { ...route.query, filter_status: statusFilter.value || undefined } });
}

const clearStatusFilter = () => {
  selectedStatuses.value = []
  statusFilter.value = ''
  page.value = 1
  fetchTickets()
  showStatusDropdown.value = false

  // Remove filter_status from URL
  const newQuery = { ...route.query };
  delete newQuery.filter_status;
  router.replace({ query: newQuery });
}

const handleClickOutside = (event) => {
  if (statusDropdown.value && !statusDropdown.value.contains(event.target)) {
    showStatusDropdown.value = false;
  }
}

const activateJump = (dotIndex) => {
  jumpPage.value = dotIndex
  jumpTarget.value = ''
}

const goToJumpPage = () => {
  const p = parseInt(jumpTarget.value)
  if (!isNaN(p) && p >= 1 && p <= totalPages.value) {
    page.value = p
    jumpPage.value = null
  }
}

onMounted(async () => {
  await Promise.all([
    fetchUsers(false),
    fetchPriorities(false),
    fetchLayanans(false),
  ]);

  // Restore selectedStatuses from URL if exists
  if (route.query.filter_status) {
    selectedStatuses.value = route.query.filter_status.split(',');
    statusFilter.value = route.query.filter_status;
  }

  document.addEventListener('click', handleClickOutside);
  ticketPollingInterval = setInterval(pollTickets, 15000);

  fetchTickets();
});


onUnmounted(() => {
  clearInterval(ticketPollingInterval);
  document.removeEventListener('click', handleClickOutside);
});


watch(page, () => {
  fetchTickets()
})

watch([sortKey, sortDirection], () => {
  page.value = 1
  fetchTickets()
})

watch(userSearch, () => {
  clearTimeout(userFetchDebounce)
  userFetchDebounce = setTimeout(() => {
    fetchUsers()
  }, 300)
})

watch(prioritySearch, () => {
  clearTimeout(priorityFetchDebounce)
  priorityFetchDebounce = setTimeout(() => {
    fetchPriorities()
  }, 300)
})

watch(layananSearch, () => {
  clearTimeout(layananFetchDebounce)
  layananFetchDebounce = setTimeout(() => {
    fetchLayanans()
  }, 300)
})

watch(statusFilter, () => {
  page.value = 1
  fetchTickets()
})
</script>