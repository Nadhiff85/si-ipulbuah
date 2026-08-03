<template>
  <div class="min-h-screen flex flex-col glass-panel-bg">
    <!-- Banner Mode Pratinjau -->
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

    <!-- ===== Header / Navbar ===== -->
    <header class="bg-white sticky top-0 z-40 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">

        <!-- Kiri: Hamburger (mobile) + Logo -->
        <div class="flex items-center gap-2.5 shrink-0">
          <button
            @click="mobileMenuOpen = true"
            class="md:hidden flex items-center justify-center w-9 h-9 rounded-full hover:bg-ink/5 text-ink/70 transition -ml-1"
            aria-label="Menu navigasi"
          >
            <Bars3Icon class="w-5 h-5" stroke-width="1.75" />
          </button>
          <router-link to="/" class="flex items-center gap-2 shrink-0">
            <img src="/images/logo.webp" alt="IPUL BUAH" class="h-9 w-9 object-contain shrink-0" />
            <p class="font-black text-primary text-lg leading-none hidden xs:block">IPUL BUAH</p>
          </router-link>
        </div>

        <!-- Tengah: Desktop nav -->
        <nav class="hidden md:flex flex-1 justify-center items-center gap-8 text-sm font-semibold">
          <router-link to="/" class="text-ink hover:text-accent transition-colors">Beranda</router-link>
          <button @click="goToProtected('/katalog')" class="text-ink/70 hover:text-accent transition-colors cursor-pointer">Katalog</button>
          <button @click="goToProtected('/paket-buah')" class="text-ink/70 hover:text-accent transition-colors cursor-pointer">Paket & Hampers</button>
        </nav>

        <!-- Kanan: Icon & Auth -->
        <div class="flex items-center gap-2 shrink-0">
          <!-- Wishlist - desktop only -->
          <button
            @click="goToProtected('/wishlist')"
            class="hidden md:flex items-center justify-center w-9 h-9 rounded-full hover:bg-ink/5 text-ink/70 hover:text-accent transition-colors cursor-pointer"
            title="Wishlist"
          >
            <HeartIcon class="w-6 h-6" stroke-width="1.75" />
          </button>

          <!-- Keranjang - desktop only (mobile pakai bottom nav) -->
          <button
            @click="goToProtected('/keranjang')"
            class="hidden md:flex items-center justify-center w-9 h-9 rounded-full hover:bg-ink/5 text-ink/70 hover:text-accent transition-colors cursor-pointer relative"
            title="Keranjang"
          >
            <ShoppingCartIcon class="w-6 h-6" stroke-width="1.75" />
            <span
              v-if="cart.itemCount > 0"
              class="absolute top-0.5 right-0.5 bg-accent text-white text-[9px] font-black min-w-[16px] h-4 rounded-full flex items-center justify-center px-0.5 leading-none"
            >{{ cart.itemCount > 9 ? '9+' : cart.itemCount }}</span>
          </button>

          <!-- Keranjang - mobile saja, badge visible -->
          <button
            @click="goToProtected('/keranjang')"
            class="md:hidden flex items-center justify-center w-9 h-9 rounded-full hover:bg-ink/5 text-ink/70 transition-colors cursor-pointer relative"
            title="Keranjang"
          >
            <ShoppingCartIcon class="w-6 h-6" stroke-width="1.75" />
            <span
              v-if="cart.itemCount > 0"
              class="absolute top-0.5 right-0.5 bg-accent text-white text-[9px] font-black min-w-[16px] h-4 rounded-full flex items-center justify-center px-0.5 leading-none"
            >{{ cart.itemCount > 9 ? '9+' : cart.itemCount }}</span>
          </button>

          <!-- Auth desktop -->
          <template v-if="auth.isLoggedIn">
            <router-link to="/akun" class="hidden md:block font-semibold text-ink hover:text-accent transition-colors text-sm ml-1">
              Halo, {{ auth.user?.name?.split(' ')[0] }}
            </router-link>
          </template>
          <template v-else>
            <div class="hidden md:flex items-center gap-3 ml-1">
              <router-link to="/login" class="text-ink/70 hover:text-accent transition-colors text-sm">Masuk</router-link>
              <router-link
                to="/register"
                class="bg-primary text-white px-4 py-1.5 rounded-full font-semibold hover:bg-primary-dark transition-all text-sm"
              >
                Daftar
              </router-link>
            </div>
          </template>
        </div>

      </div>
    </header>

    <!-- ===== Mobile Drawer ===== -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 md:hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-ink/50 backdrop-blur-[2px]" @click="mobileMenuOpen = false" />

        <!-- Drawer panel -->
        <Transition
          enter-active-class="transition-transform duration-200 ease-out"
          enter-from-class="-translate-x-full"
          enter-to-class="translate-x-0"
          leave-active-class="transition-transform duration-150 ease-in"
          leave-from-class="translate-x-0"
          leave-to-class="-translate-x-full"
          appear
        >
          <div class="absolute left-0 inset-y-0 w-72 bg-white shadow-2xl flex flex-col">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-4 py-4 border-b border-ink/10 shrink-0">
              <div class="flex items-center gap-2.5">
                <img src="/images/logo.webp" alt="IPUL BUAH" class="h-8 w-8 object-contain" />
                <span class="font-black text-primary text-base">IPUL BUAH</span>
              </div>
              <button
                @click="mobileMenuOpen = false"
                class="w-8 h-8 rounded-full hover:bg-ink/8 flex items-center justify-center text-ink/50 transition"
              >
                <XMarkIcon class="w-5 h-5" stroke-width="1.75" />
              </button>
            </div>

            <!-- User info (jika login) -->
            <div v-if="auth.isLoggedIn" class="px-4 py-3 bg-primary/5 border-b border-ink/8 shrink-0">
              <p class="text-[10px] text-ink/40 uppercase tracking-wider font-semibold mb-0.5">Masuk sebagai</p>
              <p class="font-bold text-ink text-sm">{{ auth.user?.name }}</p>
            </div>

            <!-- Nav links -->
            <nav class="flex-1 overflow-y-auto py-2">
              <router-link
                to="/"
                @click="mobileMenuOpen = false"
                class="flex items-center gap-3.5 px-5 py-3.5 text-sm font-semibold transition-colors"
                :class="route.path === '/' ? 'text-primary bg-primary/5' : 'text-ink hover:bg-ink/4'"
              >
                <HomeIcon class="w-5 h-5 shrink-0" stroke-width="1.75" /> Beranda
              </router-link>

              <button
                @click="navTo('/katalog')"
                class="w-full flex items-center gap-3.5 px-5 py-3.5 text-sm font-semibold text-ink/70 hover:bg-ink/4 transition-colors cursor-pointer"
                :class="route.path.startsWith('/katalog') ? 'text-primary bg-primary/5' : ''"
              >
                <MagnifyingGlassIcon class="w-5 h-5 shrink-0" stroke-width="1.75" /> Katalog Buah
              </button>

              <button
                @click="navTo('/paket-buah')"
                class="w-full flex items-center gap-3.5 px-5 py-3.5 text-sm font-semibold text-ink/70 hover:bg-ink/4 transition-colors cursor-pointer"
              >
                <CubeIcon class="w-5 h-5 shrink-0" stroke-width="1.75" /> Paket & Hampers
              </button>

              <button
                @click="navTo('/parsel-kustom')"
                class="w-full flex items-center gap-3.5 px-5 py-3.5 text-sm font-semibold text-ink/70 hover:bg-ink/4 transition-colors cursor-pointer"
              >
                <GiftIcon class="w-5 h-5 shrink-0" stroke-width="1.75" /> Buat Parsel Kustom
              </button>

              <button
                @click="navTo('/musiman')"
                class="w-full flex items-center gap-3.5 px-5 py-3.5 text-sm font-semibold text-ink/70 hover:bg-ink/4 transition-colors cursor-pointer"
              >
                <SunIcon class="w-5 h-5 shrink-0" stroke-width="1.75" /> Buah Musiman
              </button>

              <button
                @click="navTo('/wishlist')"
                class="w-full flex items-center gap-3.5 px-5 py-3.5 text-sm font-semibold text-ink/70 hover:bg-ink/4 transition-colors cursor-pointer"
              >
                <HeartIcon class="w-5 h-5 shrink-0" stroke-width="1.75" /> Wishlist
              </button>

              <div class="border-t border-ink/8 mx-4 my-1.5"></div>

              <router-link
                to="/tentang-kami"
                @click="mobileMenuOpen = false"
                class="flex items-center gap-3.5 px-5 py-3 text-sm text-ink/55 hover:bg-ink/4 transition-colors"
              >
                Tentang Kami
              </router-link>
              <router-link
                to="/faq"
                @click="mobileMenuOpen = false"
                class="flex items-center gap-3.5 px-5 py-3 text-sm text-ink/55 hover:bg-ink/4 transition-colors"
              >
                FAQ & Bantuan
              </router-link>
              <router-link
                to="/kontak"
                @click="mobileMenuOpen = false"
                class="flex items-center gap-3.5 px-5 py-3 text-sm text-ink/55 hover:bg-ink/4 transition-colors"
              >
                Kontak
              </router-link>

              <!-- Auth links (mobile) -->
              <div v-if="!auth.isLoggedIn" class="px-4 pt-2 pb-1 space-y-2">
                <div class="border-t border-ink/8 mb-3"></div>
                <router-link
                  to="/register"
                  @click="mobileMenuOpen = false"
                  class="flex items-center justify-center py-2.5 rounded-full bg-primary text-white font-semibold text-sm transition hover:bg-primary-dark"
                >
                  Daftar Sekarang
                </router-link>
                <router-link
                  to="/login"
                  @click="mobileMenuOpen = false"
                  class="flex items-center justify-center py-2.5 rounded-full border border-primary/30 text-primary font-semibold text-sm transition hover:bg-primary/5"
                >
                  Masuk
                </router-link>
              </div>
            </nav>

            <!-- Drawer footer: WA -->
            <div class="p-4 border-t border-ink/10 shrink-0">
              <a
                :href="storeInfo.whatsappUrl"
                target="_blank"
                rel="noopener"
                class="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-semibold text-sm text-white transition hover:opacity-90"
                style="background-color: #25D366;"
              >
                <WhatsAppIcon size="w-5 h-5" class="brightness-0 invert" /> Chat WhatsApp
              </a>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ===== Konten Halaman ===== -->
    <!-- pb-20 md:pb-0 → ruang untuk bottom nav di mobile -->
    <main class="flex-1 pb-20 md:pb-0">
      <router-view />
    </main>

    <!-- ===== Footer ===== -->
    <footer class="bg-primary-dark text-white mt-12">
      <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 text-sm">
        <div class="col-span-2 md:col-span-1">
          <div class="flex items-center gap-2 mb-3">
            <img src="/images/logo.webp" alt="IPUL BUAH" class="h-9 w-9 object-contain shrink-0" />
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
    <!-- bottom-24 md:bottom-6 → di atas bottom nav bar mobile -->
    <div class="fixed bottom-24 md:bottom-6 right-5 z-30 flex items-center gap-3 group">
      <div class="bg-white text-ink text-sm font-bold px-4 py-2 rounded-full shadow-lg opacity-0 translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-200 pointer-events-none whitespace-nowrap border border-ink/8">
        💬 Butuh bantuan?
      </div>
      <a
        :href="storeInfo.whatsappUrl"
        target="_blank"
        rel="noopener"
        class="relative w-14 h-14 rounded-full flex items-center justify-center shadow-xl hover:scale-110 active:scale-95 transition-transform cursor-pointer"
        style="background-color: #25D366;"
        title="Chat WhatsApp"
      >
        <span class="absolute inset-0 rounded-full animate-ping opacity-30" style="background-color: #25D366;"></span>
        <WhatsAppIcon size="w-7 h-7" class="brightness-0 invert relative z-10" />
      </a>
    </div>

    <!-- ===== Bottom Navigation Bar (mobile only) ===== -->
    <nav class="fixed bottom-0 inset-x-0 z-40 md:hidden bg-white border-t border-ink/10 shadow-[0_-2px_20px_rgba(0,0,0,0.08)]">
      <div class="grid grid-cols-4">

        <router-link
          to="/"
          class="flex flex-col items-center justify-center gap-1 py-2.5 text-[10px] font-semibold transition-colors"
          :class="route.path === '/' ? 'text-primary' : 'text-ink/40'"
        >
          <HomeIcon class="w-5 h-5" stroke-width="1.75" />
          Beranda
        </router-link>

        <button
          @click="goToProtected('/katalog')"
          class="flex flex-col items-center justify-center gap-1 py-2.5 text-[10px] font-semibold transition-colors cursor-pointer"
          :class="route.path.startsWith('/katalog') || route.path.startsWith('/produk') ? 'text-primary' : 'text-ink/40'"
        >
          <MagnifyingGlassIcon class="w-5 h-5" stroke-width="1.75" />
          Katalog
        </button>

        <button
          @click="goToProtected('/keranjang')"
          class="flex flex-col items-center justify-center gap-1 py-2.5 text-[10px] font-semibold transition-colors cursor-pointer relative"
          :class="route.path === '/keranjang' ? 'text-primary' : 'text-ink/40'"
        >
          <span class="relative">
            <ShoppingCartIcon class="w-5 h-5" stroke-width="1.75" />
            <span
              v-if="cart.itemCount > 0"
              class="absolute -top-1.5 -right-2 bg-accent text-white text-[8px] font-black min-w-[14px] h-3.5 rounded-full flex items-center justify-center px-0.5 leading-none"
            >{{ cart.itemCount > 9 ? '9+' : cart.itemCount }}</span>
          </span>
          Keranjang
        </button>

        <template v-if="auth.isLoggedIn">
          <router-link
            to="/akun"
            class="flex flex-col items-center justify-center gap-1 py-2.5 text-[10px] font-semibold transition-colors"
            :class="route.path === '/akun' ? 'text-primary' : 'text-ink/40'"
          >
            <UserCircleIcon class="w-5 h-5" stroke-width="1.75" />
            Akun
          </router-link>
        </template>
        <template v-else>
          <router-link
            to="/login"
            class="flex flex-col items-center justify-center gap-1 py-2.5 text-[10px] font-semibold text-ink/40"
          >
            <UserCircleIcon class="w-5 h-5" stroke-width="1.75" />
            Masuk
          </router-link>
        </template>

      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useStoreInfoStore } from '../stores/store'
import { useCartStore } from '../stores/cart'
import {
  EyeIcon,
  ArrowLeftIcon,
  HeartIcon,
  ShoppingCartIcon,
  MapPinIcon,
  Bars3Icon,
  XMarkIcon,
  HomeIcon,
  MagnifyingGlassIcon,
  UserCircleIcon,
  GiftIcon,
  SunIcon,
  CubeIcon,
} from '@heroicons/vue/24/outline'
import WhatsAppIcon from '../components/shared/WhatsAppIcon.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const storeInfo = useStoreInfoStore()
const cart = useCartStore()
const mobileMenuOpen = ref(false)

onMounted(() => {
  storeInfo.fetchStoreInfo()
  if (auth.isLoggedIn) cart.fetchCart()
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

function navTo(path) {
  mobileMenuOpen.value = false
  goToProtected(path)
}

function exitPreview() {
  auth.disablePreview()
  router.push(auth.user.role === 'superadmin' ? '/superadmin' : '/admin')
}
</script>
