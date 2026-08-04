<template>
  <div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-ink mb-1">Hubungi Kami</h1>
    <p class="text-ink/55 text-sm mb-7">Kami siap membantu — pilih topik di bawah dan chat langsung dengan admin toko.</p>

    <!-- Info toko -->
    <div class="grid sm:grid-cols-2 gap-4 mb-8">
      <div class="glass-card rounded-xl2 p-5">
        <p class="font-semibold text-ink mb-2 flex items-center gap-1.5">
          <MapPinIcon class="w-5 h-5 text-primary" stroke-width="1.75" /> Alamat Toko
        </p>
        <p class="text-ink/70 text-sm mb-3 leading-relaxed">{{ storeInfo.address }}</p>
        <a :href="storeInfo.googleMapsLink" target="_blank" class="inline-flex items-center gap-1 text-primary text-sm font-semibold hover:underline transition-colors">
          Lihat di Google Maps
          <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" stroke-width="1.75" />
        </a>
      </div>

      <div class="glass-card rounded-xl2 p-5 flex flex-col justify-between">
        <div>
          <p class="font-semibold text-ink mb-2 flex items-center gap-1.5">
            <ClockIcon class="w-5 h-5 text-primary" stroke-width="1.75" /> Jam Operasional
          </p>
          <p class="text-ink/70 text-sm mb-1">
            Setiap hari pukul {{ storeInfo.operatingHours.buka }} – {{ storeInfo.operatingHours.tutup }} WITA
          </p>
          <span
            class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full"
            :class="storeInfo.isOpenNow ? 'bg-success/15 text-success' : 'bg-danger/10 text-danger'"
          >
            <span class="w-1.5 h-1.5 rounded-full" :class="storeInfo.isOpenNow ? 'bg-success' : 'bg-danger'"></span>
            {{ storeInfo.isOpenNow ? 'Buka Sekarang' : 'Tutup' }}
          </span>
        </div>
        <div class="mt-4 pt-4 border-t border-ink/8 text-sm text-ink/50 flex items-center gap-1.5">
          <WhatsAppIcon size="w-4 h-4" />
          <span class="tabular-nums">+62 852-4418-9949</span>
        </div>
      </div>
    </div>

    <!-- Template chat WA -->
    <div class="mb-8">
      <div class="flex items-center gap-3 mb-4">
        <div class="h-px flex-1 bg-ink/10"></div>
        <p class="text-xs font-bold text-ink/40 uppercase tracking-wider">Pilih topik & langsung chat</p>
        <div class="h-px flex-1 bg-ink/10"></div>
      </div>

      <div class="grid gap-3">
        <a
          v-for="tmpl in templates"
          :key="tmpl.id"
          :href="waLink(tmpl.pesan)"
          target="_blank"
          rel="noopener"
          class="group glass-card rounded-xl2 p-4 flex items-start gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer"
        >
          <!-- Ikon -->
          <span class="text-2xl shrink-0 mt-0.5">{{ tmpl.ikon }}</span>

          <!-- Teks -->
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-ink text-sm mb-0.5">{{ tmpl.judul }}</p>
            <p class="text-ink/50 text-xs leading-relaxed line-clamp-2">{{ tmpl.pesan }}</p>
          </div>

          <!-- Tombol kirim -->
          <span
            class="shrink-0 flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full text-white transition group-hover:opacity-90"
            style="background-color: #25D366;"
          >
            <WhatsAppIcon size="w-3.5 h-3.5" class="brightness-0 invert" />
            Kirim
          </span>
        </a>
      </div>

      <p class="text-center text-xs text-ink/35 mt-4">
        Klik salah satu topik → WhatsApp terbuka dengan pesan yang sudah terisi otomatis
      </p>
    </div>

    <!-- Peta -->
    <div class="rounded-xl2 overflow-hidden border border-ink/10 shadow-sm">
      <iframe :src="storeInfo.googleMapsEmbedUrl" class="w-full h-64" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useStoreInfoStore } from '../../stores/store'
import { MapPinIcon, ClockIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline'
import WhatsAppIcon from '../../components/shared/WhatsAppIcon.vue'

const storeInfo = useStoreInfoStore()
onMounted(() => storeInfo.fetchStoreInfo())

const templates = [
  {
    id: 1,
    ikon: '🍊',
    judul: 'Tanya Stok & Harga',
    pesan: 'Halo IPUL BUAH 👋 Saya ingin tanya buah apa saja yang tersedia hari ini beserta harganya. Boleh minta infonya?',
  },
  {
    id: 2,
    ikon: '🛒',
    judul: 'Mau Pesan Buah',
    pesan: 'Halo IPUL BUAH 👋 Saya ingin memesan buah. Boleh bantu proses pesanannya?',
  },
  {
    id: 3,
    ikon: '🎁',
    judul: 'Tanya Parsel / Hampers',
    pesan: 'Halo IPUL BUAH 👋 Saya tertarik memesan parsel buah untuk hadiah. Boleh minta info pilihan isi, kemasan, dan harganya?',
  },
  {
    id: 4,
    ikon: '🚚',
    judul: 'Tanya Pengiriman',
    pesan: 'Halo IPUL BUAH 👋 Apakah ada layanan antar ke rumah? Saya ingin tanya ongkos kirim dan estimasi waktu pengirimannya.',
  },
  {
    id: 5,
    ikon: '📦',
    judul: 'Cek Status Pesanan',
    pesan: 'Halo IPUL BUAH 👋 Saya ingin mengecek status pesanan saya. Boleh minta tolong dibantu?',
  },
  {
    id: 6,
    ikon: '🌙',
    judul: 'Pesan untuk Acara / Event',
    pesan: 'Halo IPUL BUAH 👋 Saya ingin memesan buah dalam jumlah besar untuk acara. Apakah ada harga spesial atau layanan khusus untuk pemesanan acara?',
  },
  {
    id: 7,
    ikon: '💬',
    judul: 'Butuh Bantuan Lainnya',
    pesan: 'Halo IPUL BUAH 👋 Saya butuh bantuan. Boleh minta tolong dibantu?',
  },
]

function waLink(pesan) {
  const nomor = storeInfo.whatsappNumber || '6285244189949'
  return `https://wa.me/${nomor}?text=${encodeURIComponent(pesan)}`
}
</script>
