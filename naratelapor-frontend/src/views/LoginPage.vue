<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
      <h1 class="text-2xl font-bold mb-6 text-center">Login - NarateLapor</h1>
      <form @submit.prevent="handleLogin">
        <div class="mb-4">
          <label class="block mb-1 font-medium">Email</label>
          <input type="email" v-model="email" required class="w-full p-2 border rounded-md" />
        </div>
        <div class="mb-6">
          <label class="block mb-1 font-medium">Password</label>
          <input type="password" v-model="password" required class="w-full p-2 border rounded-md" />
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
          Login
        </button>
      </form>
      <p v-if="error" class="text-red-500 text-sm mt-4 text-center">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const email = ref('')
const password = ref('')
const error = ref('')
const router = useRouter()

onMounted(() => {
  const token = localStorage.getItem('token')
  if (token && token !== 'undefined') {
    router.push('/dashboard')
  }
})

const handleLogin = async () => {
  error.value = ''
  try {
    const res = await fetch('http://localhost:8001/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value }),
    })
    const data = await res.json()
    if (!res.ok || !data.access_token) {
      error.value = data.message || 'Login gagal.'
      return
    }
    localStorage.setItem('token', data.access_token)
    router.push('/dashboard')
  } catch (e) {
    error.value = 'Terjadi kesalahan koneksi.'
  }
}
</script>