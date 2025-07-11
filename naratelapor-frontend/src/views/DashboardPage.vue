<template>
  <div class="min-h-screen flex items-center justify-center bg-green-100">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-green-700">
        Halo, {{ user?.name || 'Pengguna' }} ({{ capitalizeRole(user?.role) }})! 👋
      </h1>
      <p class="text-gray-700 mt-2">
        Selamat datang di Dashboard NarateLapor 🎉
      </p>
      <p class="text-sm text-gray-500 mt-1">
        Email: {{ user?.email || '-' }}
      </p>

      <!-- Tombol ke laporan -->
      <router-link
        to="/reports"
        class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      >
        Lihat Laporan Saya
      </router-link>

      <!-- Tombol Logout -->
      <button
        @click="logout"
        class="mt-4 block mx-auto px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition"
      >
        Logout
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const user = ref(null)
const router = useRouter()

function getUserFromToken() {
  const token = localStorage.getItem('token')
  if (!token || token === 'undefined') return null
  try {
    const payloadBase64 = token.split('.')[1]
    const decodedPayload = JSON.parse(atob(payloadBase64))
    return decodedPayload
  } catch (e) {
    console.error('Gagal decode token:', e)
    return null
  }
}

onMounted(() => {
  user.value = getUserFromToken()
})

function logout() {
  localStorage.removeItem('token')
  router.push('/')
}

function capitalizeRole(role) {
  if (!role) return 'Pengguna'
  return role.charAt(0).toUpperCase() + role.slice(1)
}
</script>
