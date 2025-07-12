<template>
  <div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Buat Laporan Baru</h1>

    <form @submit.prevent="submitReport" class="space-y-4">
      <div>
        <label class="block font-medium mb-1">Judul</label>
        <input v-model="title" required class="w-full border rounded p-2" placeholder="Judul laporan" />
      </div>

      <div>
        <label class="block font-medium mb-1">Konten</label>
        <textarea v-model="content" required class="w-full border rounded p-2" rows="6" placeholder="Isi laporan..."></textarea>
      </div>

      <button
        type="submit"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Simpan Laporan
      </button>

      <p v-if="message" class="text-green-600 mt-2">{{ message }}</p>
      <p v-if="error" class="text-red-600 mt-2">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const title = ref('')
const content = ref('')
const message = ref('')
const error = ref('')
const router = useRouter()

const submitReport = async () => {
  message.value = ''
  error.value = ''

  const token = localStorage.getItem('token')
  if (!token) return (error.value = 'Token tidak ditemukan')

  try {
    const res = await fetch('http://localhost:8002/reports', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        title: title.value,
        content: content.value,
      }),
    })

    const data = await res.json()

    if (res.ok) {
      message.value = 'Laporan berhasil dibuat!'
      setTimeout(() => {
        router.push('/reports')
      }, 1000)
    } else {
      error.value = data.message || 'Gagal membuat laporan.'
    }
  } catch (err) {
    error.value = 'Gagal koneksi ke server.'
  }
}
</script>
