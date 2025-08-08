<template>

  <div class="pt-16 ml-64 p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Left Container -->
      <div class="md:col-span-2 bg-white shadow overflow-hidden">
        <!-- Header -->
        <div class="bg-red-500 text-white text-lg font-semibold px-6 py-3 flex justify-between items-center">
          <div class="flex items-center gap-2">
            <span>ID Tiket: {{ ticket.id_ticket_type }} | Status: {{ ticket.ticket_status }}</span>

            <template v-if="ticket.ticket_status === 'Open' && ticket.assigned_status === 'Assigned'">
              <button
                @click="triggerDeployment"
                class="ml-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-1.5 rounded"
              >
                Deploy
              </button>
            </template>
          </div>
        </div>

        <div class="flex justify-start px-6 pt-4">
          <button
            @click="$router.push('/tickets')"
            class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-4 py-2 rounded font-medium"
          >Back</button>
        </div>
        
        <!-- Ticket Info Table -->
        <div class="p-5">
          <table class="w-full text-sm text-left text-gray-800 border border-gray-300 mt-0">
            <thead class="bg-gray-100">
              <tr>
                <th class="border px-4 py-2 w-1/4">Title</th>
                <th class="border px-4 py-2">Deskripsi Tiket</th>
                <th class="border px-4 py-2">Grup Layanan</th>
                <th class="border px-4 py-2">Layanan</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border px-4 py-2">{{ ticket.ticket_title }}</td>
                  <!-- Ticket Description & Attachments -->
                  <td class="border px-4 py-2 whitespace-pre-wrap">
                    <div class="whitespace-pre-wrap">
                      {{ ticket.ticket_description }}
                    </div>


                    <div v-if="ticket.ticket_attachments?.length" class="mt-2">
                      <button @click="showAttachmentModal = true" class="text-blue-600 underline text-sm">
                        Lihat Lampiran ({{ ticket.ticket_attachments.length }})
                      </button>
                    </div>
                  </td>
                <td class="border px-4 py-2">{{ ticket.group_layanan }}</td>
                <td class="border px-4 py-2">{{ ticket.nama_layanan }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Tindak Lanjut -->
        <div class="px-6 pb-6">
          <h3 class="text-sm font-semibold text-gray-800 mb-2">Tindak Lanjut</h3>
          <div class="flex flex-wrap gap-26">
            <button
              @click="openLogModal"
              class="bg-blue-500 hover:bg-blue-600 text-white font-medium text-sm px-4 py-2 rounded"
            >
              Log Tiket
            </button>
            <button
              @click="openTrackingModal"
              class="bg-blue-500 hover:bg-blue-600 text-white font-medium text-sm px-4 py-2 rounded"
            >
              Tracking Point
            </button>
            <button
              @click="openResolusiModal"
              :disabled="ticket.ticket_status !== 'On Progress'"
              :class="[
                'font-medium text-sm px-4 py-2 rounded',
                ticket.ticket_status !== 'On Progress'
                  ? 'bg-gray-500 text-white'
                  : 'bg-blue-500 hover:bg-blue-600 text-white'
              ]"
            >
              Resolusi
            </button>
            <button
              @click="openEskalasiModal"
              :disabled="!!ticket.pic_eskalasi || !ticket.pic_tiket || ['Closed', 'Cancelled'].includes(ticket.ticket_status)"
              :class="[
                'font-medium text-sm px-4 py-2 rounded',
                (!!ticket.pic_eskalasi || !ticket.pic_tiket || ['Closed', 'Cancelled'].includes(ticket.ticket_status))
                  ? 'bg-gray-500 text-white'
                  : 'bg-blue-500 hover:bg-blue-600 text-white'
              ]"
            >
              Eskalasi
            </button>

            <!-- <button
              @click="openResolusiModal"
              class="bg-blue-500 hover:bg-blue-600 text-white font-medium text-sm px-4 py-2 rounded"
            >
              Resolusi
            </button> -->
            <!-- <button
              @click="openPihakKetigaModal"
              :disabled="!!ticket.pic_eskalasi"
              :class="[
                'font-medium text-sm px-4 py-2 rounded',
                ticket.pic_eskalasi
                  ? 'bg-gray-500 text-white'
                  : 'bg-blue-500 hover:bg-blue-600 text-white'
              ]"
            >
              Pihak Ketiga
            </button> -->

            <!-- <button class="bg-gray-700 hover:bg-gray-800 text-white font-medium text-sm px-4 py-2 rounded">
              Resolusi
            </button> -->
            <!-- <button class="bg-gray-500 hover:bg-gray-600 text-white font-medium text-sm px-4 py-2 rounded">
              Pihak Ketiga
            </button> -->
          </div>
        </div>

        <!-- Analysis -->
        <div class="px-6 pb-6">
          <h3 class="text-sm font-semibold text-gray-800 mb-2">Detail Analisis</h3>
          <table class="w-full text-sm text-left text-gray-700 border border-gray-300">
            <thead class="bg-gray-100">
              <tr>
                <th class="border px-3 py-2">Analisis Awal</th>
                <th class="border px-3 py-2">Rootcause</th>
                <th class="border px-3 py-2">Deskripsi Rootcause</th>
                <th class="border px-3 py-2">Resolusi</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border px-3 py-2">{{ ticket.analisis_awal || '-' }}</td>
                <td class="border px-3 py-2">{{ ticket.nama_rootcause || '-' }}</td>
                <td class="border px-3 py-2">{{ ticket.rootcause_description || '-' }}</td>
                <td class="border px-3 py-2">{{ ticket.nama_solusi || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right Container -->
      <div class="bg-white shadow overflow-hidden max-h-fit">
        <div class="bg-red-500 text-white text-lg font-semibold px-6 py-3">
          Detail Tiket
        </div>
        <div class="p-6 space-y-3 text-sm text-gray-800">
          <div><strong>ID Email: </strong> {{ ticket.id_ticket }}</div>

          <div>
            <strong>PIC Tiket: </strong>
            <span :class="!ticket.pic_tiket ? 'text-red-500' : ''">
              {{ ticket.pic_tiket || 'Belum Ada' }}
            </span>
          </div>

          <div>
            <strong>Detail Prioritas: </strong>
            <span :class="!ticket.tingkat_priority ? 'text-red-500' : ''">
              {{ ticket.tingkat_priority
                ? `${ticket.tingkat_priority} (Dampak: ${ticket.tingkat_dampak || '-'}, Urgensi: ${ticket.tingkat_urgensi || '-'})`
                : 'Belum Ada' 
              }}
            </span>
          </div>

          <div><strong>Nama User: </strong> {{ ticket.nama_user }}</div>

          <div>
            <strong>Divisi: </strong>
            <span :class="!ticket.divisi_user ? 'text-red-500' : ''">
              {{ ticket.divisi_user || ' Belum Ada' }}
            </span>
          </div>

          <div v-if="ticket.ticket_type === 'Request'">
            <strong>Permintaan: </strong>
            <span :class="!ticket.nama_permintaan ? 'text-red-500' : ''">
              {{ ticket.nama_permintaan || ' Belum Ada' }}
            </span>
          </div>

          <div>
            <strong>SLA Normal: </strong>
            <span :class="!ticket.sla_duration_normal ? 'text-red-500' : ''">
              {{ ticket.sla_duration_normal ? ticket.sla_duration_normal + ' Hari' : 'Belum Ada' }}
            </span>
          </div>

          <div>
            <strong>Tanggal Close: </strong>
            <span :class="!ticket.tanggal_close ? 'text-red-500' : ''">
              {{ ticket.tanggal_close || 'Belum Ada' }}
            </span>
          </div>

          <div>
            <strong>SLA Eskalasi: </strong>
            <span :class="!ticket.sla_duration_escalation ? 'text-red-500' : ''">
              {{ ticket.sla_duration_escalation ? ticket.sla_duration_escalation + ' Hari' : 'Belum Ada' }}
            </span>
          </div>

          <div>
            <strong>PIC Eskalasi: </strong>
            <span :class="!ticket.pic_eskalasi ? 'text-red-500' : ''">
              {{ ticket.pic_eskalasi || 'Belum Ada' }}
            </span>
          </div>

          <div>
            <strong>Assigned Status: </strong>
            <span :class="!ticket.assigned_status || ticket.assigned_status === 'Unassigned' ? 'text-red-500' : ''">
              {{ ticket.assigned_status || 'Belum Ada' }}
            </span>
          </div>

          <div>
            <strong>Assigned Date: </strong>
            <span :class="!ticket.assigned_date ? 'text-red-500' : ''">
              {{ ticket.assigned_date || 'Belum Ada' }}
            </span>
          </div>

          <div><strong>Created On:</strong> {{ ticket.created_on }}</div>
          <div><strong>Created By:</strong> {{ ticket.created_by }}</div>
          <div><strong>Last Updated On:</strong> {{ ticket.last_updated_on }}</div>
          <div><strong>Last Updated By:</strong> {{ ticket.last_updated_by }}</div>

          <div class="mt-4 flex justify-center gap-3">
            <template v-if="ticket.ticket_status === 'Open'">
              <button @click="openUpdateModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                Update Ticket
              </button>

              <button @click="showCancelModal = true" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm">
                Cancel Ticket
              </button>
            </template>

            <!-- <template v-else-if="ticket.ticket_status === 'On Progress'">
              <button
                @click="cancelTicket"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm"
              >
                Cancel Ticket
              </button>
            </template> -->
            
            <!-- <template v-else-if="ticket.ticket_status === 'Closed'">
              <button
                @click="resetClosedStatus"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm"
              >
                Reset Closed Status
              </button>
            </template> -->

            <!-- <template v-else-if="ticket.ticket_status === 'Cancelled'">
              <button
                @click="resetCancelledStatus"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm"
              >
                Reset Cancelled Status
              </button>
            </template> -->
          </div>

        </div>
      </div>

      <!-- Update Ticket Modal -->
      <div v-if="showUpdateModal" class="fixed backdrop-brightness-50 inset-0 z-100 flex items-center justify-center">
        <div class="bg-white w-full max-w-xl rounded shadow-lg p-6 relative">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold">Update Ticket</h2>
            <button @click="showUpdateModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <form @submit.prevent="submitTicketUpdate" class="space-y-4 text-sm">

            <!-- Jenis Ticket -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Tiket </label>
              <div class="w-fit border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300">
                <Multiselect
                  v-model="updateForm.ticket_type"
                  :options="ticketTypeOptions"
                  :multiple="false"
                  :searchable="false"
                  :close-on-select="true"
                  :allow-empty="false"
                  placeholder="Pilih Jenis Tiket"
                  class="text-sm"
                />
              </div>
            </div>

            <!-- Layanan -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Layanan</label>
              <div class="w-fit border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300">
                <Multiselect
                  v-model="updateForm.id_layanan"
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

            <div v-if="updateForm.ticket_type === 'Request' && updateForm.id_layanan">
              <label class="block text-sm font-medium text-gray-700 mb-1">Permintaan</label>
              <div class="w-fit border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300">
                <Multiselect
                  v-model="updateForm.id_permintaan"
                  :options="permintaanOptions"
                  :track-by="'id_permintaan'"
                  label="nama_permintaan"
                  :multiple="false"
                  :searchable="true"
                  :close-on-select="true"
                  placeholder="Pilih Permintaan"
                  class="text-sm"
                />
              </div>
            </div>


            <!-- Prioritas -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
              <div class="w-fit border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300">
                <Multiselect
                  v-model="updateForm.id_ticket_priority"
                  :options="priorities"
                  :track-by="'id_ticket_priority'"
                  label="tingkat_priority" 
                  :custom-label="customPriorityLabel"
                  :multiple="false"
                  :searchable="false"
                  :close-on-select="true"
                  :allow-empty="false"
                  placeholder="Pilih Prioritas"
                  class="text-sm"
                />
              </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-2 pt-4 border-t">
              <button type="button" @click="showUpdateModal = false" class="px-4 py-2 border text-sm rounded hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Submit
              </button>
            </div>
          </form>
        </div>
      </div>

      <!--  Eskalasi Modal -->
      <div v-if="showEskalasiModal" class="fixed backdrop-brightness-50 inset-0 z-100 flex items-center justify-center">
        <div class="bg-white w-full max-w-xl rounded shadow-lg p-6 relative">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold">Eskalasi Ticket</h2>
            <button @click="showEskalasiModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <form @submit.prevent="submitEskalasiUpdate" class="space-y-4 text-sm">

            <!-- PIC Eskalasi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">PIC Eskalasi</label>
              <div class="w-fit border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300">
                <Multiselect
                  v-model="eskalasiForm.pic_eskalasi"
                  :options="eskalasiUsers"
                  :track-by="'id_user'"
                  label="nama_user"
                  :multiple="false"
                  :searchable="true"
                  :close-on-select="true"
                  :allow-empty="true"
                  placeholder="Pilih PIC Eskalasi"
                  class="text-sm"
                />
              </div>
            </div>

            <!-- Analisis Awal -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Analisis Awal</label>
              <textarea
                v-model="eskalasiForm.analisis_awal"
                rows="3"
                class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300 text-sm"
                placeholder="Tulis analisis awal..."
              ></textarea>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-2 pt-4 border-t">
              <button type="button" @click="showEskalasiModal = false" class="px-4 py-2 border text-sm rounded hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Submit
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Ticket Log Modal -->
      <div v-if="showLogModal" class="fixed backdrop-brightness-50 inset-0 z-100 flex items-center justify-center">
        <div class="bg-white w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded shadow-lg p-6 relative">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold">Log Tiket</h2>
            <button @click="showLogModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <div class="overflow-auto">
            <table class="min-w-full border text-sm text-left text-gray-700">
              <thead class="bg-gray-100">
                <tr>
                  <th class="border px-4 py-2">ID Log</th>
                  <th class="border px-4 py-2">Last Updated On</th>
                  <th class="border px-4 py-2">Last Updated By</th>
                  <th class="border px-4 py-2">Value</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="log in ticketLogs" :key="log.id_log">
                  <td class="border px-4 py-2">{{ log.id_log }}</td>
                  <td class="border px-4 py-2">{{ log.last_updated_on }}</td>
                  <td class="border px-4 py-2">{{ log.last_updated_by }}</td>
                  <td class="border px-4 py-2 whitespace-pre-wrap break-words">{{ log.value }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex justify-end">
            <button @click="showLogModal = false" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
              OK
            </button>
          </div>
        </div>
      </div>

      <!-- Resolusi Modal -->
      <div v-if="showResolusiModal" class="fixed backdrop-brightness-50 inset-0 z-100 flex items-center justify-center">
        <div class="bg-white w-full max-w-2xl rounded shadow-lg p-6 relative">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold">Resolusi Ticket</h2>
            <button @click="showResolusiModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <form @submit.prevent="submitResolusiForm" class="space-y-4 text-sm">
            <!-- Tanggal Status -->
            <div>
              <label class="block font-medium text-gray-700">Tanggal Mulai Pengerjaan</label>
              <p class="text-gray-800"> {{ ticket.progress_date }}</p>
                <!-- <p class="text-gray-800">{{ new Date().toLocaleString('sv-SE', { hour12: false }).replace(/\u202F/g, ' ') }}</p> -->
            </div>

            <!-- Ganti Status -->
            <!-- <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ticket Status</label>
              <div class="w-fit border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300">
                <Multiselect
                  v-model="resolusiForm.ticket_status"
                  :options="ticketStatusOptions"
                  :multiple="false"
                  :searchable="false"
                  :close-on-select="true"
                  :allow-empty="false"
                  placeholder="Pilih Status"
                  class="text-sm"
                />
              </div>
            </div> -->

            <!-- Rootcause -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Rootcause</label>
              <div class="flex gap-2 items-center w-fit">
                <div class="border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300 w-full">
                  <Multiselect
                    v-model="resolusiForm.id_rootcause"
                    :options="rootcauses"
                    :track-by="'id_rootcause'"
                    label="nama_rootcause" 
                    :multiple="false"
                    :searchable="true"
                    :close-on-select="true"
                    :allow-empty="false"
                    placeholder="Pilih Rootcause"
                    class="text-sm"
                  />
                </div>
                <button
                  @click="showRootcauseModal = true"
                  type="button"
                  class="px-3 py-2 bg-blue-600 text-white rounded text-xs hover:bg-blue-700 whitespace-nowrap"
                >
                  +
                </button>
              </div>
            </div>

            <!-- Resolusi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Resolusi</label>
              <div class="flex gap-2 items-center w-fit">
                <div class="border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300 w-full">
                  <Multiselect
                    v-model="resolusiForm.id_solusi"
                    :options="solusi"
                    :track-by="'id_solusi'"
                    label="nama_solusi" 
                    :multiple="false"
                    :searchable="true"
                    :close-on-select="true"
                    :allow-empty="false"
                    placeholder="Pilih Solusi"
                    class="text-sm"
                  />
                </div>
                <button
                  @click="showSolusiModal = true"
                  type="button"
                  class="px-3 py-2 bg-blue-600 text-white rounded text-xs hover:bg-blue-700 whitespace-nowrap"
                >
                  +
                </button>
              </div>
            </div>

            <!-- Solusi Comment -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Resolusi</label>
              <textarea
                v-model="resolusiForm.solusi_comment"
                rows="3"
                class="w-full border border-gray-300 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300"
                placeholder="Tulis catatan solusi... (Opsional)"
              ></textarea>
            </div>

            <!-- Teknisi Tambahan -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Teknisi Tambahan</label>
              <div class="w-fit border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300">
                <Multiselect
                  v-model="resolusiForm.teknisi_tambahan"
                  :options="additionalUsers"
                  :track-by="'id_user'"
                  label="nama_user"
                  :multiple="true"
                  :searchable="false"
                  :close-on-select="true"
                  :allow-empty="true"
                  placeholder="Pilih Teknisi Tambahan"
                  class="text-sm"
                />
              </div>
            </div>

            <!-- Link Pendukung -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Link Pendukung</label>
              <input v-model="resolusiForm.link_pendukung" type="text" class="w-full border border-gray-300 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300"/>
            </div>

            <!-- Screenshot Pendukung -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Screenshot Pendukung</label>
              <div class="flex items-center gap-2">
                <div class="w-full">
                  <div 
                    class="border border-gray-300 rounded bg-white px-3 py-2 flex justify-between items-center cursor-pointer"
                    @click="$refs.fileInput.click()"
                  >
                    <span>{{ selectedFileName || 'Pilih File' }}</span>
                    <!-- <button 
                      type="button"
                      class="bg-gray-300 px-3 py-1 rounded text-sm"
                      @click="$refs.fileInput.click()"
                    >Browse</button> -->
                  </div>
                  <input 
                    type="file" 
                    ref="fileInput"
                    @change="handleFileChange"
                    class="hidden"
                  />
                </div>
              </div>
            </div>


            <!-- Submit -->
            <div class="flex justify-end gap-2 pt-4 border-t">
              <button type="button" @click="showResolusiModal = false" class="px-4 py-2 border rounded hover:bg-gray-50">
                Batal
              </button>
              <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Submit
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tambah Rootcause Modal -->
      <div v-if="showRootcauseModal" class="fixed backdrop-brightness-50 inset-0 z-110 flex items-center justify-center">
        <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Tambah Rootcause</h2>
            <button @click="showRootcauseModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <form @submit.prevent="submitRootcauseForm" class="space-y-4 text-sm">
            <!-- Layanan -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Layanan</label>
                <div class="w-fit border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300">
                  <Multiselect
                    v-model="ticketLayananObj"
                    :options="[ticketLayananObj]"
                    :track-by="'id_layanan'"
                    label="nama_layanan"
                    :custom-label="customLayananLabel"
                    :disabled="true"
                    :multiple="false"
                    class="text-sm"
                  />
                </div>
            </div>

            <!-- Rootcause -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Rootcause</label>
              <input
                v-model="newRootcauseForm.nama_rootcause"
                type="text"
                class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300"
                required
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Rootcause</label>
              <textarea
                v-model="newRootcauseForm.rootcause_description"
                class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300"
                rows="3"
              />
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2 pt-4 border-t">
              <button type="button" @click="showRootcauseModal = false" class="px-4 py-2 border text-sm rounded">Batal</button>
              <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">Tambah</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tambah Solusi Modal -->
      <div v-if="showSolusiModal" class="fixed backdrop-brightness-50 inset-0 z-110 flex items-center justify-center">
        <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Tambah Solusi</h2>
            <button @click="showSolusiModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <form @submit.prevent="submitSolusiForm" class="space-y-4 text-sm">
            <!-- Solusi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Solusi</label>
              <input
                v-model="newSolusiForm.nama_solusi"
                type="text"
                class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300"
                required
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Solusi</label>
              <textarea
                v-model="newSolusiForm.solusi_description"
                class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-gray-300"
                rows="3"
              />
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2 pt-4 border-t">
              <button type="button" @click="showSolusiModal = false" class="px-4 py-2 border text-sm rounded">Batal</button>
              <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">Tambah</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tracking Point Modal -->
      <div v-if="showTrackingModal" class="fixed backdrop-brightness-50 inset-0 z-100 flex items-center justify-center">
        <div class="bg-white w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded shadow-lg p-6 relative">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold">Tracking Point</h2>
            <button @click="showTrackingModal = false" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
          </div>

          <div class="px-4 py-2 space-y-6">
            <div>
              <div
                v-for="(track, index) in sortedTrackingPoints"
                :key="index"
                class="relative pl-6 mb-6 z-10"
              >
              <!-- Show short or long line unless it's the latest editable item -->
              <div
                v-if="shouldRenderLine(index)"
                :style="{ height: `${lineHeights[index] || 80}px` }"
                class="absolute -left-1 top-4 w-1 bg-green-500"
              ></div>


                <div
                  class="absolute rounded-full border-2 border-white shadow-md w-4 h-4 -left-2.5 bg-green-500"
                ></div>

                <p class="font-semibold text-gray-800 flex items-center justify-between">
                  {{ track.tracking_status }}
                  <template v-if="!['Closed', 'Cancelled'].includes(track.tracking_status)">
                    <button
                      class="text-sm text-blue-500 hover:text-blue-700 ml-2 cursor-pointer"
                      @click.stop="toggleTrackingComment(index)">
                      {{ expandedTrackingIndex.has(index) ? 'Hide' : 'Show' }}
                    </button>
                  </template>
                </p>
                <p class="text-sm text-gray-600">System: {{ track.ticket_comment }}</p>
                <p class="text-sm italic text-gray-700 mt-1">{{ track.tracking_created_on }}</p>

                <div
                  v-if="expandedTrackingIndex.has(index) && !['Closed', 'Cancelled'].includes(track.tracking_status)"
                  :ref="el => setExpandedHeight(index, el)"
                  class="mt-3 p-3 rounded text-sm space-y-2"
                >

                  <!-- 💬 Chat Log Area -->
                  <div class="flex flex-col gap-3 mt-4 max-h-64 overflow-y-auto pr-2">
                    <div
                      v-for="log in track.comment_logs
                          .slice()
                          .sort((a, b) => new Date(a.comment_created_on) - new Date(b.comment_created_on))"
                      :key="log.id_tracking_comment"
                      class="w-fit max-w-[75%] px-4 py-2 rounded-md shadow-sm text-sm whitespace-pre-wrap"
                      :class="log.comment_type === 'pic' ? 'bg-blue-100 self-end text-right' : 'bg-gray-100 self-start text-left'"
                    >
                      <p>{{ log.comment_text }}</p>
                      <p class="text-xs text-gray-500 mt-1">
                        {{ log.created_by }} • {{ log.comment_created_on }}
                      </p>
                    </div>
                  </div>

                  <!-- 📝 New Comment Input -->
                  <div class="mt-4">
                    <div v-if="index === latestEditableIndex && !['Closed', 'Cancelled'].includes(ticket.ticket_status)" class="mt-4">
                      <textarea
                        v-model="commentDrafts[index]"
                        rows="2"
                        placeholder="Tulis komentar..."
                        class="w-full border border-gray-200 px-3 py-2 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-300 resize-none"
                      ></textarea>
                      <div class="flex justify-end mt-2">
                        <button
                          @click="submitPICComment(index)"
                          :disabled="!commentDrafts[index]"
                          class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded text-sm"
                        >
                          Kirim
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Show Only Latest User Reply -->
          <!-- <div v-if="latestUserReply" class="mt-6 border-t pt-4 bg-yellow-50 p-4 rounded shadow-sm">
            <p class="text-sm text-gray-800">
              📬 <strong>Balasan Pengguna Terbaru:</strong>
            </p>
            <p class="text-sm text-gray-700 italic whitespace-pre-line mt-1">
              {{ latestUserReply.user_comment }}
            </p>
            <p class="text-xs text-gray-500 mt-2 text-right">
              {{ latestUserReply.comment_created_on }}
            </p>
          </div> -->

          <div class="mt-6 flex justify-end">
            <button @click="showTrackingModal = false" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
              OK
            </button>
          </div>
        </div>
      </div>

      <!-- Image Modal -->
      <div v-if="showImageModal" class="fixed backdrop-brightness-50 inset-0 z-100 flex items-center justify-center">
        <div class="relative">
          <img :src="previewImage" class="max-w-[66vw] max-h-[80vh] object-contain rounded shadow-lg" />
          <button @click="showImageModal = false"
            class="absolute top-2 right-2 text-white text-xl bg-black bg-opacity-50 rounded-full p-1 hover:bg-opacity-80">
            &times;
          </button>
        </div>
      </div>

      <!-- Cancel Ticket Modal -->
      <div v-if="showCancelModal" class="fixed backdrop-brightness-50 inset-0 z-120 flex items-center justify-center">
        <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold">Cancel Ticket</h2>
            <button @click="showCancelModal = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <form @submit.prevent="submitCancelTicket" class="space-y-4 text-sm">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Pembatalan</label>
              <textarea v-model="cancelForm.cancel_comment" rows="4"
                class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300"
                placeholder="Masukkan alasan pembatalan..." required>
              </textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t">
              <button type="button" @click="showCancelModal = false" class="px-4 py-2 border text-sm rounded hover:bg-gray-50">
                Batal
              </button>
              <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                Submit Pembatalan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Attachment Modal with Image Viewer -->
      <div v-if="showAttachmentModal" class="fixed backdrop-brightness-50 inset-0 z-120 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 relative">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold">Lampiran Tiket</h2>
            <button @click="showAttachmentModal = false" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
          </div>

          <div v-if="imageAttachments.length > 0" class="flex flex-col items-center space-y-4">
            <div class="relative w-full flex justify-center">
              <img
                :src="imageAttachments[currentImageIndex]?.url"
                class="max-h-[60vh] object-contain rounded shadow-md"
              />

              <!-- Left Button -->
              <button
                v-if="imageAttachments.length > 1"
                @click="currentImageIndex = (currentImageIndex - 1 + imageAttachments.length) % imageAttachments.length"
                class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-30 hover:bg-opacity-60 text-white px-2 py-1 rounded-l"
              >
                ‹
              </button>

              <!-- Right Button -->
              <button
                v-if="imageAttachments.length > 1"
                @click="currentImageIndex = (currentImageIndex + 1) % imageAttachments.length"
                class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-30 hover:bg-opacity-60 text-white px-2 py-1 rounded-r"
              >
                ›
              </button>
            </div>

            <!-- Image Thumbnails -->
            <div class="flex gap-2 flex-wrap justify-center mt-2">
              <img
                v-for="(file, index) in imageAttachments"
                :key="index"
                :src="file.url"
                class="w-16 h-16 object-cover cursor-pointer border rounded"
                :class="index === currentImageIndex ? 'border-blue-500' : 'border-gray-300'"
                @click="currentImageIndex = index"
              />
            </div>
          </div>

          <!-- Non-image Files -->
          <div v-if="nonImageAttachments.length > 0" class="mt-6">
            <h3 class="text-sm font-semibold mb-2">Lainnya:</h3>
            <ul class="space-y-2 text-sm">
              <li v-for="(file, index) in nonImageAttachments" :key="index">
                <a :href="file.url" target="_blank" class="text-blue-600 underline">
                  {{ file.name }}
                </a>
              </li>
            </ul>
          </div>

          <div class="mt-4 flex justify-end">
            <button @click="showAttachmentModal = false" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
              Tutup
            </button>
          </div>
        </div>
      </div>



    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, inject, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import router from '@/router';
import axiosInstance from '@/lib/axios'
import Multiselect from 'vue-multiselect'

const globalLoading = inject('globalLoading')
const ticketTypeOptions = ['Request', 'Incident']
const showUpdateModal = ref(false)
const showEskalasiModal = ref(false)
const showLogModal = ref(false)
const showResolusiModal = ref(false)
const layanans = ref([])
const priorities = ref([])
const ticket = ref({})
// const showLayananModal = ref(false)
const ticketStatusOptions = ['Open', 'On Progress', 'Closed', 'Cancelled']
const groupOptions = ['Jaringan & Infrastruktur', 'Keamanan Siber', 'Aplikasi']
const showAttachmentModal = ref(false);
const showRootcauseModal = ref(false)
const showSolusiModal = ref(false)
const showTrackingModal = ref(false)
const trackingPoints = ref([])
const previewImage = ref(null);
const showImageModal = ref(false);
const commentDrafts = ref({});
const expandedTrackingIndex = ref(new Set());
const showCancelModal = ref(false);
const selectedFileName = ref('');
const lineHeights = ref({});
const expandedRefs = ref({});
const currentImageIndex = ref(0);
const permintaanOptions = ref([]);


const props = defineProps({
  ticket: Object
});

const updateForm = ref({
  ticket_type: null,
  id_layanan: null,
  id_permintaan: null,
  id_ticket_priority: null
});

const eskalasiForm = ref({
  pic_eskalasi: null,
  analisis_awal: ''
});

const resolusiForm = ref({
  // ticket_status: 'Closed',
  id_rootcause: null,
  id_solusi: null,
  solusi_comment: null,
  teknisi_tambahan: [],
  link_pendukung: '',
  screenshot_pendukung: null,
});

const newRootcauseForm = ref({
  id_layanan: null,
  nama_rootcause: '',
  rootcause_description: ''
})

const newSolusiForm = ref({
  nama_solusi: '',
  solusi_description: ''
})

const cancelForm = ref({
  cancel_comment: ''
})

const effectiveTicketId = computed(() => {
  return ticket.value.id_ticket || route.params.ticketIdentifier;
});

const latestUserReply = computed(() => {
  return [...trackingPoints.value]
    .reverse()
    .find(point => !!point.user_comment)
});

const sortedTrackingPoints = computed(() => {
  return [...trackingPoints.value].sort((a, b) => {
    return a.id_ticket_tracking - b.id_ticket_tracking;
  });
});

const latestEditableIndex = computed(() => {
  return sortedTrackingPoints.value.findLastIndex(
    point => !['Closed', 'Cancelled'].includes(point.tracking_status)
  );
});


const route = useRoute()
const activeLayananOptions = computed(() => layanans.value.filter(l => l.status_layanan === 'Aktif'))
const eskalasiUsers = computed(() => users.value.filter(u => u.roles?.some(r => r.name === 'Petugas IT')))
const additionalUsers = computed(() => users.value.filter(u => u.roles?.some(r => r.name === 'Petugas IT')))
const users = ref([])
const ticketLogs = ref([])
const rootcauses = ref([]);
const solusi = ref([]);

const imageAttachments = computed(() =>
  ticket.value.ticket_attachments?.filter(file =>
    /\.(jpe?g|png|gif|bmp|webp)$/i.test(file.name)
  ) || []
);

const nonImageAttachments = computed(() =>
  ticket.value.ticket_attachments?.filter(file =>
    !/\.(jpe?g|png|gif|bmp|webp)$/i.test(file.name)
  ) || []
);


// 🟩 Custom Label: Layanan
const customLayananLabel = (option) => {
  if (!option) return '-'
  return `${option.group_layanan || ''} - ${option.nama_layanan || ''}`
}

// 🟩 Custom Label: Prioritas
const customPriorityLabel = (option) => {
  if (!option) return '-'
  return `${option.tingkat_priority || ''} - ${option.tingkat_dampak || ''} - ${option.tingkat_urgensi || ''}`
}

const handleImageClick = (event) => {
  const target = event.target;
  if (target.tagName === 'IMG') {
    previewImage.value = target.src;
    showImageModal.value = true;
  }
};

function handleFileChange(event) {
  const file = event.target.files[0];
  selectedFileName.value = file ? file.name : '';
  
  if (file) {
    const reader = new FileReader();
    reader.onload = () => {
      resolusiForm.value.screenshot_pendukung = reader.result; // base64 encoded string
    };
    reader.readAsDataURL(file);
  } else {
    resolusiForm.value.screenshot_pendukung = null;
  }
}



// 🟦 Open Modal with Proper Mapping
const openUpdateModal = async () => {
  if (!layanans.value.length) await fetchLayanans()
  if (!priorities.value.length) await fetchPriorities()

  updateForm.value = {
    ticket_type: ticket.value.ticket_type || null,
    id_layanan: layanans.value.find(l => l.id_layanan === ticket.value.id_layanan) || null,
    id_permintaan: permintaanOptions.value.find(p => p.id_permintaan === ticket.value.id_permintaan) || null,
    id_ticket_priority: priorities.value.find(p => p.id_ticket_priority === ticket.value.id_ticket_priority) || null,
  }

  showUpdateModal.value = true
}

const openEskalasiModal = async () => {
  if (!users.value.length) await fetchUsers()

  // 🟨 Add debugging logs here
  console.log('Escalation to ID:', ticket.value.escalation_to)
  console.log('Available Eskalasi Users:', eskalasiUsers.value)
  console.log('Matched:',
    users.value.find(u => u.id_user === ticket.value.escalation_to)
  )

  eskalasiForm.value = {
    pic_eskalasi: users.value.find(u => u.id_user === ticket.value.escalation_to) || null,
    analisis_awal: ticket.value.analisis_awal || '',
  }

  showEskalasiModal.value = true
}

const openLogModal = async () => {
  if (!ticket.value.id_ticket) return
  await fetchLogs()
  showLogModal.value = true
}

const openResolusiModal = () => {
  showResolusiModal.value = true;
};

const openTrackingModal = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get(`/tickets/${ticket.value.id_ticket}/tracking`);
    trackingPoints.value = Array.isArray(data) ? data : [];

    commentDrafts.value = {};
    trackingPoints.value.forEach((_, i) => {
      commentDrafts.value[i] = '';
    });

    showTrackingModal.value = true;
  } catch (err) {
    console.error('❌ Failed to fetch tracking points:', err);
  } finally {
    globalLoading.value = false;
  }
};

// 🟦 API Fetch
const fetchLayanans = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/layanans', {
      params: { page: 1, per_page: 100 }
    });
    layanans.value = data.data;
  } catch (err) {
    console.error('❌ Error fetching layanans:', err);
    layanans.value = [];
  } finally {
    globalLoading.value = false;
  }
};

