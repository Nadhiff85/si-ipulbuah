<template>
  <div class="max-w-md mx-auto px-4 py-16">
    <div class="text-center mb-6">
      <img src="/images/logo.webp" alt="IPUL BUAH" class="h-16 w-16 mx-auto mb-3 object-contain" />
      <h1 class="text-xl font-bold text-ink">{{ step === 'credentials' ? 'Masuk ke Akun Anda' : 'Verifikasi Kode OTP' }}</h1>
    </div>

    <!-- Pesan alasan diarahkan ke login (mis. klik produk di beranda publik) -->
    <div v-if="reason && step === 'credentials'" class="bg-badge/15 text-ink text-sm rounded-xl2 px-4 py-3 mb-5 border border-badge/40">
      {{ reason }}
    </div>

    <!-- ===== Langkah 1: Email & Kata Sandi ===== -->
    <form v-if="step === 'credentials'" @submit.prevent="handleLogin" class="glass-card rounded-xl2 p-6 space-y-4">
      <div>
        <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5"><EnvelopeIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Email</label>
        <input v-model="form.email" type="email" required class="input" placeholder="nama@email.com" />
      </div>
      <div>
        <div class="flex items-center justify-between mb-1">
          <label class="text-sm font-medium text-ink flex items-center gap-1.5"><LockClosedIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Kata Sandi</label>
          <router-link to="/lupa-password" class="text-xs text-primary font-medium hover:underline">Lupa kata sandi?</router-link>
        </div>
        <div class="relative">
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required class="input pr-10" placeholder="••••••••" />
          <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink/40 hover:text-ink/70 transition cursor-pointer" tabindex="-1">
            <EyeSlashIcon v-if="showPassword" class="w-4 h-4" stroke-width="1.75" />
            <EyeIcon v-else class="w-4 h-4" stroke-width="1.75" />
          </button>
        </div>
      </div>

      <p v-if="error" class="text-danger text-sm">{{ error }}</p>

      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-full transition disabled:opacity-60 cursor-pointer disabled:cursor-not-allowed"
      >
        {{ loading ? 'Memproses...' : 'Masuk' }}
      </button>

      <p class="text-center text-sm text-ink/60">
        Belum punya akun?
        <router-link to="/register" class="text-primary font-medium hover:underline">Daftar di sini</router-link>
      </p>
    </form>

    <!-- ===== Langkah 2: Kode OTP ===== -->
    <div v-else class="glass-card rounded-xl2 p-6">
      <p class="text-sm text-ink/60 text-center mb-1">Kode 6 digit telah dikirim ke</p>
      <p class="text-sm font-bold text-ink text-center mb-5">{{ emailHint }}</p>

      <OtpInput
        ref="otpInputRef"
        v-model="otpCode"
        :disabled="verifying"
        :error="!!otpError"
        @complete="handleVerify"
      />

      <p v-if="otpError" class="text-danger text-sm text-center mt-3">{{ otpError }}</p>

      <button
        type="button"
        :disabled="verifying || otpCode.length !== 6"
        @click="handleVerify(otpCode)"
        class="w-full mt-5 bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-full transition disabled:opacity-60 cursor-pointer disabled:cursor-not-allowed"
      >
        {{ verifying ? 'Memverifikasi...' : 'Verifikasi & Masuk' }}
      </button>

      <div class="flex items-center justify-between mt-4 text-sm">
        <button type="button" @click="backToCredentials" class="text-ink/50 hover:text-ink cursor-pointer">
          &larr; Ganti akun
        </button>
        <button
          type="button"
          :disabled="resendCooldown > 0 || resending"
          @click="handleResend"
          class="font-medium text-primary hover:underline cursor-pointer disabled:text-ink/30 disabled:no-underline disabled:cursor-not-allowed"
        >
          {{ resendCooldown > 0 ? `Kirim ulang (${resendCooldown}s)` : resending ? 'Mengirim...' : 'Kirim ulang kode' }}
        </button>
      </div>
    </div>

    <p v-if="step === 'credentials'" class="text-center text-xs text-ink/40 mt-4">
      Staff Toko? <router-link to="/staff/login" class="text-ink/60 hover:underline">Masuk lewat Portal Staff</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { EnvelopeIcon, LockClosedIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import OtpInput from '../../components/shared/OtpInput.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const reason = route.query.reason || ''
const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

const step = ref('credentials') // 'credentials' | 'otp'
const challenge = ref('')
const emailHint = ref('')
const otpCode = ref('')
const otpError = ref('')
const otpInputRef = ref(null)
const verifying = ref(false)
const resending = ref(false)
const resendCooldown = ref(0)
let cooldownTimer = null
const showPassword = ref(false)

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    const data = await auth.requestLogin(form.value)
    challenge.value = data.challenge
    emailHint.value = data.email_hint
    step.value = 'otp'
    startCooldown()
  } catch (e) {
    error.value = e.response?.data?.message || 'Email atau kata sandi salah.'
  } finally {
    loading.value = false
  }
}

async function handleVerify(code) {
  if (code.length !== 6 || verifying.value) return
  verifying.value = true
  otpError.value = ''
  try {
    const user = await auth.verifyLoginOtp(challenge.value, code)

    // Proteksi: akun Admin/Superadmin diarahkan ke portal Staff, bukan toko pelanggan
    if (user.role === 'admin' || user.role === 'superadmin') {
      await auth.logout()
      error.value = 'Akun ini terdaftar sebagai Staff Toko. Silakan masuk lewat Portal Staff.'
      step.value = 'credentials'
      return
    }

    router.push(route.query.redirect || '/')
  } catch (e) {
    otpError.value = e.response?.data?.message || 'Kode OTP salah.'
    otpInputRef.value?.clear()
  } finally {
    verifying.value = false
  }
}

async function handleResend() {
  resending.value = true
  otpError.value = ''
  try {
    await auth.resendLoginOtp(challenge.value)
    otpInputRef.value?.clear()
    startCooldown()
  } catch (e) {
    otpError.value = e.response?.data?.message || 'Gagal mengirim ulang kode.'
  } finally {
    resending.value = false
  }
}

function startCooldown() {
  resendCooldown.value = 45
  clearInterval(cooldownTimer)
  cooldownTimer = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) clearInterval(cooldownTimer)
  }, 1000)
}

function backToCredentials() {
  step.value = 'credentials'
  otpCode.value = ''
  otpError.value = ''
  clearInterval(cooldownTimer)
}

onUnmounted(() => clearInterval(cooldownTimer))
</script>

<style scoped>
.input {
  @apply w-full bg-white border border-ink/10 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-accent/40;
}
</style>
