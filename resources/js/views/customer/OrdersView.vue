<template>
  <div class="max-w-3xl mx-auto px-4 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-bold text-ink">Riwayat Pembelian</h1>
        <p class="text-xs text-ink/50 mt-0.5">{{ orders.length }} pesanan ditemukan</p>
      </div>
      <router-link
        to="/katalog"
        class="text-sm font-semibold text-white bg-primary hover:bg-primary-dark px-4 py-2 rounded-full transition cursor-pointer"
      >
        + Belanja Lagi
      </router-link>
    </div>

    <!-- Filter Tab -->
    <div class="flex gap-2 mb-5 overflow-x-auto pb-1">
      <button
        v-for="tab in tabs" :key="tab.key"
        @click="activeTab = tab.key"
        class="shrink-0 text-xs font-semibold px-4 py-2 rounded-full border transition cursor-pointer"
        :class="activeTab === tab.key
          ? 'bg-primary text-white border-primary'
          : 'bg-white text-ink/60 border-ink/15 hover:border-primary/40'"
      >
        {{ tab.label }}
        <span v-if="tab.count > 0" class="ml-1 bg-white/25 text-[10px] px-1.5 py-0.5 rounded-full" :class="activeTab === tab.key ? 'text-white' : 'text-ink/40'">
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="glass-card rounded-xl2 p-5 animate-pulse">
        <div class="flex justify-between mb-3">
          <div class="h-3.5 bg-ink/10 rounded w-36"></div>
          <div class="h-5 bg-ink/10 rounded-full w-24"></div>
        </div>
        <div class="h-3 bg-ink/10 rounded w-48 mb-3"></div>
        <div class="flex justify-between">
          <div class="h-3 bg-ink/10 rounded w-24"></div>
          <div class="h-3.5 bg-ink/10 rounded w-20"></div>
        </div>
      </div>
    </div>

    <!-- Kosong -->
    <div v-else-if="filtered.length === 0" class="glass-card rounded-xl2 p-10 text-center">
      <ArchiveBoxIcon class="w-10 h-10 text-ink/20 mx-auto mb-3" stroke-width="1.5" />
      <p class="font-semibold text-ink/50 mb-1">{{ activeTab === 'all' ? 'Belum ada pesanan' : 'Tidak ada pesanan di kategori ini' }}</p>
      <p class="text-xs text-ink/40 mb-4">Yuk mulai belanja buah segar pilihan!</p>
      <router-link to="/katalog" class="inline-block bg-primary text-white text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-primary-dark transition">
        Lihat Katalog
      </router-link>
    </div>

    <!-- Daftar Pesanan -->
    <div v-else class="space-y-3">
      <div
        v-for="order in filtered"
        :key="order.id"
        class="glass-card rounded-xl2 overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all"
      >
        <!-- Stripe status atas -->
        <div class="h-1 w-full" :class="stripeClass(order.status)"></div>

        <div class="p-4">
          <!-- Baris atas: nomor + badge status -->
          <div class="flex justify-between items-start mb-3">
            <div>
              <p class="font-bold text-ink text-sm">{{ order.order_number }}</p>
              <p class="text-[11px] text-ink/45 mt-0.5 flex items-center gap-1">
                <CalendarIcon class="w-3 h-3" stroke-width="2" />
                {{ formatDate(order.created_at) }}
              </p>
            </div>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full" :class="statusClass(order.status)">
              {{ statusText(order.status) }}
            </span>
          </div>

          <!-- Item summary -->
          <div class="flex items-center gap-2 mb-3">
            <div class="flex items-center gap-1 text-xs text-ink/60">
              <ShoppingBagIcon class="w-3.5 h-3.5 shrink-0" stroke-width="2" />
              <span>{{ order.items.length }} item</span>
            </div>
            <span class="text-ink/20">·</span>
            <div class="flex items-center gap-1 text-xs text-ink/60">
              <component :is="order.fulfillment_type === 'delivery' ? TruckIcon : BuildingStorefrontIcon" class="w-3.5 h-3.5 shrink-0" stroke-width="2" />
              <span>{{ order.fulfillment_type === 'delivery' ? 'Delivery' : 'Pickup' }}</span>
            </div>
            <span class="text-ink/20">·</span>
            <span class="text-xs text-ink/60 truncate max-w-[150px]">
              {{ order.items.map(i => i.item_name).join(', ') }}
            </span>
          </div>

          <!-- Baris bawah: total + aksi -->
          <div class="flex items-center justify-between pt-3 border-t border-ink/8">
            <span class="font-extrabold text-ink tabular-nums">Rp {{ formatPrice(order.total) }}</span>
            <div class="flex gap-2">
              <router-link
                v-if="order.payment?.method === 'qris' && order.payment?.status === 'pending' && order.status === 'menunggu_bayar'"
                :to="`/pesanan/${order.id}`"
                class="text-[11px] font-bold bg-accent text-white px-3 py-1.5 rounded-full hover:bg-accent-light transition"
                @click.stop
              >
                Bayar Sekarang
              </router-link>
              <router-link
                :to="`/pesanan/${order.id}`"
                class="text-[11px] font-semibold text-primary border border-primary/30 bg-primary/5 px-3 py-1.5 rounded-full hover:bg-primary/10 transition"
              >
                Detail →
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import {
  ArchiveBoxIcon,
  CalendarIcon,
  ShoppingBagIcon,
  TruckIcon,
  BuildingStorefrontIcon,
} from '@heroicons/vue/24/outline'

