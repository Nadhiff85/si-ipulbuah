import { defineStore } from 'pinia'
import api from '../services/api'

// Data identitas toko (diambil dari endpoint publik /api/store-settings)
// Fallback dipakai sementara sebelum backend/API terhubung, supaya
// tampilan tetap bisa direview.
export const useStoreInfoStore = defineStore('storeInfo', {
  state: () => ({
    loaded: false,
    storeName: 'IPUL BUAH',
    tagline: 'Segar Setiap Hari, Sehat untuk Keluarga',
    aboutContent: '',
    address: 'Jl. Kemiri No. 47, Siranindi, Kec. Palu Barat, Kota Palu, Sulawesi Tengah, 94111',
    whatsappNumber: '6281200000000',
    bankAccounts: [],
    qrisImage: null,
    operatingHours: {
      buka: '08:00',
      tutup: '17:00',
    },
    deliveryRegions: ['Kota Palu', 'Kabupaten Sigi', 'Kabupaten Donggala'],
  }),

  getters: {
    // Status buka/tutup otomatis berdasarkan jam saat ini
    isOpenNow(state) {
      const now = new Date()
      const [openH, openM] = state.operatingHours.buka.split(':').map(Number)
      const [closeH, closeM] = state.operatingHours.tutup.split(':').map(Number)
      const openMinutes = openH * 60 + openM
      const closeMinutes = closeH * 60 + closeM
      const nowMinutes = now.getHours() * 60 + now.getMinutes()
      return nowMinutes >= openMinutes && nowMinutes < closeMinutes
    },

    googleMapsUrl(state) {
      return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(state.address)}`
    },

    googleMapsEmbedUrl(state) {
      return `https://maps.google.com/maps?q=${encodeURIComponent(state.address)}&z=16&output=embed`
    },

    whatsappUrl(state) {
      return `https://wa.me/${state.whatsappNumber}`
    },
  },

  actions: {
    async fetchStoreInfo() {
      try {
        const { data } = await api.get('/store-settings')
        this.$patch(data)
        this.loaded = true
      } catch (e) {
        // Backend belum aktif -> tetap pakai data fallback di atas
        this.loaded = false
      }
    },
  },
})