const fetchPriorities = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/priorities');
    priorities.value = data;
  } catch (err) {
    console.error('❌ Error fetching priorities:', err);
    priorities.value = [];
  } finally {
    globalLoading.value = false;
  }
};

const fetchUsers = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/users', {
      params: { per_page: 100 }
    });
    users.value = data.data;
  } catch (err) {
    console.error('❌ Error fetching users:', err);
    users.value = [];
  } finally {
    globalLoading.value = false;
  }
};


const fetchLogs = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get(`/tickets/${ticket.value.id_ticket}/logs`);
    ticketLogs.value = data;
  } catch (err) {
    console.error('❌ Error fetching logs:', err);
    ticketLogs.value = [];
  } finally {
    globalLoading.value = false;
  }
};


const fetchRootcauses = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/rootcauses', {
      params: { page: 1, per_page: 1000 }
    });
    rootcauses.value = Array.isArray(data.data) ? data.data : [];
  } catch (e) {
    console.error('❌ Error fetching rootcauses:', e);
    rootcauses.value = [];
  } finally {
    globalLoading.value = false;
  }
};

const fetchSolusi = async () => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/solusi', {
      params: { page: 1, per_page: 1000 }
    });
    solusi.value = Array.isArray(data.data) ? data.data : [];
  } catch (e) {
    console.error('❌ Error fetching solusi:', e);
    solusi.value = [];
  } finally {
    globalLoading.value = false;
  }
};

