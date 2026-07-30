<template>
  <div class="min-h-screen bg-surface-soft flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <div class="text-center mb-6">
        <span class="h-16 w-16 mx-auto mb-3 rounded-2xl bg-primary text-white font-black text-xl flex items-center justify-center shadow-sm">IB</span>
        <h1 class="text-xl font-bold text-ink">
          {{ step === 'email' ? 'Lupa Kata Sandi' : step === 'reset' ? 'Verifikasi & Kata Sandi Baru' : 'Berhasil!' }}
        </h1>
        <p class="text-ink/50 text-sm mt-1">
          {{ step === 'email' ? 'Masukkan email akun Anda, kami kirim kode OTP untuk reset.' : step === 'reset' ? 'Masukkan kode dari email, lalu buat kata sandi baru.' : '' }}
        </p>
      </div>

      <!-- ===== Langkah 1: Email ===== -->
      <form v-if="step === 'email'" @submit.prevent="handleForgot" class="glass-card rounded-xl2 p-6 space-y-4">
        <div>
          <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5">
            <EnvelopeIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Email
          </label>
          <input v-model="email" type="email" required class="input" placeholder="nama@email.com" />
        </div>

        <p v-if="error" class="text-danger text-sm">{{ error }}</p>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-full transition disabled:opacity-60 cursor-pointer disabled:cursor-not-allowed"
        >
          {{ loading ? 'Mengirim kode...' : 'Kirim Kode OTP' }}
        </button>

        <p class="text-center text-sm text-ink/60">
          Ingat kata sandi?
          <router-link to="/login" class="text-primary font-medium hover:underline">Masuk di sini</router-link>
        </p>
      </form>

      <!-- ===== Langkah 2: OTP + Kata Sandi Baru ===== -->
      <form v-else-if="step === 'reset'" @submit.prevent="handleReset" class="glass-card rounded-xl2 p-6 space-y-4">
        <div>
          <p class="text-sm text-ink/60 text-center mb-1">Kode 6 digit telah dikirim ke</p>
          <p class="text-sm font-bold text-ink text-center mb-4">{{ emailHint }}</p>
          <OtpInput ref="otpInputRef" v-model="otpCode" :disabled="resetting" :error="!!error" />
        </div>

        <div>
          <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5">
            <LockClosedIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Kata Sandi Baru
          </label>
          <input v-model="password" type="password" required minlength="8" class="input" placeholder="Minimal 8 karakter" />
        </div>
        <div>
          <label class="text-sm font-medium text-ink block mb-1 flex items-center gap-1.5">
            <LockClosedIcon class="w-4 h-4 text-ink/50" stroke-width="1.75" /> Ulangi Kata Sandi Baru
          </label>
          <input v-model="passwordConfirmation" type="password" required class="input" placeholder="Ulangi kata sandi baru" />
        </div>

        <p v-if="error" class="text-danger text-sm">{{ error }}</p>

        <button
          type="submit"
          :disabled="resetting || otpCode.length !== 6"
          class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-full transition disabled:opacity-60 cursor-pointer disabled:cursor-not-allowed"
        >
          {{ resetting ? 'Menyimpan...' : 'Ubah Kata Sandi' }}
        </button>

        <div class="flex items-center justify-between text-sm">
          <button type="button" @click="backToEmail" class="text-ink/50 hover:text-ink cursor-pointer">
            &larr; Ganti email
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
      </form>

      <!-- ===== Langkah 3: Selesai ===== -->
      <div v-else class="glass-card rounded-xl2 p-6 text-center">
        <div class="w-14 h-14 rounded-full bg-success/15 flex items-center justify-center mx-auto mb-4">
          <CheckCircleIcon class="w-8 h-8 text-success" stroke-width="1.75" />
        </div>
        <p class="text-ink font-semibold mb-1">Kata sandi berhasil diperbarui</p>
        <p class="text-ink/50 text-sm mb-6">Silakan masuk kembali dengan kata sandi baru Anda.</p>
        <div class="flex gap-2">
          <router-link to="/login" class="flex-1 bg-primary hover:bg-primary-dark text-white font-semibold py-2.5 rounded-full transition text-sm text-center">
            Masuk Pelanggan
          </router-link>
          <router-link to="/staff/login" class="flex-1 border border-ink/15 text-ink hover:bg-ink/5 font-semibold py-2.5 rounded-full transition text-sm text-center">
            Portal Staff
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import api from '../services/api'
import { EnvelopeIcon, LockClosedIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import OtpInput from '../components/shared/OtpInput.vue'

const step = ref('email') // 'email' | 'reset' | 'done'
const email = ref('')
const challenge = ref('')
const emailHint = ref('')
const otpCode = ref('')
const otpInputRef = ref(null)
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const resetting = ref(false)
const resending = ref(false)
const error = ref('')
const resendCooldown = ref(0)
let cooldownTimer = null

async function handleForgot() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.post('/forgot-password', { email: email.value })
    challenge.value = data.challenge
    emailHint.value = data.email_hint
    step.value = 'reset'
    startCooldown()
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal mengirim kode OTP.'
  } finally {
    loading.value = false
  }
}

async function handleReset() {
  if (otpCode.value.length !== 6) return
  resetting.value = true
  error.value = ''
  try {
    await api.post('/forgot-password/reset', {
      challenge: challenge.value,
      code: otpCode.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    step.value = 'done'
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal mengubah kata sandi.'
    otpInputRef.value?.clear()
  } finally {
    resetting.value = false
  }
}

async function handleResend() {
  resending.value = true
  error.value = ''
  try {
    await api.post('/forgot-password/resend', { challenge: challenge.value })
    otpInputRef.value?.clear()
    startCooldown()
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal mengirim ulang kode.'
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

function backToEmail() {
  step.value = 'email'
  otpCode.value = ''
  password.value = ''
  passwordConfirmation.value = ''
  error.value = ''
  clearInterval(cooldownTimer)
}

onUnmounted(() => clearInterval(cooldownTimer))
</script>

<style scoped>
.input {
  @apply w-full bg-white border border-ink/10 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-accent/40;
}
</style>
