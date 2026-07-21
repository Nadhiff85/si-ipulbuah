<template>
  <div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-ink mb-4">Tentang IPUL BUAH</h1>
    <!-- Konten dari Admin (rich text, fitur B.11) jika sudah diisi -->
    <div v-if="storeInfo.aboutContent" class="prose-content text-ink/70 leading-relaxed mb-8" v-html="storeInfo.aboutContent"></div>

    <!-- Fallback teks default jika Admin belum mengisi konten -->
    <template v-else>
      <p class="text-ink/70 leading-relaxed mb-4">
        IPUL BUAH adalah toko buah segar yang berdiri untuk melayani kebutuhan buah-buahan berkualitas
        bagi keluarga di Kota Palu, Kabupaten Sigi, dan Kabupaten Donggala. Kami menyediakan buah lokal
        maupun impor pilihan, serta layanan pembuatan parsel buah kustom untuk berbagai acara spesial Anda.
      </p>
      <p class="text-ink/70 leading-relaxed mb-8">
        "{{ storeInfo.tagline }}" adalah komitmen kami — menghadirkan buah segar setiap hari, langsung dari
        petani lokal terbaik, agar keluarga Anda selalu sehat dan bahagia.
      </p>
    </template>

    <div class="grid sm:grid-cols-3 gap-4 mb-8">
      <div class="glass-card rounded-xl2 p-5 text-center">
        <SparklesIcon class="w-8 h-8 mx-auto mb-2 text-primary" stroke-width="1.5" />
        <p class="font-semibold text-sm">100% Segar</p>
      </div>
      <div class="glass-card rounded-xl2 p-5 text-center">
        <TruckIcon class="w-8 h-8 mx-auto mb-2 text-primary" stroke-width="1.5" />
        <p class="font-semibold text-sm">Pengiriman Cepat</p>
      </div>
      <div class="glass-card rounded-xl2 p-5 text-center">
        <LockClosedIcon class="w-8 h-8 mx-auto mb-2 text-primary" stroke-width="1.5" />
        <p class="font-semibold text-sm">Bayar Aman (QRIS)</p>
      </div>
    </div>

    <div class="glass-card-soft bg-primary/5 rounded-xl2 p-5">
      <p class="font-semibold text-ink mb-1 flex items-center gap-1.5">
        <MapPinIcon class="w-5 h-5 text-primary" stroke-width="1.75" /> Lokasi Toko
      </p>
      <p class="text-ink/70 text-sm mb-2">{{ storeInfo.address }}</p>
      <a :href="storeInfo.googleMapsLink" target="_blank" class="inline-flex items-center gap-1 text-primary text-sm font-medium hover:underline transition-colors cursor-pointer">
        Lihat di Google Maps
        <ArrowRightIcon class="w-3.5 h-3.5" stroke-width="1.75" />
      </a>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useStoreInfoStore } from '../../stores/store'
import { SparklesIcon, TruckIcon, LockClosedIcon, MapPinIcon, ArrowRightIcon } from '@heroicons/vue/24/outline'

const storeInfo = useStoreInfoStore()
onMounted(() => storeInfo.fetchStoreInfo())
</script>

<style scoped>
.prose-content :deep(h1),
.prose-content :deep(h2),
.prose-content :deep(h3) {
  @apply font-bold text-ink mt-4 mb-2;
}
.prose-content :deep(p) { @apply mb-3; }
.prose-content :deep(ul) { @apply list-disc pl-5 mb-3; }
.prose-content :deep(a) { @apply text-primary underline; }
</style>