const fetchPermintaan = async (id_layanan) => {
  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get('/permintaan', {
      params: { id_layanan }
    });
    permintaanOptions.value = data.data || [];
  } catch (err) {
    console.error('❌ Failed to fetch permintaan:', err);
    permintaanOptions.value = [];
  } finally {
    globalLoading.value = false;
  }
};



const submitEskalasiUpdate = async () => {
  const payload = {
    escalation_to: eskalasiForm.value.pic_eskalasi?.id_user,
    rootcause_awal: eskalasiForm.value.analisis_awal,
    inline_update: true,
  };

  globalLoading.value = true;
  try {
    await axiosInstance.put(`/tickets/${effectiveTicketId.value}`, payload);
    const { data } = await axiosInstance.get(`/tickets/${effectiveTicketId.value}`);
    ticket.value = data;
    showEskalasiModal.value = false;
  } catch (error) {
    console.error('❌ Error updating escalation:', error);
  } finally {
    globalLoading.value = false;
  }
};


// 🟩 Submit Handler
const submitTicketUpdate = async () => {
  globalLoading.value = true;
  try {
    await axiosInstance.put(`/tickets/${effectiveTicketId.value}`, {
      ticket_type: updateForm.value.ticket_type,
      id_layanan: updateForm.value.id_layanan?.id_layanan,
      id_ticket_priority: updateForm.value.id_ticket_priority?.id_ticket_priority,
      id_permintaan: updateForm.value.id_permintaan?.id_permintaan || null,
      inline_update: true
    });

    const { data } = await axiosInstance.get(`/tickets/${effectiveTicketId.value}`);
    ticket.value = data;
    showUpdateModal.value = false;

    const missing = [];
    if (!data.ticket_type) missing.push('Jenis Tiket');
    if (!data.id_layanan) missing.push('Layanan');
    if (!data.id_ticket_priority) missing.push('Prioritas');
    if (!data.divisi_user) missing.push('Divisi');

    if (missing.length > 0) {
      alert(
        `✅ Update berhasil, tetapi terdapat data masih kosong:\n\n- ${missing.join('\n- ')}\n\nSilakan lengkapi untuk memulai pengerjaan ticket.`
      );
    }

    if (data.id_ticket_type !== route.params.ticketIdentifier) {
      router.replace(`/tickets/${data.id_ticket_type}`);
    }
  } catch (error) {
    console.error('❌ Error updating ticket:', error);
  } finally {
    globalLoading.value = false;
  }
};


