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
        <p class="font-semibold text-ink mb-2 flex items-center gap-1.5">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-success" fill="currentColor">
            <path d="M21.99 6.547a11 11 0 0 0-.103-1.282a4.3 4.3 0 0 0-.363-1.09A3.85 3.85 0 0 0 19.83 2.48a4.3 4.3 0 0 0-1.083-.362a11 11 0 0 0-1.292-.105c-.183-.007-.42-.01-.53-.01L7.077 2c-.11 0-.347.003-.53.01a11 11 0 0 0-1.282.103a4.3 4.3 0 0 0-1.09.363A3.85 3.85 0 0 0 2.48 4.17a4.3 4.3 0 0 0-.362 1.083a11 11 0 0 0-.106 1.292c-.006.183-.01.42-.01.53L2 16.923c0 .11.003.347.01.53a11 11 0 0 0 .103 1.282a4.3 4.3 0 0 0 .363 1.09A3.85 3.85 0 0 0 4.17 21.52a4.3 4.3 0 0 0 1.083.362a11 11 0 0 0 1.292.105c.183.007.42.01.53.01l9.848.002c.11 0 .347-.003.53-.01a11 11 0 0 0 1.282-.103a4.3 4.3 0 0 0 1.09-.363a3.85 3.85 0 0 0 1.696-1.694a4.3 4.3 0 0 0 .362-1.083a11 11 0 0 0 .106-1.292c.006-.183.01-.42.01-.53L22 7.077c0-.11-.003-.347-.01-.53m-9.773 12.41h-.003a7.1 7.1 0 0 1-3.407-.868l-3.78.991l1.012-3.693a7.13 7.13 0 1 1 6.178 3.57"></path>
            <path d="M12.22 5.901a5.927 5.927 0 0 0-5.023 9.076l.141.224l-.599 2.186l2.243-.588l.216.128a5.9 5.9 0 0 0 3.016.826h.003A5.926 5.926 0 0 0 12.219 5.9Zm3.484 8.47a1.83 1.83 0 0 1-1.202.847a2.44 2.44 0 0 1-1.122-.07a10 10 0 0 1-1.015-.376a7.94 7.94 0 0 1-3.043-2.689a3.46 3.46 0 0 1-.728-1.842a2 2 0 0 1 .624-1.485a.66.66 0 0 1 .475-.223c.118 0 .237 0 .341.006c.11.005.256-.042.4.306c.15.356.506 1.233.55 1.322a.33.33 0 0 1 .015.312a1.2 1.2 0 0 1-.178.297c-.09.104-.187.232-.267.312c-.09.089-.182.185-.079.363a5.4 5.4 0 0 0 .991 1.234a4.9 4.9 0 0 0 1.433.884c.178.09.282.074.386-.045s.445-.52.564-.698s.237-.148.4-.089s1.04.49 1.218.58s.297.133.341.207a1.5 1.5 0 0 1-.104.847"></path>
          </svg>
          WhatsApp
        </p>
        <p class="text-ink/70 text-sm mb-3">Chat langsung dengan admin toko kami</p>
        <a :href="storeInfo.whatsappUrl" target="_blank" class="text-success text-sm font-medium hover:underline">Chat Sekarang →</a>
      </div>
      <div class="bg-white rounded-xl2 border border-ink/5 p-5 sm:col-span-2">
        <p class="font-semibold text-ink mb-2 flex items-center gap-1.5">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="w-5 h-5 shrink-0 text-primary">
            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
              <circle cx="8" cy="8" r="6.25"></circle>
              <path d="m8.25 4.75v3.5l-2.5 2"></path>
            </g>
          </svg>
          Jam Operasional
        </p>
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