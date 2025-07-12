<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">Daftar Laporan</h1>

    <!-- Filter status -->
    <div class="mb-6">
      <label class="block mb-1 text-sm font-medium text-gray-600">Filter Status</label>
      <select v-model="filterStatus" @change="fetchReports" class="border rounded px-3 py-2">
        <option value="">Semua</option>
        <option value="draft">Draft</option>
        <option value="published">Published</option>
        <option value="reviewed">Reviewed</option>
      </select>
    </div>

    <!-- Tombol buat laporan -->
    <router-link
      to="/reports/create"
      class="inline-block mb-6 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
    >
      + Buat Laporan
    </router-link>

    <div v-if="loading" class="text-gray-500">Mengambil data laporan...</div>
    <div v-else-if="reports.length === 0" class="text-gray-500">Belum ada laporan.</div>

    <ul v-else class="space-y-4">
      <li v-for="report in reports" :key="report.id" class="p-4 bg-white rounded shadow">
        <!-- Judul -->
        <router-link
          :to="`/reports/${report.id}`"
          class="text-lg font-semibold text-blue-600 hover:underline block"
        >
          {{ report.title || '(Tanpa Judul)' }}
        </router-link>

        <!-- Tombol edit (hanya jika draft) -->
        <router-link
          v-if="report.status === 'draft'"
          :to="`/reports/${report.id}/edit`"
          class="text-sm text-blue-500 hover:underline"
        >
          Edit
        </router-link>

        <button @click="deleteReport(report.id)" class="text-sm text-red-500 hover:underline">
          Hapus
        </button>

        <!-- Tanggal & status -->
        <p class="text-sm text-gray-600">Tanggal: {{ formatDate(report.date) }}</p>
        <span
          class="inline-block mt-2 px-2 py-1 rounded text-xs font-medium"
          :class="
            report.status === 'published'
              ? 'bg-green-100 text-green-800'
              : report.status === 'reviewed'
                ? 'bg-blue-100 text-blue-800'
                : 'bg-yellow-100 text-yellow-800'
          "
        >
          {{ report.status }}
        </span>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const reports = ref([])
const loading = ref(true)
const filterStatus = ref('')

function formatDate(dateStr) {
  if (!dateStr || typeof dateStr !== 'string') return '-'
  const d = new Date(dateStr)
  return isNaN(d.getTime())
    ? '-'
    : d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      })
}

async function fetchReports() {
  loading.value = true
  const token = localStorage.getItem('token')
  if (!token) return

  let url = 'http://localhost:8002/reports'
  if (filterStatus.value) {
    url += `?status=${filterStatus.value}`
  }

  try {
    const res = await fetch(url, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    const data = await res.json()

    if (res.ok) {
      reports.value = data.data || []
    } else {
      console.error(data.message || 'Gagal mengambil laporan')
    }
  } catch (err) {
    console.error('Gagal koneksi ke server:', err)
  } finally {
    loading.value = false
  }
}

async function deleteReport(id) {
  const confirmDelete = confirm('Yakin ingin menghapus laporan ini?')
  if (!confirmDelete) return

  const token = localStorage.getItem('token')
  if (!token) return

  try {
    const res = await fetch(`http://localhost:8002/reports/${id}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    const data = await res.json()

    if (res.ok) {
      reports.value = reports.value.filter((r) => r.id !== id)
    } else {
      console.error(data.message || 'Gagal menghapus laporan')
    }
  } catch (err) {
    console.error('Gagal koneksi ke server:', err)
  }
}

onMounted(fetchReports)
</script>
