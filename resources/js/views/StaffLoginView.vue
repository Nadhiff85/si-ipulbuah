<template>
  <div class="min-h-screen bg-primary-dark flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-6">
        <img src="/logo.png" alt="IPUL BUAH" class="h-16 w-16 mx-auto mb-3 bg-white rounded-full p-2" />
        <h1 class="text-xl font-bold text-white">Portal Staff IPUL BUAH</h1>
        <p class="text-white/60 text-sm mt-1">Khusus Admin Toko & Superadmin</p>
      </div>

      <form @submit.prevent="handleLogin" class="bg-white rounded-xl2 shadow-lg p-6 space-y-4">
        <div>
          <label class="text-sm font-medium text-ink block mb-1">Email Staff</label>
          <input v-model="form.email" type="email" required class="input" placeholder="admin@ipulbuah.com" />
        </div>
        <div>
          <label class="text-sm font-medium text-ink block mb-1">Kata Sandi</label>
          <input v-model="form.password" type="password" required class="input" placeholder="••••••••" />
        </div>

        <p v-if="error" class="text-danger text-sm">{{ error }}</p>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-primary-dark hover:bg-ink text-white font-semibold py-3 rounded-full transition disabled:opacity-60"
        >
          {{ loading ? 'Memproses...' : 'Masuk sebagai Staff' }}
        </button>

        <p class="text-center text-sm text-ink/60">
          Bukan staff toko?
          <router-link to="/login" class="text-primary font-medium hover:underline">Masuk sebagai Pelanggan</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    const user = await auth.login(form.value)

    // Proteksi: akun Pelanggan tidak boleh masuk lewat portal Staff
    if (user.role === 'pelanggan') {
      await auth.logout()
      error.value = 'Akun ini terdaftar sebagai Pelanggan. Silakan masuk lewat halaman Pelanggan.'
      return
    }

    router.push(user.role === 'superadmin' ? '/superadmin' : '/admin')
  } catch (e) {
    error.value = e.response?.data?.message || 'Email atau kata sandi salah.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.input {
  @apply w-full border border-ink/15 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/40;
}
</style>
