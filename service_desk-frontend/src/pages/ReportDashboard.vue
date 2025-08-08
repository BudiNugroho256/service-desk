<template>
  <div class="pt-16 ml-64 p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
      <!-- Left Container -->
      <div class="md:col-span-1 bg-white shadow overflow-hidden h-fit">
        <!-- Header -->
        <div class="bg-red-500 text-white text-lg font-semibold px-6 py-3 flex justify-between items-center">
          <div class="flex items-center gap-2">
            <span>Overview</span>
          </div>
        </div>
        
        <!-- Body Content -->
        <div class="px-6 py-4 text-sm text-gray-700 leading-relaxed text-justify">
            <p class="mb-2">Halaman Report dan Dashboard dari Service Desk digunakan untuk membantu melakukan monitoring dan pengambilan keputusan terhadap Pengelolaan Layanan TI. Hal ini diharapkan mampu meningkatkan kualitas Pengelolaan Layanan TI di Hutama Karya.</p>
        </div>
      </div>

      <!-- Right Container -->
      <div class="md:col-span-1 bg-white shadow h-full flex flex-col">
        <!-- Header (stick at top) -->
        <div class="bg-red-500 text-white text-lg font-semibold px-6 py-3">
            <span>Report</span>
        </div>

        <!-- Body (takes remaining space and centers content) -->
        <div class="flex-1 px-6 py-4 flex justify-center items-center">
            <div class="flex justify-center items-center gap-10">
            <div class="w-fit border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-1 focus:ring-gray-300">
                <Multiselect
                v-model="selectedReport"
                :options="reports"
                :track-by="'id_report'"
                label="nama_report" 
                :multiple="false"
                :searchable="true"
                :close-on-select="true"
                :allow-empty="true"
                placeholder="Pilih Report"
                class="text-sm"
                />
            </div>
              <button
                type="button"
                @click="showPrintModal = true"
                :disabled="!selectedReport"
                class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 disabled:cursor-not-allowed"
              >
                Print
              </button>
            </div>
        </div>
      </div>


      <!-- Dashboard Container -->
      <div class="w-280 bg-white shadow overflow-hidden">
        <!-- Header -->
        <div class="bg-red-500 text-white text-lg font-semibold px-6 py-3 flex justify-between items-center">
          <span>Dashboard</span>
        </div>

        <!-- Power BI Iframe Without Halaman Tabs -->
        <div class="w-full h-[600px] px-6 py-3">
          <iframe
            src="https://app.powerbi.com/view?r=eyJrIjoiZmE3OTk5ZjAtNjhkNC00MGViLWE5N2QtODI2OWU1ODM1Mjc3IiwidCI6IjM0ODViOTYzLTgyYmEtNGE2Zi04MTBmLWI1Y2MyMjZmZjg5OCIsImMiOjEwfQ%3D%3D&pageName=65d95ef2131fbe2504d9"
            class="w-full h-full border-0"
            allowfullscreen
          ></iframe>
        </div>
      </div>

      <div v-if="showPrintModal" class="fixed backdrop-brightness-50 inset-0 z-100 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 shadow-md w-full max-w-sm">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold">Download Report</h2>
            <button @click="showPrintModal = false" class="text-gray-500 hover:text-gray-700 text-xl">&times;</button>
          </div>
          <div class="space-y-4">
            <button
              @click="handleDownload('pdf')"
              class="w-full text-sm px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
            >
              Download PDF
            </button>
            <button
              @click="handleDownload('excel')"
              class="w-full text-sm px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Download Excel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import axiosInstance from '@/lib/axios'
import Multiselect from 'vue-multiselect'

const globalLoading = inject('globalLoading')

// State
const reports = ref([])
const selectedReport = ref(null)
const showPrintModal = ref(false)

// Fetch reports from backend
const fetchReports = async () => {
  globalLoading.value = true
  try {
    const { data } = await axiosInstance.get('/reports')
    reports.value = Array.isArray(data) ? data : data.data || []
  } catch (err) {
    console.error('❌ Error fetching reports:', err)
    reports.value = []
  } finally {
    globalLoading.value = false
  }
}

const handleDownload = async (type) => {
  showPrintModal.value = false
  if (type === 'pdf') {
    await downloadReportPDF()
  } else if (type === 'excel') {
    await downloadReportExcel()
  }
}

const downloadReportExcel = async () => {
  if (!selectedReport.value?.id_report) return

  globalLoading.value = true

  try {
    const response = await axiosInstance.get(`/reports/${selectedReport.value.id_report}/excel`, {
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url

    const now = new Date()
    const day = String(now.getDate()).padStart(2, '0')
    const month = String(now.getMonth() + 1).padStart(2, '0')
    const year = now.getFullYear()
    const currentDate = `${day}-${month}-${year}`

    const filename = `${selectedReport.value.nama_report || 'report'} ${currentDate}.xlsx`
    link.setAttribute('download', filename)

    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (err) {
    console.error('❌ Failed to download report Excel:', err)
  } finally {
    globalLoading.value = false
  }
}


// Download selected report as PDF
const downloadReportPDF = async () => {
  if (!selectedReport.value?.id_report) {
    console.warn('⚠️ No report selected')
    return
  }

  globalLoading.value = true

  try {
    const response = await axiosInstance.get(`/reports/${selectedReport.value.id_report}/pdf`, {
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    const now = new Date()
    const day = String(now.getDate()).padStart(2, '0')
    const month = String(now.getMonth() + 1).padStart(2, '0') // Month is 0-based
    const year = now.getFullYear()
    const currentDate = `${day}-${month}-${year}` // e.g., "16-07-2025"
    const filename = `${selectedReport.value.nama_report || 'report'} ${currentDate}.pdf`
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (err) {
    console.error('❌ Failed to download report PDF:', err)
  } finally {
    globalLoading.value = false
  }
}


// On mount, load the reports
onMounted(fetchReports)
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