<template>
  <div class="max-w-md mx-auto px-4 py-16">
    <div class="text-center mb-6">
      <span class="h-16 w-16 mx-auto mb-3 rounded-2xl bg-accent text-white font-black text-xl flex items-center justify-center shadow-sm">IB</span>
      <h1 class="text-xl font-bold text-ink">Buat Akun Baru</h1>
      <p class="text-ink/60 text-sm mt-1">Daftar untuk mulai belanja buah segar dari rumah</p>
    </div>

    <form @submit.prevent="handleRegister" class="glass-card rounded-xl2 p-6 space-y-4">
      <div>
        <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5"><UserIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Nama Lengkap</label>
        <input v-model="form.name" type="text" required class="input" placeholder="Nama Anda" />
      </div>
      <div>
        <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5"><EnvelopeIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Email</label>
        <input v-model="form.email" type="email" required class="input" placeholder="nama@email.com" />
      </div>
      <div>
        <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5"><DevicePhoneMobileIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> No. WhatsApp</label>
        <input v-model="form.phone" type="tel" required class="input" placeholder="08xxxxxxxxxx" />
      </div>
      <div>
        <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5"><LockClosedIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Kata Sandi</label>
        <input v-model="form.password" type="password" required minlength="8" class="input" placeholder="Minimal 8 karakter" />
      </div>
      <div>
        <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5"><LockClosedIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Konfirmasi Kata Sandi</label>
        <input v-model="form.password_confirmation" type="password" required class="input" placeholder="Ulangi kata sandi" />
      </div>

      <p v-if="error" class="text-danger text-sm">{{ error }}</p>

      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-accent hover:bg-accent-light text-white font-semibold py-3 rounded-full transition disabled:opacity-60 cursor-pointer disabled:cursor-not-allowed"
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
import { UserIcon, EnvelopeIcon, DevicePhoneMobileIcon, LockClosedIcon } from '@heroicons/vue/24/outline'

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
  @apply w-full bg-white border border-ink/10 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-accent/40;
}
</style>
