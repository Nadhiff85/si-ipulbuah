<template>
  <div class="min-h-screen flex bg-surface-soft">
    <aside class="w-64 bg-primary-dark text-white shrink-0 hidden lg:flex flex-col">
      <div class="px-5 py-4 flex items-center gap-2.5 border-b border-white/10">
        <img src="/logo.png" class="h-8 w-8 object-contain rounded-full bg-white p-1" />
        <div class="leading-tight">
          <p class="font-semibold text-[15px]">IPUL BUAH</p>
          <p class="text-[11px] text-white/45">Superadmin Panel</p>
        </div>
      </div>

      <nav class="flex-1 overflow-y-auto py-3">
        <SidebarLink to="/superadmin" :icon="Squares2X2Icon" label="Dashboard" exact />

        <SidebarGroup title="Manajemen Admin">
          <SidebarLink to="/superadmin/admin-toko" :icon="UserCircleIcon" label="Akun Admin Toko" />
          <SidebarLink to="/superadmin/role-permission" :icon="KeyIcon" label="Role & Permission" />
        </SidebarGroup>

        <SidebarGroup title="Audit & Keamanan">
          <SidebarLink to="/superadmin/audit-trail-pelanggan" :icon="MagnifyingGlassCircleIcon" label="Audit Trail Pelanggan" />
          <SidebarLink to="/superadmin/audit-trail-admin" :icon="DocumentTextIcon" label="Audit Trail Admin" />
          <SidebarLink to="/superadmin/keamanan" :icon="ShieldCheckIcon" label="Keamanan Sistem" />
        </SidebarGroup>

        <SidebarGroup title="Operasional">
          <SidebarLink to="/superadmin/wilayah-global" :icon="GlobeAltIcon" label="Wilayah Pengiriman Global" />
          <SidebarLink to="/superadmin/monitoring" :icon="SignalIcon" label="Monitoring Sistem" />
          <SidebarLink to="/superadmin/log-sistem" :icon="ClipboardDocumentListIcon" label="Log Aktivitas Sistem" />
          <SidebarLink to="/superadmin/backup-database" :icon="CircleStackIcon" label="Backup Database" />
        </SidebarGroup>

        <SidebarGroup title="Pengaturan">
          <SidebarLink to="/superadmin/pengaturan-sistem" :icon="Cog6ToothIcon" label="Pengaturan Sistem" />
          <SidebarLink to="/superadmin/pengaturan-notifikasi" :icon="BellAlertIcon" label="Pengaturan Notifikasi" />
        </SidebarGroup>

        <SidebarGroup title="Akses Admin Toko">
          <SidebarLink to="/admin" :icon="BuildingStorefrontIcon" label="Buka Panel Admin Toko" />
        </SidebarGroup>
      </nav>

      <div class="p-4 border-t border-white/10">
        <div class="bg-white/5 rounded-xl2 p-3 flex items-center gap-2.5">
          <ShieldCheckIcon class="w-6 h-6 text-success shrink-0" stroke-width="1.75" />
          <div>
            <p class="text-xs font-medium">Keamanan Sistem</p>
            <p class="text-[11px] text-white/45">Sistem berjalan aman</p>
          </div>
        </div>
      </div>
    </aside>

    <div class="flex-1 min-w-0">
      <header class="bg-white sticky top-0 z-30 border-b border-ink/5 px-6 py-3.5 flex items-center justify-between">
        <div>
          <h1 class="font-semibold text-ink text-[15px]">{{ pageTitle }}</h1>
          <p class="text-xs text-ink/45 mt-0.5">Selamat datang kembali, {{ auth.user?.name }}</p>
        </div>
        <button @click="logout" class="flex items-center gap-2 text-sm text-ink/60 hover:text-danger transition">
          <ArrowRightStartOnRectangleIcon class="w-5 h-5" stroke-width="1.75" />
        </button>
      </header>

      <main class="p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import SidebarLink from '../components/shared/SidebarLink.vue'
import SidebarGroup from '../components/shared/SidebarGroup.vue'
import {
  Squares2X2Icon, UserCircleIcon, KeyIcon, MagnifyingGlassCircleIcon, DocumentTextIcon,
  ShieldCheckIcon, GlobeAltIcon, SignalIcon, ClipboardDocumentListIcon, CircleStackIcon,
  Cog6ToothIcon, BellAlertIcon, BuildingStorefrontIcon, ArrowRightStartOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const pageTitle = computed(() => route.meta.title || 'Dashboard Superadmin')

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>
