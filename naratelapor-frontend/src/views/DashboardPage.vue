<template>
  <div v-if="user" class="min-h-screen flex items-center justify-center bg-green-100">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-green-700">
        Halo, {{ user?.name || 'Pengguna' }} 
        <span class="text-sm text-gray-600">({{ capitalizeRole(user?.role) }})</span> 👋
      </h1>
      <p class="text-gray-700 mt-2">
        Selamat datang di Dashboard NarateLapor 🎉
      </p>
      <p class="text-sm text-gray-500 mt-1">
        Email: {{ user?.email || '-' }}
      </p>

      <!-- Tombol berdasarkan role -->
      <div class="mt-6 space-y-3">
        <router-link
          v-if="user?.role === 'intern'"
          to="/reports"
          class="block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          Lihat Laporan Saya 📝
        </router-link>

        <router-link
          v-if="user?.role === 'supervisor' || user?.role === 'admin'"
          to="/supervisor-reports"
          class="block bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600"
        >
          Lihat Semua Laporan Intern 🔍
        </router-link>
      </div>

      <!-- Tombol Logout -->
      <button
        @click="logout"
        class="mt-6 px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition"
      >
        Logout
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const user = ref(null)

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
  const expectedRole = route.params.role

  // ⛔ Kalau role di URL gak cocok sama token, redirect ke yang benar
  if (user.value && user.value.role !== expectedRole) {
    router.push(`/dashboard/${user.value.role}`)
  }
})

function logout() {
  localStorage.clear()
  router.push('/')
}

function capitalizeRole(role) {
  if (!role) return 'Pengguna'
  return role.charAt(0).toUpperCase() + role.slice(1)
}
</script>