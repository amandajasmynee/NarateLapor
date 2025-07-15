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

      <!-- Rating Section -->
      <div class="mt-6 border-t pt-4">
        <!-- Tampilkan rating -->
        <template v-if="rating && !editMode">
          <h3 class="text-lg font-semibold text-gray-800">Rating dari Supervisor:</h3>
          <p class="mt-1 flex items-center gap-1">
            <strong>Rating:</strong>
            <span class="text-2xl">
              <span
                v-for="n in 5"
                :key="n"
                class="text-3xl"
                :style="{ color: '#facc15' }"
              >
                {{ rating.rating >= n ? '⭐' : '☆' }}
              </span>
            </span>
          </p>
          <p><strong>Komentar:</strong> {{ rating.comment || '-' }}</p>

          <div v-if="role === 'supervisor'" class="flex gap-4 mt-3">
            <button
              @click="startEditRating"
              class="text-sm text-blue-600 hover:underline"
            >
              ✏️ Ubah Rating
            </button>
            <button
              @click="deleteRating"
              class="text-sm text-red-600 hover:underline"
            >
              🗑️ Hapus Rating
            </button>
          </div>
        </template>

        <!-- Form edit rating -->
        <template v-else-if="editMode">
          <h3 class="text-lg font-semibold text-gray-800 mb-2">Ubah Rating:</h3>
          <form @submit.prevent="updateRating" class="space-y-4">
            <div>
              <label class="block mb-1">Rating:</label>
              <div class="flex items-center space-x-1 mb-2">
                <span
                  v-for="n in 5"
                  :key="n"
                  @click="setRating(n)"
                  class="text-3xl cursor-pointer hover:scale-110 transition-transform"
                  :style="{ color: '#facc15' }"
                >
                  {{ newRating.rating >= n ? '⭐' : '☆' }}
                </span>
              </div>
            </div>
            <div>
              <label class="block">Komentar:</label>
              <textarea v-model="newRating.comment" rows="3" class="border p-2 w-full"></textarea>
            </div>
            <div class="flex gap-2">
              <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Simpan Perubahan
              </button>
              <button type="button" @click="editMode = false" class="text-gray-500 hover:underline">
                Batal
              </button>
            </div>
          </form>
        </template>

        <!-- Form tambah rating -->
        <template v-else-if="role === 'supervisor'">
          <h3 class="text-lg font-semibold text-gray-800 mb-2">Beri Rating:</h3>
          <form @submit.prevent="submitRating" class="space-y-4">
            <div>
              <label class="block mb-1">Rating:</label>
              <div class="flex items-center space-x-1 mb-2">
                <span
                  v-for="n in 5"
                  :key="n"
                  @click="setRating(n)"
                  class="text-3xl cursor-pointer hover:scale-110 transition-transform"
                  :style="{ color: '#facc15' }"
                >
                  {{ newRating.rating >= n ? '⭐' : '☆' }}
                </span>
              </div>
            </div>
            <div>
              <label class="block">Komentar (opsional):</label>
              <textarea v-model="newRating.comment" rows="3" class="border p-2 w-full"></textarea>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              Kirim Rating
            </button>
          </form>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const report = ref(null)
const rating = ref(null)
const loading = ref(true)
const editMode = ref(false)
const newRating = ref({ rating: 0, comment: '' })

const token = localStorage.getItem('token')
const role = localStorage.getItem('role')
const id = route.params.id

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric',
  })
}

function setRating(n) {
  newRating.value.rating = n
}

function startEditRating() {
  newRating.value.rating = rating.value.rating
  newRating.value.comment = rating.value.comment || ''
  editMode.value = true
}

onMounted(async () => {
  if (!token || !id) return

  try {
    const res = await fetch(`http://localhost:8002/reports/${id}`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    const data = await res.json()
    if (res.ok) {
      report.value = data.data
    }

    const ratingRes = await fetch(`http://localhost:8003/ratings/${id}`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    if (ratingRes.ok) {
      const ratingData = await ratingRes.json()
      rating.value = ratingData.data
    }
  } catch (err) {
    console.error('Error:', err)
  } finally {
    loading.value = false
  }
})

async function submitRating() {
  if (newRating.value.rating < 1 || newRating.value.rating > 5) {
    alert('Rating harus antara 1 - 5 bintang!')
    return
  }

  try {
    const res = await fetch('http://localhost:8003/ratings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        report_id: id,
        rating: newRating.value.rating,
        comment: newRating.value.comment,
      }),
    })

    if (res.ok) {
      const data = await res.json()
      rating.value = data.data
      alert('Rating berhasil dikirim!')
    } else if (res.status === 409) {
      alert('Kamu sudah memberi rating untuk laporan ini.')
    } else {
      const errText = await res.text()
      console.error('Gagal kirim rating:', errText)
      alert('Gagal mengirim rating. Cek console.')
    }
  } catch (error) {
    console.error('Submit error:', error)
    alert('Terjadi kesalahan saat mengirim rating.')
  }
}

async function updateRating() {
  if (newRating.value.rating < 1 || newRating.value.rating > 5) {
    alert('Rating harus antara 1 - 5 bintang!')
    return
  }

  try {
    const res = await fetch(`http://localhost:8003/ratings/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        rating: newRating.value.rating,
        comment: newRating.value.comment,
      }),
    })

    if (res.ok) {
      const data = await res.json()
      rating.value = data.data
      editMode.value = false
      alert('Rating berhasil diperbarui!')
    } else {
      const errText = await res.text()
      console.error('Gagal update rating:', errText)
      alert('Gagal mengubah rating. Cek console.')
    }
  } catch (error) {
    console.error('Update error:', error)
    alert('Terjadi kesalahan saat mengubah rating.')
  }
}

async function deleteRating() {
  if (!confirm('Yakin ingin menghapus rating ini?')) return

  try {
    const res = await fetch(`http://localhost:8003/ratings/${id}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    if (res.ok) {
      rating.value = null
      alert('Rating berhasil dihapus.')
    } else {
      const errText = await res.text()
      console.error('Gagal hapus rating:', errText)
      alert('Gagal menghapus rating. Cek console.')
    }
  } catch (error) {
    console.error('Delete error:', error)
    alert('Terjadi kesalahan saat menghapus rating.')
  }
}
</script>