const submitResolusiForm = async () => {
  globalLoading.value = true;
  try {
    const payload = {
      ticket_status: 'Closed',
      id_rootcause: resolusiForm.value.id_rootcause?.id_rootcause || null,
      id_solusi: resolusiForm.value.id_solusi?.id_solusi || null,
      solusi_comment: resolusiForm.value.solusi_comment || null,
      link_pendukung: resolusiForm.value.link_pendukung || '',
      screenshot_pendukung: resolusiForm.value.screenshot_pendukung || null,
      teknisi_tambahan: resolusiForm.value.teknisi_tambahan.map(user => user.nama_user),
      inline_update: true
    };

    await axiosInstance.put(`/tickets/${effectiveTicketId.value}`, payload);
    const { data } = await axiosInstance.get(`/tickets/${effectiveTicketId.value}`);
    ticket.value = data;
    showResolusiModal.value = false;
  } catch (error) {
    console.error('❌ Error submitting resolusi:', error);
  } finally {
    globalLoading.value = false;
  }
};


const submitRootcauseForm = async () => {
  globalLoading.value = true;
  try {
    newRootcauseForm.value.id_layanan = ticketLayananObj.value;

    const payload = {
      id_layanan: newRootcauseForm.value.id_layanan?.id_layanan,
      nama_rootcause: newRootcauseForm.value.nama_rootcause,
      rootcause_description: newRootcauseForm.value.rootcause_description
    };

    await axiosInstance.post('/rootcauses', payload);
    await fetchRootcauses();
    showRootcauseModal.value = false;
    newRootcauseForm.value = { id_layanan: null, nama_rootcause: '', rootcause_description: '' };
  } catch (error) {
    console.error('❌ Error adding rootcause:', error);
  } finally {
    globalLoading.value = false;
  }
};


