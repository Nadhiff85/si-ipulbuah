<template>
  <div class="max-w-md mx-auto px-4 py-16">
    <div class="text-center mb-6">
      <span class="h-16 w-16 mx-auto mb-3 rounded-2xl bg-accent text-white font-black text-xl flex items-center justify-center shadow-sm">IB</span>
      <h1 class="text-xl font-bold text-ink">{{ step === 'form' ? 'Buat Akun Baru' : 'Verifikasi Email Anda' }}</h1>
      <p class="text-ink/60 text-sm mt-1">
        {{ step === 'form' ? 'Daftar untuk mulai belanja buah segar dari rumah' : 'Masukkan kode OTP untuk membuktikan email ini benar milik Anda' }}
      </p>
    </div>

    <!-- ===== Langkah 1: Form Pendaftaran ===== -->
    <form v-if="step === 'form'" @submit.prevent="handleRegister" class="glass-card rounded-xl2 p-6 space-y-4">
      <div>
        <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5"><UserIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Nama Lengkap</label>
        <input v-model="form.name" type="text" required class="input" placeholder="Nama Anda" />
      </div>
      <div>
        <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5"><EnvelopeIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Email</label>
        <input v-model="form.email" type="email" required class="input" placeholder="nama@email.com" />
        <p class="text-[11px] text-ink/40 mt-1">Kode verifikasi akan dikirim ke email ini - pastikan aktif.</p>
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
        class="w-full mt-5 bg-accent hover:bg-accent-light text-white font-semibold py-3 rounded-full transition disabled:opacity-60 cursor-pointer disabled:cursor-not-allowed"
      >
        {{ verifying ? 'Memverifikasi...' : 'Verifikasi & Mulai Belanja' }}
      </button>

      <div class="flex items-center justify-between mt-4 text-sm">
        <button type="button" @click="backToForm" class="text-ink/50 hover:text-ink cursor-pointer">
          &larr; Ubah data
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
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { UserIcon, EnvelopeIcon, DevicePhoneMobileIcon, LockClosedIcon } from '@heroicons/vue/24/outline'
import OtpInput from '../../components/shared/OtpInput.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const form = ref({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error = ref('')

const step = ref('form') // 'form' | 'otp'
const challenge = ref('')
const emailHint = ref('')
const otpCode = ref('')
const otpError = ref('')
const otpInputRef = ref(null)
const verifying = ref(false)
const resending = ref(false)
const resendCooldown = ref(0)
let cooldownTimer = null

async function handleRegister() {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Konfirmasi kata sandi tidak cocok.'
    return
  }

  loading.value = true
  error.value = ''
  try {
    const data = await auth.requestRegister(form.value)
    challenge.value = data.challenge
    emailHint.value = data.email_hint
    step.value = 'otp'
    startCooldown()
  } catch (e) {
    error.value = e.response?.data?.message || 'Pendaftaran gagal. Coba lagi.'
  } finally {
    loading.value = false
  }
}

async function handleVerify(code) {
  if (code.length !== 6 || verifying.value) return
  verifying.value = true
  otpError.value = ''
  try {
    await auth.verifyRegisterOtp(challenge.value, code)
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
    await auth.resendRegisterOtp(challenge.value)
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

function backToForm() {
  step.value = 'form'
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
