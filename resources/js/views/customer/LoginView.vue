<template>
  <div class="max-w-md mx-auto px-4 py-16">
    <div class="text-center mb-6">
      <img src="/logo.png" alt="IPUL BUAH" class="h-16 w-16 mx-auto mb-3" />
      <h1 class="text-xl font-bold text-ink">Masuk ke Akun Anda</h1>
    </div>

    <!-- Pesan alasan diarahkan ke login (mis. klik produk di beranda publik) -->
    <div v-if="reason" class="bg-badge/15 text-ink text-sm rounded-xl2 px-4 py-3 mb-5 border border-badge/40">
      {{ reason }}
    </div>

    <form @submit.prevent="handleLogin" class="bg-white rounded-xl2 shadow-sm border border-ink/5 p-6 space-y-4">
      <div>
        <label class="text-sm font-medium text-ink block mb-1">Email</label>
        <input v-model="form.email" type="email" required class="input" placeholder="nama@email.com" />
      </div>
      <div>
        <label class="text-sm font-medium text-ink block mb-1">Kata Sandi</label>
        <input v-model="form.password" type="password" required class="input" placeholder="••••••••" />
      </div>

      <p v-if="error" class="text-danger text-sm">{{ error }}</p>

      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-full transition disabled:opacity-60"
      >
        {{ loading ? 'Memproses...' : 'Masuk' }}
      </button>

      <p class="text-center text-sm text-ink/60">
        Belum punya akun?
        <router-link to="/register" class="text-primary font-medium hover:underline">Daftar di sini</router-link>
      </p>
    </form>

    <p class="text-center text-xs text-ink/40 mt-4">
      Staff Toko? <router-link to="/staff/login" class="text-ink/60 hover:underline">Masuk lewat Portal Staff</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const reason = route.query.reason || ''
const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    const user = await auth.login(form.value)

    // Proteksi: akun Admin/Superadmin diarahkan ke portal Staff, bukan toko pelanggan
    if (user.role === 'admin' || user.role === 'superadmin') {
      await auth.logout()
      error.value = 'Akun ini terdaftar sebagai Staff Toko. Silakan masuk lewat Portal Staff.'
      return
    }

    router.push(route.query.redirect || '/')
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
