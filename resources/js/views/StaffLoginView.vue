<template>
  <div class="min-h-screen bg-primary-dark flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-6">
        <img src="/images/logo.webp" alt="IPUL BUAH" class="h-16 w-16 mx-auto mb-3 object-contain drop-shadow" />
        <h1 class="text-xl font-bold text-white">Portal Staff IPUL BUAH</h1>
        <p class="text-white/60 text-sm mt-1">{{ step === 'credentials' ? 'Khusus Admin Toko & Superadmin' : 'Verifikasi kode OTP untuk melanjutkan' }}</p>
      </div>

      <!-- ===== Langkah 1: Email & Kata Sandi ===== -->
      <form v-if="step === 'credentials'" @submit.prevent="handleLogin" class="glass-card rounded-xl2 shadow-lg p-6 space-y-4">
        <div>
          <label class="text-sm font-medium text-ink block mb-1">Email Staff</label>
          <input v-model="form.email" type="email" required class="input" placeholder="admin@ipulbuah.com" />
        </div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="text-sm font-medium text-ink">Kata Sandi</label>
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
          class="w-full bg-primary-dark hover:bg-ink text-white font-semibold py-3 rounded-full transition disabled:opacity-60 cursor-pointer disabled:cursor-not-allowed"
        >
          {{ loading ? 'Memproses...' : 'Masuk sebagai Staff' }}
        </button>

        <p class="text-center text-sm text-ink/60">
          Bukan staff toko?
          <router-link to="/login" class="text-primary font-medium hover:underline">Masuk sebagai Pelanggan</router-link>
        </p>
      </form>

      <!-- ===== Langkah 2: Kode OTP ===== -->
      <div v-else class="glass-card rounded-xl2 shadow-lg p-6">
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
          class="w-full mt-5 bg-primary-dark hover:bg-ink text-white font-semibold py-3 rounded-full transition disabled:opacity-60 cursor-pointer disabled:cursor-not-allowed"
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
    </div>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import OtpInput from '../components/shared/OtpInput.vue'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const auth = useAuthStore()

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

    // Proteksi: akun Pelanggan tidak boleh masuk lewat portal Staff
    if (user.role === 'pelanggan') {
      await auth.logout()
      error.value = 'Akun ini terdaftar sebagai Pelanggan. Silakan masuk lewat halaman Pelanggan.'
      step.value = 'credentials'
      return
    }

    router.push(user.role === 'superadmin' ? '/superadmin' : '/admin')
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