const submitSolusiForm = async () => {
  globalLoading.value = true;
  try {
    const payload = {
      nama_solusi: newSolusiForm.value.nama_solusi,
      solusi_description: newSolusiForm.value.solusi_description
    };

    await axiosInstance.post('/solusi', payload);
    await fetchSolusi();
    showSolusiModal.value = false;
    newSolusiForm.value = { nama_solusi: '', solusi_description: '' };
  } catch (error) {
    console.error('❌ Error adding solusi:', error);
  } finally {
    globalLoading.value = false;
  }
};


const submitPICComment = async (index) => {
  const comment = commentDrafts.value[index];
  if (!comment) return;

  globalLoading.value = true;
  try {
    await axiosInstance.post(
      `/tickets/${ticket.value.id_ticket}/tracking/${trackingPoints.value[index].id_ticket_tracking}/comment`,
      { pic_comment: comment }
    );

    trackingPoints.value[index].comment_logs.push({
      id_tracking_comment: Date.now(),
      comment_text: comment,
      created_by: ticket.value.pic_tiket || 'Anda',
      comment_created_on: new Date().toLocaleString('sv-SE', { hour12: false }).replace(/\u202F/g, ' '),
      comment_type: 'pic',
    });

    commentDrafts.value[index] = '';

    await nextTick();
    const el = expandedRefs.value[index];
    if (el) setExpandedHeight(index, el);

  } catch (error) {
    console.error("❌ Gagal mengirim komentar PIC:", error);
    alert("Gagal mengirim komentar PIC.");
  } finally {
    globalLoading.value = false;
  }
};


