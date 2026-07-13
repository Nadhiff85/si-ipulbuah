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
          <a :href="storeInfo.googleMapsUrl" target="_blank" class="inline-flex items-center gap-1 mt-2 text-badge hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="currentColor">
              <path d="M16 10c0-2.21-1.79-4-4-4s-4 1.79-4 4s1.79 4 4 4s4-1.79 4-4m-6 0c0-1.1.9-2 2-2s2 .9 2 2s-.9 2-2 2s-2-.9-2-2"></path>
              <path d="M11.42 21.81c.17.12.38.19.58.19s.41-.06.58-.19c.3-.22 7.45-5.37 7.42-11.82c0-4.41-3.59-8-8-8s-8 3.59-8 8c-.03 6.44 7.12 11.6 7.42 11.82M12 4c3.31 0 6 2.69 6 6c.02 4.44-4.39 8.43-6 9.74c-1.61-1.31-6.02-5.29-6-9.74c0-3.31 2.69-6 6-6"></path>
            </svg>
            Lihat di Google Maps
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
            <WhatsAppIcon />
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
      <WhatsAppIcon size="w-14 h-14" />
    </a>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useStoreInfoStore } from '../stores/store'
import WhatsAppIcon from '../components/shared/WhatsAppIcon.vue'

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