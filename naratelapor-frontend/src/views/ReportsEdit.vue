<template>
  <div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Laporan</h1>

    <form @submit.prevent="updateReport">
      <div class="mb-4">
        <label class="block mb-1 text-gray-700">Judul</label>
        <input v-model="title" type="text" class="w-full border rounded px-3 py-2" />
      </div>

      <div class="mb-4">
        <label class="block mb-1 text-gray-700">Isi Laporan</label>
        <textarea v-model="content" rows="6" class="w-full border rounded px-3 py-2"></textarea>
      </div>

      <div class="flex gap-2">
        <button
          type="submit"
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          Simpan Perubahan
        </button>
        <router-link
          to="/reports"
          class="px-4 py-2 rounded border border-gray-400 text-gray-600 hover:bg-gray-100"
        >
          Batal
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const id = route.params.id

const title = ref('')
const content = ref('')

onMounted(async () => {
  const token = localStorage.getItem('token')
  const res = await fetch(`http://localhost:8002/reports/${id}`, {
    headers: { Authorization: `Bearer ${token}` }
  })
  const data = await res.json()
  if (res.ok) {
    title.value = data.data.title
    content.value = data.data.content
  }
})

async function updateReport() {
  const token = localStorage.getItem('token')
  const res = await fetch(`http://localhost:8002/reports/${id}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({
      title: title.value,
      content: content.value,
      status: 'draft', // status tetap draft saat edit
    }),
  })

  if (res.ok) {
    router.push('/reports')
  } else {
    const error = await res.json()
    alert(error.message || 'Gagal mengupdate laporan')
  }
}
</script>
