<template>
  <div class="max-w-3xl mx-auto p-6">
    <button @click="$router.back()" class="text-blue-600 hover:underline mb-4">&larr; Kembali</button>

    <div v-if="loading">Mengambil data laporan...</div>
    <div v-else-if="!report">Laporan tidak ditemukan.</div>
    <div v-else class="bg-white p-6 rounded shadow">
      <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ report.title || 'Untitled' }}</h1>
      <p class="text-sm text-gray-500 mb-1">Tanggal: {{ formatDate(report.date) }}</p>
      <span class="text-xs inline-block px-2 py-1 rounded font-semibold mb-4"
        :class="report.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
        {{ report.status }}
      </span>

      <p class="text-gray-700 whitespace-pre-line">{{ report.content }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const report = ref(null)
const loading = ref(true)

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric',
  })
}

onMounted(async () => {
  const token = localStorage.getItem('token')
  const id = route.params.id
  if (!token || !id) return

  try {
    const res = await fetch(`http://localhost:8002/reports/${id}`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })
    const data = await res.json()
    if (res.ok) {
      report.value = data.data
    } else {
      console.error('Gagal ambil detail laporan:', data.message)
    }
  } catch (err) {
    console.error('Gagal koneksi:', err)
  } finally {
    loading.value = false
  }
})
</script>
