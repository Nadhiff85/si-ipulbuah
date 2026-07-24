<template>
  <div class="min-h-screen flex glass-panel-bg">
    <!-- ===== Sidebar ===== -->
    <aside class="w-64 bg-primary-dark text-white shrink-0 hidden lg:flex flex-col">
      <div class="px-5 py-4 flex items-center gap-2.5 border-b border-white/10">
        <span class="h-8 w-8 rounded-lg bg-accent text-white font-black text-xs flex items-center justify-center shrink-0">IB</span>
        <div class="leading-tight">
          <p class="font-semibold text-[15px]">IPUL BUAH</p>
          <p class="text-[11px] text-white/45">Admin Toko</p>
        </div>
      </div>

      <nav class="flex-1 overflow-y-auto py-3">
        <SidebarLink to="/admin" :icon="Squares2X2Icon" label="Dashboard" exact />

        <SidebarGroup title="Master Data">
          <SidebarLink to="/admin/produk" :icon="ShoppingBagIcon" label="Produk" />
          <SidebarLink to="/admin/kategori" :icon="TagIcon" label="Kategori" />
          <SidebarLink to="/admin/paket-hampers" :icon="GiftIcon" label="Paket / Hampers" />
          <SidebarLink to="/admin/promo" :icon="ReceiptPercentIcon" label="Promo & Diskon" />
          <SidebarLink to="/admin/area-pengiriman" :icon="MapPinIcon" label="Area Pengiriman" />
        </SidebarGroup>

        <SidebarGroup title="Transaksi">
          <SidebarLink to="/admin/pesanan" :icon="ClipboardDocumentListIcon" label="Pesanan Masuk" />
          <SidebarLink to="/admin/pembayaran" :icon="CreditCardIcon" label="Pembayaran" />
          <SidebarLink to="/admin/jadwal-pengiriman" :icon="ClockIcon" label="Jadwal Pengiriman" />
        </SidebarGroup>

        <SidebarGroup title="Pelanggan">
          <SidebarLink to="/admin/pelanggan" :icon="UsersIcon" label="Pelanggan" />
          <SidebarLink to="/admin/ulasan" :icon="StarIcon" label="Ulasan & Rating" />
        </SidebarGroup>

        <SidebarGroup title="Komunikasi">
          <SidebarLink to="/admin/whatsapp-center" :icon="ChatBubbleLeftRightIcon" label="WhatsApp Center" />
          <SidebarLink to="/admin/notifikasi" :icon="BellAlertIcon" label="Kirim Notifikasi" />
        </SidebarGroup>

        <SidebarGroup title="Laporan">
          <SidebarLink to="/admin/laporan" :icon="ChartBarIcon" label="Semua Laporan" />
        </SidebarGroup>

        <SidebarGroup title="Pengaturan">
          <SidebarLink to="/admin/konten" :icon="PhotoIcon" label="Konten (Banner/FAQ)" />
          <SidebarLink to="/admin/pengaturan-toko" :icon="Cog6ToothIcon" label="Pengaturan Toko" />
        </SidebarGroup>
      </nav>
    </aside>

    <!-- ===== Konten ===== -->
    <div class="flex-1 min-w-0">
      <header class="bg-white sticky top-0 z-30 shadow-sm px-6 py-3.5 flex items-center justify-between">
        <div>
          <h1 class="font-semibold text-ink text-[15px]">{{ pageTitle }}</h1>
          <p class="text-xs text-ink/45 mt-0.5">Selamat datang kembali, {{ auth.user?.name }}</p>
        </div>
        <div class="flex items-center gap-4">
          <button
            @click="previewStore"
            class="flex items-center gap-1.5 text-xs font-medium text-primary bg-primary/10 hover:bg-primary/20 px-3 py-1.5 rounded-full transition cursor-pointer"
          >
            <EyeIcon class="w-4 h-4" stroke-width="1.75" /> Lihat Tampilan Toko
          </button>
          <span
            class="text-xs font-medium px-3 py-1.5 rounded-full flex items-center gap-1.5"
            :class="storeInfo.isOpenNow ? 'bg-success/15 text-success' : 'bg-danger/10 text-danger'"
          >
            <span class="w-1.5 h-1.5 rounded-full" :class="storeInfo.isOpenNow ? 'bg-success' : 'bg-danger'"></span>
            Toko {{ storeInfo.isOpenNow ? 'Buka' : 'Tutup' }}
          </span>
          <button class="text-ink/40 hover:text-ink/70 transition cursor-pointer" aria-label="Notifikasi">
            <BellIcon class="w-5 h-5" stroke-width="1.75" />
          </button>
          <button @click="logout" class="flex items-center gap-2 text-sm text-ink/60 hover:text-danger transition cursor-pointer" aria-label="Keluar">
            <ArrowRightStartOnRectangleIcon class="w-5 h-5" stroke-width="1.75" />
          </button>
        </div>
      </header>

      <main class="p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useStoreInfoStore } from '../stores/store'
import SidebarLink from '../components/shared/SidebarLink.vue'
import SidebarGroup from '../components/shared/SidebarGroup.vue'
import {
  Squares2X2Icon, ShoppingBagIcon, TagIcon, GiftIcon, ReceiptPercentIcon, MapPinIcon,
  ClipboardDocumentListIcon, CreditCardIcon, ClockIcon, UsersIcon, StarIcon,
  ChatBubbleLeftRightIcon, BellAlertIcon, ChartBarIcon, PhotoIcon, Cog6ToothIcon,
  BellIcon, ArrowRightStartOnRectangleIcon, EyeIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const storeInfo = useStoreInfoStore()

const pageTitle = computed(() => route.meta.title || 'Dashboard Admin')

async function logout() {
  await auth.logout()
  router.push('/login')
}

function previewStore() {
  auth.enablePreview()
  router.push('/')
}

onMounted(() => storeInfo.fetchStoreInfo())
</script>
