<template>
  <div class="p-6 max-w-4xl mx-auto">
    <router-link to="/dashboard" class="text-blue-600 hover:underline block mb-4">
      ← Kembali ke Dashboard
    </router-link>

    <h1 class="text-2xl font-bold mb-4 text-gray-800">Semua Laporan Intern</h1>

    <!-- Filter status -->
    <div class="mb-6">
      <label class="block mb-1 text-sm font-medium text-gray-600">Filter Status</label>
      <select v-model="filterStatus" @change="fetchReports" class="border rounded px-3 py-2">
        <option value="">Semua</option>
        <option value="submitted">Submitted</option>
        <option value="reviewed">Reviewed</option>
        <option value="revised">Revised</option>
        <option value="'done'">Done</option>
      </select>
    </div>

    <div v-if="loading" class="text-gray-500">Mengambil data laporan...</div>
    <div v-else-if="reports.length === 0" class="text-gray-500">Belum ada laporan ditemukan.</div>

    <ul v-else class="space-y-4">
      <li v-for="report in reports" :key="report.id" class="p-4 bg-white rounded shadow">
        <div class="flex justify-between items-center mb-1">
          <h2 class="text-lg font-semibold text-blue-600">
            {{ report.title || 'Untitled' }}
          </h2>
          <span
            class="text-xs px-2 py-1 rounded font-medium"
            :class="{
              'bg-yellow-100 text-yellow-800': report.status === 'draft',
              'bg-blue-100 text-blue-800': report.status === 'submitted',
              'bg-green-100 text-green-800': report.status === 'reviewed',
              'bg-red-100 text-red-800': report.status === 'revised',
              'bg-gray-200 text-gray-800': report.status === 'done' 
            }"
          >
            {{ report.status }}
          </span>
        </div>
        <p class="text-sm text-gray-600 mb-1">Tanggal: {{ formatDate(report.date) }}</p>
        <p class="text-sm text-gray-500">Intern Name: {{ report.username }}</p>
        <router-link :to="`/reports/${report.id}`" class="text-blue-500 text-sm hover:underline">
          Lihat Detail
        </router-link>

        <!-- Tombol aksi -->
        <div class="flex gap-2 mt-2">
          <button
            v-if="report.status === 'reviewed'"
            @click="reviewReports(report.id, 'done')"
            class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm"
          >
            Tandai Selesai
          </button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const reports = ref([])
const filterStatus = ref('')
const loading = ref(true)

function formatDate(dateStr) {
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
  let url = `http://localhost:8002/reports/all`
  if (filterStatus.value) {
    url += `?status=${filterStatus.value}`
  }

  try {
    const res = await fetch(url, {
      headers: { Authorization: `Bearer ${token}` },
    })

    const data = await res.json()
    if (res.ok) {
      reports.value = data.data || []
    } else {
      console.error(data.message)
      reports.value = []
    }
  } catch (err) {
    console.error('Gagal koneksi:', err)
    reports.value = []
  } finally {
    loading.value = false
  }
}

async function reviewReports(id, status) {
  const token = localStorage.getItem('token')
  const confirmed = confirm(`Yakin ingin menandai laporan ini sebagai ${status}?`)
  if (!confirmed) return

  try {
    const res = await fetch(`http://localhost:8002/reports/${id}/review`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({ status }),
    })

    const data = await res.json()
    if (res.ok) {
      alert('Berhasil memperbarui status laporan.')
      fetchReports()
    } else {
      console.warn('Respon error:', data)
      alert(data.message || 'Gagal update status.')
    }
  } catch (err) {
    console.error(err)
    alert('Terjadi kesalahan saat menghubungi server.')
  }
}

onMounted(fetchReports)
</script>
