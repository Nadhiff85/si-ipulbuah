<template>
  <div class="min-h-screen flex flex-col bg-surface-soft">
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

    <!-- ===== Header / Navbar (Satu Baris & Huruf Navigasi Ekstra Besar) ===== -->
    <header class="bg-white sticky top-0 z-40 shadow-sm border-b border-ink/5">
      <div class="max-w-7xl mx-auto px-4 py-5 flex items-center justify-between gap-4">
        
        <!-- Bagian Kiri: Logo -->
        <router-link to="/" class="flex items-center gap-2 shrink-0">
          <img src="/logo.png" alt="IPUL BUAH" class="h-11 w-11 object-contain" />
          <div class="leading-tight">
            <p class="font-black text-primary text-xl -mb-0.5">IPUL BUAH</p>
            <p class="text-xs text-ink/60">Segar setiap hari</p>
          </div>
        </router-link>

        <!-- Bagian Tengah: Menu Utama Navigasi (HURUF JAUH LEBIH BESAR & TEBAL) -->
        <nav class="hidden md:flex flex-1 justify-center items-center gap-10 text-lg lg:text-xl font-bold">
          <router-link to="/" class="text-ink hover:text-primary transition-colors">Beranda</router-link>
          <button @click="goToProtected('/katalog')" class="text-ink/70 hover:text-primary transition-colors">Katalog</button>
          <button @click="goToProtected('/paket-buah')" class="text-ink/70 hover:text-primary transition-colors">Paket & Hampers</button>
        </nav>

        <!-- Bagian Kanan: Menu Pengguna / Akun (Ikut Diperbesar Menjadi text-base) -->
        <div class="flex items-center gap-6 text-base font-semibold shrink-0">
          <button @click="goToProtected('/wishlist')" class="hidden sm:flex items-center gap-1 text-ink/70 hover:text-primary transition-colors">
            ❤ Wishlist
          </button>
          <button @click="goToProtected('/keranjang')" class="flex items-center gap-1 text-ink/70 hover:text-primary transition-colors">
            🛒 Keranjang
          </button>

          <template v-if="auth.isLoggedIn">
            <router-link to="/akun" class="font-bold text-ink hover:text-primary transition-colors">
              Halo, {{ auth.user?.name?.split(' ')[0] }}
            </router-link>
          </template>
          <template v-else>
            <div class="flex items-center gap-4">
              <router-link to="/login" class="text-ink/70 hover:text-primary transition-colors">Masuk</router-link>
              <router-link
                to="/register"
                class="bg-primary text-white px-5 py-2 rounded-full font-bold hover:bg-primary-dark transition-all"
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
            <img src="/logo.png" alt="IPUL BUAH" class="h-9 w-9 object-contain bg-white rounded-full p-1" />
            <span class="font-bold text-lg">IPUL BUAH</span>
          </div>
          <p class="text-white/70">{{ storeInfo.tagline }}</p>
        </div>

        <div>
          <p class="font-semibold mb-3">Lokasi Toko</p>
          <p class="text-white/70 leading-relaxed">{{ storeInfo.address }}</p>
          <a :href="storeInfo.googleMapsLink" target="_blank" class="inline-block mt-2 text-badge hover:underline">
            📍 Lihat di Google Maps
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
            <li><button @click="goToProtected('/promo')" class="hover:text-badge transition-colors text-left w-full">Promo</button></li>
            <li><button @click="goToProtected('/musiman')" class="hover:text-badge transition-colors text-left w-full">Musiman</button></li>
            <li><router-link to="/tentang-kami" class="hover:text-badge transition-colors block">Tentang Kami</router-link></li>
            <li><router-link to="/faq" class="hover:text-badge transition-colors block">FAQ & Bantuan</router-link></li>
            <li><router-link to="/kontak" class="hover:text-badge transition-colors block">Kontak</router-link></li>
          </ul>
          
          <p class="font-semibold mb-2">Butuh Bantuan?</p>
          <a :href="storeInfo.whatsappUrl" target="_blank" class="inline-flex items-center gap-2 bg-success/20 text-success px-3 py-2 rounded-lg hover:bg-success/30 transition-all">
            💬 Chat WhatsApp
          </a>
        </div>
      </div>
      <div class="border-t border-white/10 py-4 text-center text-xs text-white/50">
        &copy; 2026 IPUL BUAH. Segar Setiap Hari, Sehat untuk Keluarga.
        <span class="mx-2">·</span>
        <router-link to="/staff/login" class="text-white/40 hover:text-white/70 hover:underline">Portal Staff</router-link>
      </div>
    </footer>

    <!-- Tombol WhatsApp mengambang -->
    <a
      :href="storeInfo.whatsappUrl"
      target="_blank"
      class="fixed bottom-5 right-5 z-50 bg-success text-white w-14 h-14 rounded-full flex items-center justify-center text-2xl shadow-lg hover:scale-105 transition"
      title="Chat via WhatsApp"
    >
      💬
    </a>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useStoreInfoStore } from '../stores/store'
import { EyeIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'

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