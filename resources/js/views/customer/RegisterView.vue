<template>
  <div class="max-w-md mx-auto px-4 py-16">
    <div class="text-center mb-6">
      <img src="/logo.png" alt="IPUL BUAH" class="h-16 w-16 mx-auto mb-3" />
      <h1 class="text-xl font-bold text-ink">Buat Akun Baru</h1>
      <p class="text-ink/60 text-sm mt-1">Daftar untuk mulai belanja buah segar dari rumah 🍊</p>
    </div>

    <form @submit.prevent="handleRegister" class="bg-white rounded-xl2 shadow-sm border border-ink/5 p-6 space-y-4">
      <div>
        <label class="text-sm font-medium text-ink block mb-1">Nama Lengkap</label>
        <input v-model="form.name" type="text" required class="input" placeholder="Nama Anda" />
      </div>
      <div>
        <label class="text-sm font-medium text-ink block mb-1">Email</label>
        <input v-model="form.email" type="email" required class="input" placeholder="nama@email.com" />
      </div>
      <div>
        <label class="text-sm font-medium text-ink block mb-1">No. WhatsApp</label>
        <input v-model="form.phone" type="tel" required class="input" placeholder="08xxxxxxxxxx" />
      </div>
      <div>
        <label class="text-sm font-medium text-ink block mb-1">Kata Sandi</label>
        <input v-model="form.password" type="password" required minlength="8" class="input" placeholder="Minimal 8 karakter" />
      </div>
      <div>
        <label class="text-sm font-medium text-ink block mb-1">Konfirmasi Kata Sandi</label>
        <input v-model="form.password_confirmation" type="password" required class="input" placeholder="Ulangi kata sandi" />
      </div>

      <p v-if="error" class="text-danger text-sm">{{ error }}</p>

      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-accent hover:bg-accent-light text-white font-semibold py-3 rounded-full transition disabled:opacity-60"
      >
        {{ loading ? 'Memproses...' : 'Daftar Sekarang' }}
      </button>

      <p class="text-center text-sm text-ink/60">
        Sudah punya akun?
        <router-link to="/login" class="text-primary font-medium hover:underline">Masuk di sini</router-link>
      </p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const form = ref({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error = ref('')

async function handleRegister() {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Konfirmasi kata sandi tidak cocok.'
    return
  }

  loading.value = true
  error.value = ''
  try {
    await auth.register(form.value)
    router.push(route.query.redirect || '/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Pendaftaran gagal. Coba lagi.'
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