const submitCancelTicket = async () => {
  globalLoading.value = true;
  try {
    await axiosInstance.put(`/tickets/${effectiveTicketId.value}`, {
      ticket_status: 'Cancelled',
      cancel_comment: cancelForm.value.cancel_comment,
      inline_update: true
    });

    const { data } = await axiosInstance.get(`/tickets/${effectiveTicketId.value}`);
    ticket.value = data;
    showCancelModal.value = false;
    cancelForm.value.cancel_comment = '';
  } catch (error) {
    console.error('❌ Error cancelling ticket:', error);
    alert('Gagal melakukan pembatalan.');
  } finally {
    globalLoading.value = false;
  }
};


// const cancelTicket = async () => {
//   try {
//     await axiosInstance.put(`/tickets/${effectiveTicketId.value}`, {
//       ticket_status: 'Cancelled',
//       tracking_status: 'Cancelled',
//       inline_update: true
//     })

//     // Refresh ticket info
//     const { data } = await axiosInstance.get(`/tickets/${effectiveTicketId.value}`);
//     ticket.value = data
//   } catch (error) {
//     console.error('Error cancelling ticket:', error)
//   }
// }

// const resetClosedStatus = async () => {
//   try {
//     await axiosInstance.put(`/tickets/${effectiveTicketId.value}`, {
//       ticket_status: 'Open',
//       inline_update: true
//     })

