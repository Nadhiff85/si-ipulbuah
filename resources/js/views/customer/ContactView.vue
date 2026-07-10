<template>
  <div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-ink mb-6">Hubungi Kami</h1>

    <div class="grid sm:grid-cols-2 gap-5">
      <div class="bg-white rounded-xl2 border border-ink/5 p-5">
        <p class="font-semibold text-ink mb-2 flex items-center gap-1.5">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-primary" fill="currentColor">
            <path d="M16 10c0-2.21-1.79-4-4-4s-4 1.79-4 4s1.79 4 4 4s4-1.79 4-4m-6 0c0-1.1.9-2 2-2s2 .9 2 2s-.9 2-2 2s-2-.9-2-2"></path>
            <path d="M11.42 21.81c.17.12.38.19.58.19s.41-.06.58-.19c.3-.22 7.45-5.37 7.42-11.82c0-4.41-3.59-8-8-8s-8 3.59-8 8c-.03 6.44 7.12 11.6 7.42 11.82M12 4c3.31 0 6 2.69 6 6c.02 4.44-4.39 8.43-6 9.74c-1.61-1.31-6.02-5.29-6-9.74c0-3.31 2.69-6 6-6"></path>
          </svg>
          Alamat Toko
        </p>
        <p class="text-ink/70 text-sm mb-3">{{ storeInfo.address }}</p>
        <a :href="storeInfo.googleMapsUrl" target="_blank" class="text-primary text-sm font-medium hover:underline">Lihat di Google Maps →</a>
      </div>
      <div class="bg-white rounded-xl2 border border-ink/5 p-5">
        <p class="font-semibold text-ink mb-2">💬 WhatsApp</p>
        <p class="text-ink/70 text-sm mb-3">Chat langsung dengan admin toko kami</p>
        <a :href="storeInfo.whatsappUrl" target="_blank" class="text-success text-sm font-medium hover:underline">Chat Sekarang →</a>
      </div>
      <div class="bg-white rounded-xl2 border border-ink/5 p-5 sm:col-span-2">
        <p class="font-semibold text-ink mb-2">🕒 Jam Operasional</p>
        <p class="text-ink/70 text-sm">
          Setiap hari, {{ storeInfo.operatingHours.buka }} - {{ storeInfo.operatingHours.tutup }} WITA
          <span :class="storeInfo.isOpenNow ? 'text-success' : 'text-danger'" class="font-semibold ml-2">
            ({{ storeInfo.isOpenNow ? 'Buka Sekarang' : 'Tutup' }})
          </span>
        </p>
      </div>
    </div>

    <div class="mt-5 rounded-xl2 overflow-hidden border border-ink/5">
      <iframe :src="storeInfo.googleMapsEmbedUrl" class="w-full h-72" loading="lazy"></iframe>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useStoreInfoStore } from '../../stores/store'

const storeInfo = useStoreInfoStore()
onMounted(() => storeInfo.fetchStoreInfo())
</script>