const orders = ref([])
const loading = ref(true)
const activeTab = ref('all')

async function fetchOrders() {
  loading.value = true
  try {
    const { data } = await api.get('/orders')
    orders.value = data.data
  } finally {
    loading.value = false
  }
}

const tabs = computed(() => [
  { key: 'all', label: 'Semua', count: orders.value.length },
  { key: 'menunggu_bayar', label: 'Menunggu Bayar', count: orders.value.filter(o => o.status === 'menunggu_bayar').length },
  { key: 'aktif', label: 'Aktif', count: orders.value.filter(o => ['dikonfirmasi','diproses','dikirim_siap_ambil'].includes(o.status)).length },
  { key: 'selesai', label: 'Selesai', count: orders.value.filter(o => o.status === 'selesai').length },
  { key: 'dibatalkan', label: 'Dibatalkan', count: orders.value.filter(o => o.status === 'dibatalkan').length },
])

const filtered = computed(() => {
  if (activeTab.value === 'all') return orders.value
  if (activeTab.value === 'aktif') return orders.value.filter(o => ['dikonfirmasi','diproses','dikirim_siap_ambil'].includes(o.status))
  return orders.value.filter(o => o.status === activeTab.value)
})

function stripeClass(s) {
  return {
    menunggu_bayar: 'bg-warning',
    dikonfirmasi: 'bg-primary',
    diproses: 'bg-badge',
    dikirim_siap_ambil: 'bg-success',
    selesai: 'bg-success',
    dibatalkan: 'bg-danger',
  }[s] || 'bg-ink/20'
}

function statusText(s) {
  return {
    menunggu_bayar: 'Menunggu Bayar',
    dikonfirmasi: 'Dikonfirmasi',
    diproses: 'Diproses',
    dikirim_siap_ambil: 'Dikirim / Siap Ambil',
    selesai: 'Selesai',
    dibatalkan: 'Dibatalkan',
  }[s] || s
}

function statusClass(s) {
  return {
    menunggu_bayar: 'bg-warning/15 text-warning',
    dikonfirmasi: 'bg-primary/15 text-primary',
    diproses: 'bg-badge/20 text-accent',
    dikirim_siap_ambil: 'bg-success/15 text-success',
    selesai: 'bg-success/20 text-success',
    dibatalkan: 'bg-danger/15 text-danger',
  }[s] || 'bg-ink/10 text-ink/60'
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(fetchOrders)
</script>