//     // Refresh ticket info
//     const { data } = await axiosInstance.get(`/tickets/${effectiveTicketId.value}`);
//     ticket.value = data
//   } catch (error) {
//     console.error('Error resetting closed status:', error)
//   }
// }

// const resetCancelledStatus = async () => {
//   try {
//     await axiosInstance.put(`/tickets/${effectiveTicketId.value}`, {
//       ticket_status: 'Open',
//       inline_update: true
//     })

//     // Refresh ticket info
//     const { data } = await axiosInstance.get(`/tickets/${effectiveTicketId.value}`);
//     ticket.value = data
//   } catch (error) {
//     console.error('Error resetting cancelled status:', error)
//   }
// }

const ticketLayananObj = computed(() =>
  layanans.value.find(l => l.id_layanan === ticket.value.id_layanan) || null
)

const markNotificationAsRead = async (ticketId) => {
  try {
    await axiosInstance.post(`/notifications/mark-read-by-ticket`, {
      id_ticket: ticketId
    });
    console.log('Notification marked as read for ticket:', ticketId);
  } catch (error) {
    console.error('Failed to mark notification as read:', error);
  }
}


const triggerDeployment = async () => {
  globalLoading.value = true;
  try {
    await axiosInstance.put(`/tickets/${effectiveTicketId.value}`, {
      ticket_status: 'On Progress',
      tracking_status: 'On Progress',
      inline_update: true
    });

    const { data } = await axiosInstance.get(`/tickets/${effectiveTicketId.value}`);
    ticket.value = data;
  } catch (err) {
    console.error('❌ Failed to deploy:', err);
  } finally {
    globalLoading.value = false;
  }
};

