<template>
  <div>
    <!-- ===== Hero Banner ===== -->
    <section class="relative overflow-hidden bg-surface-soft">
      <span class="absolute top-5 right-5 z-20 bg-accent text-white text-xs font-bold px-4 py-2 rounded-full shadow">
        Gratis Ongkir*
      </span>

      <!-- Background 1 foto saja - lebih sederhana & gampang dipastikan tampil.
           Taruh foto Anda di: public/images/hero-bg.jpeg (nama file harus persis ini) -->
      <div
        class="absolute inset-0 bg-cover bg-center"
        :style="{ backgroundImage: `url(/images/hero-bg.jpeg)` }"
      ></div>

      <!-- Overlay gradasi HANYA di sisi kiri (area teks) supaya foto di kanan tetap jelas terlihat -->
      <div class="absolute inset-0 bg-gradient-to-r from-surface-soft via-surface-soft/85 to-transparent"></div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 py-16 md:py-20">
        <div class="max-w-xl">
          <span class="inline-block bg-badge/20 text-accent font-semibold px-3 py-1 rounded-full text-xs mb-4">
            100% SEGAR
          </span>
          <h1 class="text-3xl md:text-4xl font-extrabold text-ink leading-tight mb-4">
            <span class="text-primary">Buah Segar Pilihan</span><br />
            Langsung dari Petani Lokal
          </h1>
          <p class="text-ink/60 mb-6">
            Kualitas terbaik untuk keluarga sehat dan bahagia. Melayani Kota Palu,
            Kabupaten Sigi & Kabupaten Donggala.
          </p>
          <div class="flex flex-wrap gap-3 mb-6">
            <span class="trust-badge"><CheckCircleIcon class="w-3.5 h-3.5" stroke-width="2" /> 100% Segar</span>
            <span class="trust-badge"><TruckIcon class="w-3.5 h-3.5" stroke-width="2" /> Pengiriman Cepat</span>
            <span class="trust-badge"><LockClosedIcon class="w-3.5 h-3.5" stroke-width="2" /> Bayar Aman (QRIS)</span>
            <span class="trust-badge"><ShieldCheckIcon class="w-3.5 h-3.5" stroke-width="2" /> Garansi Kualitas</span>
          </div>
          <div class="flex gap-3">
            <button @click="goLogin('/katalog')" class="bg-accent hover:bg-accent-light text-white font-semibold px-6 py-3 rounded-full transition">
              Mulai Belanja
            </button>
            <button @click="goLogin('/parsel-kustom')" class="bg-white border border-primary text-primary font-semibold px-6 py-3 rounded-full hover:bg-primary/5 transition inline-flex items-center gap-2">
              <GiftIcon class="w-5 h-5" stroke-width="1.75" /> Buat Parsel Kustom
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== Kategori (data asli dari database) ===== -->
    <section class="max-w-7xl mx-auto px-4 py-10">
      <h2 class="text-xl font-bold text-ink mb-5">Jelajahi Kategori</h2>

      <div v-if="loadingCategories" class="grid grid-cols-3 sm:grid-cols-6 gap-4">
        <div v-for="i in 6" :key="i" class="flex flex-col items-center gap-2 animate-pulse">
          <div class="w-16 h-16 rounded-full bg-ink/10"></div>
          <div class="h-3 w-12 bg-ink/10 rounded"></div>
        </div>
      </div>

      <p v-else-if="categories.length === 0" class="text-ink/40 text-sm">Belum ada kategori tersedia.</p>

      <div v-else class="grid grid-cols-3 sm:grid-cols-6 gap-4">
        <button
          v-for="cat in categories"
          :key="cat.id"
          @click="goLogin('/katalog')"
          class="flex flex-col items-center gap-2 group"
        >
          <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center overflow-hidden group-hover:bg-primary/20 transition">
            <img v-if="cat.image" :src="cat.image" :alt="cat.name" class="w-full h-full object-cover" />
            <TagIcon v-else class="w-6 h-6 text-primary" stroke-width="1.5" />
          </div>
          <span class="text-xs text-ink/70">{{ cat.name }}</span>
        </button>
      </div>
    </section>

    <!-- ===== Produk Terlaris (data asli, dikurasi Admin/Superadmin via is_featured) ===== -->
    <section class="max-w-7xl mx-auto px-4 py-10">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-ink">Produk Terlaris</h2>
        <button @click="goLogin('/katalog')" class="text-primary text-sm font-medium hover:underline">Lihat Semua →</button>
      </div>

      <div v-if="loadingFeatured" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
        <div v-for="i in 6" :key="i" class="bg-white rounded-xl2 overflow-hidden animate-pulse">
          <div class="aspect-square bg-ink/10"></div>
          <div class="p-3 space-y-2">
            <div class="h-3 bg-ink/10 rounded w-3/4"></div>
            <div class="h-3 bg-ink/10 rounded w-1/2"></div>
          </div>
        </div>
      </div>

      <p v-else-if="featuredProducts.length === 0" class="text-ink/40 text-sm">Belum ada produk terlaris yang dikurasi.</p>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
        <button
          v-for="p in featuredProducts"
          :key="p.id"
          @click="goLogin(`/produk/${p.slug}`)"
          class="text-left bg-white rounded-xl2 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all overflow-hidden"
        >
          <div class="aspect-square bg-primary/5 flex items-center justify-center relative overflow-hidden">
            <img v-if="p.images?.[0]?.image_path" :src="p.images[0].image_path" :alt="p.name" class="w-full h-full object-cover" />
            <PhotoIcon v-else class="w-8 h-8 text-ink/20" stroke-width="1.5" />
            <span v-if="p.labels?.[0]" class="absolute top-2 left-2 bg-badge text-ink text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
              {{ labelText(p.labels[0]) }}
            </span>
          </div>
          <div class="p-3">
            <p class="font-medium text-sm text-ink truncate">{{ p.name }}</p>
            <p class="text-accent font-bold text-sm mt-1">Rp {{ formatPrice(p.price_unit) }}<span class="text-ink/40 font-normal">/{{ p.unit }}</span></p>
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
        <span><GiftIcon class="w-9 h-9" stroke-width="1.5" /></span>
      </button>
      <button
        @click="goLogin('/musiman')"
        class="text-left bg-accent rounded-xl2 p-6 text-white flex items-center justify-between hover:bg-accent-light transition"
      >
        <div>
          <p class="font-bold text-lg mb-1">Buah Musiman</p>
          <p class="text-white/80 text-sm">Segar, enak & sedang musim sekarang!</p>
        </div>
        <span><SunIcon class="w-9 h-9" stroke-width="1.5" /></span>
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
            <span class="opacity-0 group-hover:opacity-100 transition bg-white px-4 py-2 rounded-full font-semibold text-primary shadow inline-flex items-center gap-1.5">
              <MapIcon class="w-4 h-4" stroke-width="1.75" /> Buka di Google Maps
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
            class="flex items-center justify-center gap-1.5 text-center bg-primary text-white text-sm font-semibold py-2.5 rounded-full hover:bg-primary-dark transition"
          >
            <MapPinIcon class="w-4 h-4" stroke-width="1.75" /> Lihat Rute
          </a>
          <a
            :href="storeInfo.whatsappUrl"
            target="_blank"
            class="flex items-center justify-center gap-1.5 text-center border border-success text-success text-sm font-semibold py-2.5 rounded-full hover:bg-success/10 transition"
          >
            <ChatBubbleLeftRightIcon class="w-4 h-4" stroke-width="1.75" /> Hubungi via WhatsApp
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useStoreInfoStore } from '../../stores/store'
import api from '../../services/api'
import {
  PhotoIcon, TagIcon, GiftIcon, SunIcon, CheckCircleIcon, TruckIcon,
  LockClosedIcon, ShieldCheckIcon, MapIcon, MapPinIcon, ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline'


const router = useRouter()
const auth = useAuthStore()
const storeInfo = useStoreInfoStore()

onMounted(() => {
  storeInfo.fetchStoreInfo()
  fetchFeaturedProducts()
  fetchCategories()
})

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
// Kategori diambil dari API publik (data asli dari Manajemen Kategori Admin)
const categories = ref([])
const loadingCategories = ref(true)

async function fetchCategories() {
  loadingCategories.value = true
  try {
    const { data } = await api.get('/categories/glimpse')
    categories.value = data.categories
  } finally {
    loadingCategories.value = false
  }
}

// Produk Terlaris diambil dari API publik, dikurasi Admin/Superadmin lewat toggle is_featured
const featuredProducts = ref([])
const loadingFeatured = ref(true)

async function fetchFeaturedProducts() {
  loadingFeatured.value = true
  try {
    const { data } = await api.get('/products/featured')
    featuredProducts.value = data.products
  } finally {
    loadingFeatured.value = false
  }
}

function labelText(label) {
  return { segar: 'Segar', best_seller: 'Best Seller', musiman: 'Musiman', promo: 'Promo' }[label] || label
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}
</script>

<style scoped>
.trust-badge {
  @apply bg-white text-ink/70 text-xs font-medium px-3 py-1.5 rounded-full border border-ink/10 inline-flex items-center gap-1.5;
}
</style>