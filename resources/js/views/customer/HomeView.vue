<template>
  <div>
    <!-- ===== Hero Banner ===== -->
    <section class="bg-gradient-to-br from-primary/10 via-surface-soft to-accent/10">
      <div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-2 gap-8 items-center">
        <div>
          <span class="inline-block bg-badge/20 text-accent font-semibold px-3 py-1 rounded-full text-xs mb-4">
            100% SEGAR
          </span>
          <h1 class="text-3xl md:text-4xl font-extrabold text-ink leading-tight mb-4">
            Buah Segar Pilihan, Langsung dari Petani Lokal
          </h1>
          <p class="text-ink/60 mb-6">
            Kualitas terbaik untuk keluarga sehat dan bahagia. Melayani Kota Palu,
            Kabupaten Sigi & Kabupaten Donggala.
          </p>
          <div class="flex flex-wrap gap-3 mb-6">
            <span class="trust-badge">✅ 100% Segar</span>
            <span class="trust-badge">🚚 Pengiriman Cepat</span>
            <span class="trust-badge">🔒 Bayar Aman (QRIS)</span>
            <span class="trust-badge">🏅 Garansi Kualitas</span>
          </div>
          <div class="flex gap-3">
            <button @click="goLogin('/katalog')" class="bg-accent hover:bg-accent-light text-white font-semibold px-6 py-3 rounded-full transition">
              Mulai Belanja
            </button>
            <button @click="goLogin('/parsel-kustom')" class="bg-white border border-primary text-primary font-semibold px-6 py-3 rounded-full hover:bg-primary/5 transition">
              🎁 Buat Parsel Kustom
            </button>
          </div>
        </div>
        <div class="relative">
          <div class="aspect-square rounded-xl2 bg-white shadow-lg flex items-center justify-center text-8xl">
            🧺🍊🍇🍎
          </div>
          <span class="absolute -top-3 -right-3 bg-accent text-white text-xs font-bold px-4 py-2 rounded-full shadow">
            Gratis Ongkir*
          </span>
        </div>
      </div>
    </section>

    <!-- ===== Kategori Sekilas ===== -->
    <section class="max-w-7xl mx-auto px-4 py-10">
      <h2 class="text-xl font-bold text-ink mb-5">Jelajahi Kategori</h2>
      <div class="grid grid-cols-3 sm:grid-cols-6 gap-4">
        <button
          v-for="cat in categoriesGlimpse"
          :key="cat.name"
          @click="goLogin('/katalog')"
          class="flex flex-col items-center gap-2 group"
        >
          <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-3xl group-hover:bg-primary/20 transition">
            {{ cat.icon }}
          </div>
          <span class="text-xs text-ink/70">{{ cat.name }}</span>
        </button>
      </div>
    </section>

    <!-- ===== Cuplikan Produk Terlaris (terkunci, ajak login) ===== -->
    <section class="max-w-7xl mx-auto px-4 py-10">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-ink">Produk Terlaris</h2>
        <button @click="goLogin('/katalog')" class="text-primary text-sm font-medium hover:underline">Lihat Semua →</button>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
        <button
          v-for="p in teaserProducts"
          :key="p.name"
          @click="goLogin('/katalog')"
          class="text-left bg-white rounded-xl2 shadow-sm hover:shadow-md transition overflow-hidden"
        >
          <div class="aspect-square bg-primary/5 flex items-center justify-center text-5xl relative">
            {{ p.emoji }}
            <span v-if="p.label" class="absolute top-2 left-2 bg-badge text-ink text-[10px] font-bold px-2 py-0.5 rounded-full">
              {{ p.label }}
            </span>
          </div>
          <div class="p-3">
            <p class="font-medium text-sm text-ink truncate">{{ p.name }}</p>
            <p class="text-xs text-accent font-semibold mt-1 flex items-center gap-1">
              🔒 Masuk untuk lihat harga
            </p>
          </div>
        </button>
      </div>
    </section>

    <!-- ===== Banner Parsel Kustom & Musiman ===== -->
    <section class="max-w-7xl mx-auto px-4 py-4 grid md:grid-cols-2 gap-5">
      <button
        @click="goLogin('/parsel-kustom')"
        class="text-left bg-primary rounded-xl2 p-6 text-white flex items-center justify-between hover:bg-primary-dark transition"
      >
        <div>
          <p class="font-bold text-lg mb-1">Buat Parsel Kustom</p>
          <p class="text-white/80 text-sm">Rangkai sendiri hampers buah sesuai keinginanmu</p>
        </div>
        <span class="text-3xl">🎁</span>
      </button>
      <button
        @click="goLogin('/musiman')"
        class="text-left bg-accent rounded-xl2 p-6 text-white flex items-center justify-between hover:bg-accent-light transition"
      >
        <div>
          <p class="font-bold text-lg mb-1">Buah Musiman</p>
          <p class="text-white/80 text-sm">Segar, enak & sedang musim sekarang!</p>
        </div>
        <span class="text-3xl">🥭</span>
      </button>
    </section>

    <!-- ===== Lokasi Toko ===== -->
    <section class="max-w-7xl mx-auto px-4 py-12">
      <h2 class="text-xl font-bold text-ink mb-1">Lokasi Toko Kami</h2>
      <p class="text-ink/60 text-sm mb-5">Klik peta untuk membuka rute lengkap di Google Maps</p>

      <div class="grid md:grid-cols-3 gap-5">
        <!-- Peta -->
        <a
          :href="storeInfo.googleMapsUrl"
          target="_blank"
          class="md:col-span-2 block rounded-xl2 overflow-hidden shadow-sm border border-ink/5 relative group"
        >
          <iframe
            :src="storeInfo.googleMapsEmbedUrl"
            class="w-full h-72 md:h-80 pointer-events-none"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          ></iframe>
          <div class="absolute inset-0 bg-ink/0 group-hover:bg-ink/10 transition flex items-center justify-center">
            <span class="opacity-0 group-hover:opacity-100 transition bg-white px-4 py-2 rounded-full font-semibold text-primary shadow">
              🗺️ Buka di Google Maps
            </span>
          </div>
        </a>

        <!-- Info Toko -->
        <div class="bg-white rounded-xl2 shadow-sm border border-ink/5 p-5 space-y-4">
          <div>
            <p class="text-xs text-ink/50 mb-1">Status Toko</p>
            <span
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
              :class="storeInfo.isOpenNow ? 'bg-success/20 text-success' : 'bg-danger/10 text-danger'"
            >
              ● {{ storeInfo.isOpenNow ? 'Buka Sekarang' : 'Tutup' }}
            </span>
          </div>
          <div>
            <p class="text-xs text-ink/50 mb-1">Jam Operasional</p>
            <p class="text-sm font-medium text-ink">
              {{ storeInfo.operatingHours.buka }} - {{ storeInfo.operatingHours.tutup }} WITA
            </p>
          </div>
          <div>
            <p class="text-xs text-ink/50 mb-1">Alamat</p>
            <p class="text-sm text-ink leading-relaxed">{{ storeInfo.address }}</p>
          </div>
          <a
            :href="storeInfo.googleMapsUrl"
            target="_blank"
            class="block text-center bg-primary text-white text-sm font-semibold py-2.5 rounded-full hover:bg-primary-dark transition"
          >
            📍 Lihat Rute
          </a>
          <a
            :href="storeInfo.whatsappUrl"
            target="_blank"
            class="block text-center border border-success text-success text-sm font-semibold py-2.5 rounded-full hover:bg-success/10 transition"
          >
            💬 Hubungi via WhatsApp
          </a>
        </div>
      </div>
    </section>

    <!-- ===== CTA Daftar ===== -->
    <section class="max-w-7xl mx-auto px-4 pb-16">
      <div class="bg-primary-dark rounded-xl2 p-8 md:p-10 text-center text-white">
        <h2 class="text-2xl font-bold mb-2">Yuk, Mulai Belanja Buah Segar!</h2>
        <p class="text-white/70 mb-6">Daftar sekarang untuk lihat katalog lengkap, harga, dan mulai pesan dari rumah.</p>
        <div class="flex justify-center gap-3">
          <router-link to="/register" class="bg-accent hover:bg-accent-light text-white font-semibold px-6 py-3 rounded-full transition">
            Daftar Sekarang
          </router-link>
          <router-link to="/login" class="bg-white/10 hover:bg-white/20 text-white font-semibold px-6 py-3 rounded-full transition">
            Sudah Punya Akun? Masuk
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useStoreInfoStore } from '../../stores/store'