const toggleTrackingComment = async (index) => {
  if (expandedTrackingIndex.value.has(index)) {
    // Collapse it
    expandedTrackingIndex.value.delete(index);
    lineHeights.value[index] = 80;
  } else {
    // Expand it
    expandedTrackingIndex.value.add(index);
    await nextTick();
    const el = expandedRefs.value[index];
    if (el) setExpandedHeight(index, el);
  }
};

const setExpandedHeight = (index, el) => {
  if (!el) return;
  expandedRefs.value[index] = el;
  lineHeights.value[index] = el.offsetHeight + 24 + 72;
};

// const isLastTrackingPoint = (index) => {
//   return index === sortedTrackingPoints.value.length - 1;
// };

const shouldRenderLine = (index) => {
  const current = sortedTrackingPoints.value[index];
  const next = sortedTrackingPoints.value[index + 1];

  const isEditable = !['Closed', 'Cancelled'].includes(current.tracking_status);
  const nextIsClosed = ['Closed', 'Cancelled'].includes(next?.tracking_status);

  // Render line if current is editable and:
  // - it is NOT the last editable index
  // - OR next is a final closed/cancelled status (to keep the line visually connecting)
  return isEditable && (
    index !== latestEditableIndex.value || nextIsClosed
  );
};

// 🟦 Init
onMounted(async () => {
  globalLoading.value = true;
  const ticketIdentifier = route.params.ticketIdentifier;

  try {
    // Run all fetches concurrently
    await Promise.all([
      fetchLayanans(),
      fetchPriorities(),
      fetchUsers(),
      fetchRootcauses(),
      fetchSolusi(),
      fetchPermintaan()
    ]);

    // Fetch ticket details
    const { data } = await axiosInstance.get(`/tickets/${ticketIdentifier}`);
    ticket.value = data;

    if (data.id_ticket_type && ticketIdentifier !== data.id_ticket_type) {
      router.replace(`/tickets/${data.id_ticket_type}`);
    }

    // Run logs and notifications concurrently
    await Promise.all([
      fetchLogs(),
      data.id_ticket ? markNotificationAsRead(data.id_ticket) : Promise.resolve()
    ]);

  } catch (err) {
    console.error('❌ Failed to load ticket:', err);
  } finally {
    globalLoading.value = false;
  }
});


watch(() => route.params.ticketIdentifier, async (newId) => {
  if (!newId) return;

  globalLoading.value = true;
  try {
    const { data } = await axiosInstance.get(`/tickets/${newId}`);
    ticket.value = data;

    if (data.id_ticket_type && newId !== data.id_ticket_type) {
      router.replace(`/tickets/${data.id_ticket_type}`);
    }

    await Promise.all([
      fetchLogs(),
      data.id_ticket ? markNotificationAsRead(data.id_ticket) : Promise.resolve()
    ]);

  } catch (err) {
    console.error('❌ Failed to reload ticket on param change:', err);
  } finally {
    globalLoading.value = false;
  }
});


watch(trackingPoints, async () => {
  await nextTick();

  const validIndexes = new Set(sortedTrackingPoints.value.map((_, i) => i));
  expandedTrackingIndex.value.forEach(index => {
    if (!validIndexes.has(index)) {
      expandedTrackingIndex.value.delete(index);
      delete expandedRefs.value[index];
      lineHeights.value[index] = 80;
    } else {
      const el = expandedRefs.value[index];
      if (el) setExpandedHeight(index, el);
    }
  });
});

watch(
  () => [updateForm.value.ticket_type, updateForm.value.id_layanan],
  async ([type, layanan], [prevType, prevLayanan]) => {
    if (type === 'Request' && layanan?.id_layanan) {
      if (!prevLayanan || layanan.id_layanan !== prevLayanan.id_layanan) {
        updateForm.value.id_permintaan = null;
      }
      await fetchPermintaan(layanan.id_layanan);
    } else {
      permintaanOptions.value = [];
      updateForm.value.id_permintaan = null;
    }
  },
  { immediate: true }
);


</script>

<style scoped>
table {
  border-collapse: collapse;
}
th,
td {
  border: 1px solid #ccc;
}
</style>