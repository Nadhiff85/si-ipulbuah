<template>
  <div class="min-h-screen flex flex-col glass-panel-bg">
    <!-- Banner Mode Pratinjau - hanya tampil untuk staff yang sedang cek tampilan toko -->
    <div v-if="auth.isStaff && auth.previewMode" class="bg-ink text-white text-sm px-4 py-2.5 flex items-center justify-between sticky top-0 z-50">
      <span class="flex items-center gap-2">
        <EyeIcon class="w-4 h-4" stroke-width="1.75" />
        Mode Pratinjau — Anda sedang melihat tampilan seperti pelanggan
      </span>
      <button
        @click="exitPreview"
        class="flex items-center gap-1.5 bg-white/15 hover:bg-white/25 px-3 py-1 rounded-full text-xs font-medium transition"
      >
        <ArrowLeftIcon class="w-3.5 h-3.5" stroke-width="1.75" /> Kembali ke Dashboard
      </button>
    </div>

    <!-- ===== Header / Navbar (ringkas & rapi) ===== -->
    <header class="bg-white sticky top-0 z-40 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">

        <!-- Bagian Kiri: Logo -->
        <router-link to="/" class="flex items-center gap-2 shrink-0">
          <span class="h-9 w-9 rounded-xl bg-accent text-white font-black text-sm flex items-center justify-center shrink-0">IB</span>
          <p class="font-black text-primary text-lg leading-none">IPUL BUAH</p>
        </router-link>

        <!-- Bagian Tengah: Menu Utama Navigasi -->
        <nav class="hidden md:flex flex-1 justify-center items-center gap-8 text-sm font-semibold">
          <router-link to="/" class="text-ink hover:text-accent transition-colors">Beranda</router-link>
          <button @click="goToProtected('/katalog')" class="text-ink/70 hover:text-accent transition-colors cursor-pointer">Katalog</button>
          <button @click="goToProtected('/paket-buah')" class="text-ink/70 hover:text-accent transition-colors cursor-pointer">Paket & Hampers</button>
        </nav>

        <!-- Bagian Kanan: Menu Pengguna / Akun -->
        <div class="flex items-center gap-4 text-sm font-medium shrink-0">
          <button @click="goToProtected('/wishlist')" class="hidden sm:flex items-center justify-center text-ink/70 hover:text-accent transition-colors cursor-pointer" title="Wishlist">
            <HeartIcon class="w-6 h-6" stroke-width="1.75" />
          </button>
          <button @click="goToProtected('/keranjang')" class="flex items-center justify-center text-ink/70 hover:text-accent transition-colors cursor-pointer" title="Keranjang">
            <ShoppingCartIcon class="w-6 h-6" stroke-width="1.75" />
          </button>

          <template v-if="auth.isLoggedIn">
            <router-link to="/akun" class="font-semibold text-ink hover:text-accent transition-colors">
              Halo, {{ auth.user?.name?.split(' ')[0] }}
            </router-link>
          </template>
          <template v-else>
            <div class="flex items-center gap-3">
              <router-link to="/login" class="text-ink/70 hover:text-accent transition-colors">Masuk</router-link>
              <router-link
                to="/register"
                class="bg-primary text-white px-4 py-1.5 rounded-full font-semibold hover:bg-primary-dark transition-all"
              >
                Daftar
              </router-link>
            </div>
          </template>
        </div>

      </div>
    </header>

    <!-- ===== Konten Halaman ===== -->
    <main class="flex-1">
      <router-view />
    </main>

    <!-- ===== Footer ===== -->
    <footer class="bg-primary-dark text-white mt-12">
      <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-4 gap-8 text-sm">
        <div>
          <div class="flex items-center gap-2 mb-3">
            <span class="h-9 w-9 rounded-xl bg-badge text-primary-dark font-black text-sm flex items-center justify-center shrink-0">IB</span>
            <span class="font-bold text-lg">IPUL BUAH</span>
          </div>
          <p class="text-white/70">{{ storeInfo.tagline }}</p>
        </div>

        <div>
          <p class="font-semibold mb-3">Lokasi Toko</p>
          <p class="text-white/70 leading-relaxed">{{ storeInfo.address }}</p>
          <a :href="storeInfo.googleMapsLink" target="_blank" class="inline-flex items-center gap-1.5 mt-2 text-badge hover:underline cursor-pointer">
            <MapPinIcon class="w-4 h-4" stroke-width="1.75" /> Lihat di Google Maps
          </a>
        </div>

        <div>
          <p class="font-semibold mb-3">Wilayah Pengiriman</p>
          <ul class="text-white/70 space-y-1">
            <li v-for="region in storeInfo.deliveryRegions" :key="region">✓ {{ region }}</li>
          </ul>
        </div>

        <div>
          <p class="font-semibold mb-3">Layanan & Informasi</p>
          <ul class="space-y-2 mb-4 text-white/70">
            <li><button @click="goToProtected('/promo')" class="hover:text-badge transition-colors text-left w-full cursor-pointer">Promo</button></li>
            <li><button @click="goToProtected('/musiman')" class="hover:text-badge transition-colors text-left w-full cursor-pointer">Musiman</button></li>
            <li><router-link to="/tentang-kami" class="hover:text-badge transition-colors block">Tentang Kami</router-link></li>
            <li><router-link to="/faq" class="hover:text-badge transition-colors block">FAQ & Bantuan</router-link></li>
            <li><router-link to="/kontak" class="hover:text-badge transition-colors block">Kontak</router-link></li>
          </ul>
          
          <p class="font-semibold mb-2">Butuh Bantuan?</p>
          <a :href="storeInfo.whatsappUrl" target="_blank" class="inline-flex items-center gap-2 bg-white/10 text-white px-3 py-2 rounded-lg hover:bg-white/20 transition-all cursor-pointer">
            <WhatsAppIcon size="w-4 h-4" /> Chat WhatsApp
          </a>
        </div>
      </div>
      <div class="border-t border-white/10 py-4 text-center text-xs text-white/50">
        &copy; 2026 IPUL BUAH. Segar Setiap Hari, Sehat untuk Keluarga.
        <span class="mx-2">·</span>
        <router-link to="/staff/login" class="text-white/40 hover:text-white/70 hover:underline">Portal Staff</router-link>
      </div>
    </footer>

    <!-- Tombol Bantuan WhatsApp mengambang -->
    <div class="fixed bottom-6 right-5 z-50 flex items-center gap-3 group">
      <!-- Label muncul saat hover -->
      <div class="bg-white text-ink text-sm font-bold px-4 py-2 rounded-full shadow-lg opacity-0 translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-200 pointer-events-none whitespace-nowrap border border-ink/8">
        💬 Butuh bantuan?
      </div>
      <!-- Tombol WA -->
      <a
        :href="storeInfo.whatsappUrl"
        target="_blank"
        rel="noopener"
        class="relative w-14 h-14 rounded-full flex items-center justify-center shadow-xl hover:scale-110 active:scale-95 transition-transform cursor-pointer"
        style="background-color: #25D366;"
        title="Chat WhatsApp"
      >
        <!-- Ring pulse -->
        <span class="absolute inset-0 rounded-full animate-ping opacity-30" style="background-color: #25D366;"></span>
        <WhatsAppIcon size="w-7 h-7" class="brightness-0 invert relative z-10" />
      </a>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useStoreInfoStore } from '../stores/store'
import { EyeIcon, ArrowLeftIcon, HeartIcon, ShoppingCartIcon, MapPinIcon } from '@heroicons/vue/24/outline'
import WhatsAppIcon from '../components/shared/WhatsAppIcon.vue'

const router = useRouter()
const auth = useAuthStore()
const storeInfo = useStoreInfoStore()

onMounted(() => {
  storeInfo.fetchStoreInfo()
})

function goToProtected(path) {
  if (auth.isLoggedIn) {
    router.push(path)
  } else {
    router.push({
      path: '/login',
      query: { redirect: path, reason: 'Masuk atau daftar dulu untuk melihat detail produk dan mulai belanja 🍊' },
    })
  }
}

function exitPreview() {
  auth.disablePreview()
  router.push(auth.user.role === 'superadmin' ? '/superadmin' : '/admin')
}
</script>