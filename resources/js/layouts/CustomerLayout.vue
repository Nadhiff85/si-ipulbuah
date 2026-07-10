<template>
  <div class="min-h-screen flex flex-col bg-surface-soft">
    <!-- ===== Header ===== -->
    <header class="bg-white sticky top-0 z-40 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">
        <router-link to="/" class="flex items-center gap-2 shrink-0">
          <img src="/logo.png" alt="IPUL BUAH" class="h-11 w-11 object-contain" />
          <div class="leading-tight">
            <p class="font-extrabold text-primary text-lg -mb-1">IPUL BUAH</p>
            <p class="text-[11px] text-ink/60">Segar setiap hari</p>
          </div>
        </router-link>

        <div class="flex-1 hidden md:block">
          <button
            @click="goToProtected('/katalog')"
            class="w-full text-left px-4 py-2.5 rounded-full border border-ink/10 text-ink/40 hover:border-primary/40 transition"
          >
            🔍 Cari buah favoritmu...
          </button>
        </div>

        <nav class="flex items-center gap-4 text-sm">
          <button @click="goToProtected('/wishlist')" class="hidden sm:flex items-center gap-1 text-ink/70 hover:text-primary">
            ❤ Wishlist
          </button>
          <button @click="goToProtected('/keranjang')" class="flex items-center gap-1 text-ink/70 hover:text-primary">
            🛒 Keranjang
          </button>

          <template v-if="auth.isLoggedIn">
            <router-link to="/akun" class="font-medium text-ink hover:text-primary">Halo, {{ auth.user?.name?.split(' ')[0] }}</router-link>
          </template>
          <template v-else>
            <router-link to="/login" class="text-ink/70 hover:text-primary">Masuk</router-link>
            <router-link
              to="/register"
              class="bg-primary text-white px-4 py-2 rounded-full font-medium hover:bg-primary-dark transition"
            >
              Daftar
            </router-link>
          </template>
        </nav>
      </div>

      <div class="border-t border-ink/5">
        <div class="max-w-7xl mx-auto px-4 flex gap-6 text-sm py-2 overflow-x-auto">
          <router-link to="/" class="whitespace-nowrap font-medium text-primary">Beranda</router-link>
          <button @click="goToProtected('/katalog')" class="whitespace-nowrap text-ink/70 hover:text-primary">Katalog</button>
          <button @click="goToProtected('/paket-buah')" class="whitespace-nowrap text-ink/70 hover:text-primary">Paket & Hampers</button>
          <button @click="goToProtected('/promo')" class="whitespace-nowrap text-ink/70 hover:text-primary">Promo</button>
          <button @click="goToProtected('/musiman')" class="whitespace-nowrap text-ink/70 hover:text-primary">Musiman</button>
          <router-link to="/tentang-kami" class="whitespace-nowrap text-ink/70 hover:text-primary">Tentang Kami</router-link>
          <router-link to="/faq" class="whitespace-nowrap text-ink/70 hover:text-primary">FAQ & Bantuan</router-link>
          <router-link to="/kontak" class="whitespace-nowrap text-ink/70 hover:text-primary">Kontak</router-link>
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
          <a :href="storeInfo.googleMapsUrl" target="_blank" class="inline-block mt-2 text-badge hover:underline">
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
          <p class="font-semibold mb-3">Butuh Bantuan?</p>
          <a :href="storeInfo.whatsappUrl" target="_blank" class="inline-flex items-center gap-2 bg-success/20 text-success px-3 py-2 rounded-lg hover:bg-success/30">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="w-5 h-5 shrink-0">
              <circle cx="16" cy="16" r="16" fill="#25D366"></circle>
              <path fill="#FFFFFF" d="M23.47 8.52A9.8 9.8 0 0 0 16.06 5.5c-5.42 0-9.83 4.4-9.84 9.82a9.8 9.8 0 0 0 1.31 4.92L6.1 26.5l6.4-1.68a9.85 9.85 0 0 0 4.7 1.2h.01c5.42 0 9.83-4.4 9.84-9.82a9.76 9.76 0 0 0-2.88-6.92zm-7.41 15.1h-.01a8.17 8.17 0 0 1-4.17-1.14l-.3-.18-3.1.81.83-3.02-.2-.31a8.17 8.17 0 0 1-1.26-4.36c0-4.52 3.68-8.19 8.2-8.19a8.15 8.15 0 0 1 5.8 2.4a8.14 8.14 0 0 1 2.4 5.8c0 4.52-3.68 8.19-8.2 8.19zm4.49-6.14c-.25-.12-1.45-.72-1.68-.8s-.39-.12-.56.13c-.16.24-.64.8-.79.97s-.29.19-.54.06a6.7 6.7 0 0 1-1.97-1.22a7.4 7.4 0 0 1-1.36-1.7c-.14-.25-.02-.38.11-.5c.11-.11.25-.29.37-.44c.12-.14.16-.25.24-.41c.08-.17.04-.31-.02-.44s-.56-1.35-.76-1.85c-.2-.48-.41-.42-.56-.42h-.48a.9.9 0 0 0-.66.31a2.75 2.75 0 0 0-.86 2.04c0 1.2.87 2.37 1 2.53c.12.17 1.71 2.62 4.15 3.67c.58.25 1.03.4 1.38.51c.58.19 1.11.16 1.53.1c.47-.07 1.45-.59 1.65-1.16s.2-1.06.14-1.16s-.23-.16-.48-.28z"></path>
            </svg>
            Chat WhatsApp
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
      class="fixed bottom-5 right-5 z-50 w-14 h-14 flex items-center justify-center hover:scale-105 transition drop-shadow-lg"
      title="Chat via WhatsApp"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="w-14 h-14">
        <circle cx="16" cy="16" r="16" fill="#25D366"></circle>
        <path fill="#FFFFFF" d="M23.47 8.52A9.8 9.8 0 0 0 16.06 5.5c-5.42 0-9.83 4.4-9.84 9.82a9.8 9.8 0 0 0 1.31 4.92L6.1 26.5l6.4-1.68a9.85 9.85 0 0 0 4.7 1.2h.01c5.42 0 9.83-4.4 9.84-9.82a9.76 9.76 0 0 0-2.88-6.92zm-7.41 15.1h-.01a8.17 8.17 0 0 1-4.17-1.14l-.3-.18-3.1.81.83-3.02-.2-.31a8.17 8.17 0 0 1-1.26-4.36c0-4.52 3.68-8.19 8.2-8.19a8.15 8.15 0 0 1 5.8 2.4a8.14 8.14 0 0 1 2.4 5.8c0 4.52-3.68 8.19-8.2 8.19zm4.49-6.14c-.25-.12-1.45-.72-1.68-.8s-.39-.12-.56.13c-.16.24-.64.8-.79.97s-.29.19-.54.06a6.7 6.7 0 0 1-1.97-1.22a7.4 7.4 0 0 1-1.36-1.7c-.14-.25-.02-.38.11-.5c.11-.11.25-.29.37-.44c.12-.14.16-.25.24-.41c.08-.17.04-.31-.02-.44s-.56-1.35-.76-1.85c-.2-.48-.41-.42-.56-.42h-.48a.9.9 0 0 0-.66.31a2.75 2.75 0 0 0-.86 2.04c0 1.2.87 2.37 1 2.53c.12.17 1.71 2.62 4.15 3.67c.58.25 1.03.4 1.38.51c.58.19 1.11.16 1.53.1c.47-.07 1.45-.59 1.65-1.16s.2-1.06.14-1.16s-.23-.16-.48-.28z"></path>
      </svg>
    </a>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useStoreInfoStore } from '../stores/store'

const router = useRouter()
const auth = useAuthStore()
const storeInfo = useStoreInfoStore()

onMounted(() => {
  storeInfo.fetchStoreInfo()
})

// Fitur/menu yang butuh login akan diarahkan ke halaman Login dengan pesan alasan
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
</script>