const router = useRouter()
const auth = useAuthStore()
const storeInfo = useStoreInfoStore()

onMounted(() => storeInfo.fetchStoreInfo())

// Semua interaksi produk di beranda publik -> arahkan ke login jika belum masuk
function goLogin(path) {
  if (auth.isLoggedIn) {
    router.push(path)
  } else {
    router.push({
      path: '/login',
      query: { redirect: path, reason: 'Masuk atau daftar dulu untuk melihat detail produk dan mulai belanja 🍊' },
    })
  }
}

// Data sekilas (dummy) untuk tampilan beranda publik saja - bukan data transaksional
const categoriesGlimpse = [
  { name: 'Buah Lokal', icon: '🍌' },
  { name: 'Buah Impor', icon: '🍎' },
  { name: 'Musiman', icon: '🥭' },
  { name: 'Paket Buah', icon: '🧺' },
  { name: 'Hampers', icon: '🎁' },
  { name: 'Best Seller', icon: '⭐' },
]

const teaserProducts = [
  { name: 'Jeruk Medan', emoji: '🍊', label: 'Best Seller' },
  { name: 'Anggur Red Globe', emoji: '🍇', label: null },
  { name: 'Durian Montong', emoji: '🥥', label: 'Musiman' },
  { name: 'Mangga Harum Manis', emoji: '🥭', label: null },
  { name: 'Apel Fuji', emoji: '🍎', label: null },
  { name: 'Paket Hemat Family', emoji: '🧺', label: 'Promo' },
]
</script>

<style scoped>
.trust-badge {
  @apply bg-white text-ink/70 text-xs font-medium px-3 py-1.5 rounded-full border border-ink/10;
}
</style>
