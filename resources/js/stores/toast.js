import { defineStore } from 'pinia'

// Store global untuk notifikasi toast singkat (mis. error koneksi/server yang
// tidak tertangani komponen manapun) - supaya tidak ada halaman yang gagal
// senyap tanpa pesan ke pengguna.
export const useToastStore = defineStore('toast', {
  state: () => ({
    message: '',
    type: 'error', // error | success | info
    visible: false,
  }),
  actions: {
    show(message, type = 'error') {
      this.message = message
      this.type = type
      this.visible = true
      clearTimeout(this._timer)
      this._timer = setTimeout(() => {
        this.visible = false
      }, 4000)
    },
    hide() {
      this.visible = false
    },
  },
})
