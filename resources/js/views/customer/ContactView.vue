<template>
  <div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-ink mb-6">Hubungi Kami</h1>

    <div class="grid sm:grid-cols-2 gap-5">
      <div class="glass-card rounded-xl2 p-5">
        <p class="font-semibold text-ink mb-2 flex items-center gap-1.5">
          <MapPinIcon class="w-5 h-5 text-primary" stroke-width="1.75" /> Alamat Toko
        </p>
        <p class="text-ink/70 text-sm mb-3">{{ storeInfo.address }}</p>
        <a :href="storeInfo.googleMapsLink" target="_blank" class="inline-flex items-center gap-1 text-primary text-sm font-medium hover:underline transition-colors cursor-pointer">
          Lihat di Google Maps
          <ArrowRightIcon class="w-3.5 h-3.5" stroke-width="1.75" />
        </a>
      </div>
      <div class="glass-card rounded-xl2 p-5">
        <p class="font-semibold text-ink mb-2 flex items-center gap-1.5">
          <WhatsAppIcon size="w-5 h-5" /> WhatsApp
        </p>
        <p class="text-ink/70 text-sm mb-3">Chat langsung dengan admin toko kami</p>
        <a :href="storeInfo.whatsappUrl" target="_blank" class="inline-flex items-center gap-1 text-success text-sm font-medium hover:underline transition-colors cursor-pointer">
          Chat Sekarang
          <ArrowRightIcon class="w-3.5 h-3.5" stroke-width="1.75" />
        </a>
      </div>
      <div class="glass-card rounded-xl2 p-5 sm:col-span-2">
        <p class="font-semibold text-ink mb-2 flex items-center gap-1.5">
          <ClockIcon class="w-5 h-5 text-primary" stroke-width="1.75" /> Jam Operasional
        </p>
        <p class="text-ink/70 text-sm">
          Setiap hari, {{ storeInfo.operatingHours.buka }} - {{ storeInfo.operatingHours.tutup }} WITA
          <span :class="storeInfo.isOpenNow ? 'text-success' : 'text-danger'" class="font-semibold ml-2">
            ({{ storeInfo.isOpenNow ? 'Buka Sekarang' : 'Tutup' }})
          </span>
        </p>
      </div>
    </div>

    <div class="mt-5 rounded-xl2 overflow-hidden border border-ink/10 shadow-sm">
      <iframe :src="storeInfo.googleMapsEmbedUrl" class="w-full h-72" loading="lazy"></iframe>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useStoreInfoStore } from '../../stores/store'
import { MapPinIcon, ClockIcon, ArrowRightIcon } from '@heroicons/vue/24/outline'
import WhatsAppIcon from '../../components/shared/WhatsAppIcon.vue'

const storeInfo = useStoreInfoStore()
onMounted(() => storeInfo.fetchStoreInfo())
</script>