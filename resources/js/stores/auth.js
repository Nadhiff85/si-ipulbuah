import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('ipulbuah_user') || 'null'),
    token: localStorage.getItem('ipulbuah_token') || null,
    // Mode Pratinjau Toko: staff (admin/superadmin) sengaja masuk lihat tampilan
    // pelanggan untuk cek hasil perubahan (produk, banner, konten, dll)
    previewMode: sessionStorage.getItem('ipulbuah_preview_mode') === '1',
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    isStaff: (state) => state.user?.role === 'admin' || state.user?.role === 'superadmin',
  },

  actions: {
    enablePreview() {
      this.previewMode = true
      sessionStorage.setItem('ipulbuah_preview_mode', '1')
    },
    disablePreview() {
      this.previewMode = false
      sessionStorage.removeItem('ipulbuah_preview_mode')
    },
    // Langkah 1: kirim email+password. Kredensial benar -> backend mengirim
    // kode OTP ke email dan mengembalikan { otp_required, challenge, ... }
    // TANPA token - sesi baru dibuat setelah verifyLoginOtp() berhasil.
    async requestLogin(credentials) {
      const { data } = await api.post('/login', credentials)
      return data
    },

    // Langkah 2: kode OTP dikonfirmasi -> token diterbitkan & sesi disimpan
    async verifyLoginOtp(challenge, code) {
      const { data } = await api.post('/login/verify-otp', { challenge, code })
      this.setSession(data.user, data.token)
      return data.user
    },

    async resendLoginOtp(challenge) {
      const { data } = await api.post('/login/resend-otp', { challenge })
      return data
    },

    // Langkah 1: kirim data pendaftaran. Sukses -> backend mengirim kode OTP
    // ke email dan mengembalikan { otp_required, challenge, ... } TANPA
    // token - sesi baru dibuat setelah verifyRegisterOtp() berhasil.
    async requestRegister(payload) {
      const { data } = await api.post('/register', payload)
      return data
    },

    // Langkah 2: kode OTP dikonfirmasi -> email terverifikasi, token diterbitkan
    async verifyRegisterOtp(challenge, code) {
      const { data } = await api.post('/register/verify-otp', { challenge, code })
      this.setSession(data.user, data.token)
      return data.user
    },

    async resendRegisterOtp(challenge) {
      const { data } = await api.post('/register/resend-otp', { challenge })
      return data
    },

    async logout() {
      try { await api.post('/logout') } catch (e) { /* ignore */ }
      this.user = null
      this.token = null
      this.disablePreview()
      localStorage.removeItem('ipulbuah_user')
      localStorage.removeItem('ipulbuah_token')
    },

    setSession(user, token) {
      this.user = user
      this.token = token
      localStorage.setItem('ipulbuah_user', JSON.stringify(user))
      localStorage.setItem('ipulbuah_token', token)
    },
  },
})
