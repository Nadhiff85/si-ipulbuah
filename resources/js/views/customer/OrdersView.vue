<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-ink mb-6">Pesanan Saya</h1>

    <p v-if="loading" class="text-ink/50 text-sm">Memuat pesanan...</p>
    <p v-else-if="orders.length === 0" class="text-ink/50 text-sm">Anda belum memiliki pesanan.</p>

    <div v-else class="space-y-3">
      <router-link
        v-for="order in orders"
        :key="order.id"
        :to="`/pesanan/${order.id}`"
        class="block bg-white rounded-xl2 border border-ink/5 p-4 hover:shadow-md transition"
      >
        <div class="flex justify-between items-start mb-2">
          <div>
            <p class="font-semibold text-ink">{{ order.order_number }}</p>
            <p class="text-xs text-ink/50">{{ formatDate(order.created_at) }}</p>
          </div>
          <span class="text-xs font-semibold px-3 py-1 rounded-full" :class="statusClass(order.status)">
            {{ statusText(order.status) }}
          </span>
        </div>
        <div class="flex justify-between items-center text-sm">
          <span class="text-ink/60">{{ order.items.length }} item · {{ order.fulfillment_type === 'delivery' ? 'Delivery' : 'Pickup' }}</span>
          <span class="font-bold text-ink">Rp {{ formatPrice(order.total) }}</span>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const orders = ref([])
const loading = ref(true)

async function fetchOrders() {
  loading.value = true
  try {
    const { data } = await api.get('/orders')
    orders.value = data.data
  } finally {
    loading.value = false
  }
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}

function statusText(s) {
  return {
    menunggu_bayar: 'Menunggu Bayar',
    dikonfirmasi: 'Dikonfirmasi',
    diproses: 'Diproses',
    dikirim_siap_ambil: 'Dikirim/Siap Ambil',
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
    selesai: 'bg-success/25 text-success',
    dibatalkan: 'bg-danger/15 text-danger',
  }[s] || 'bg-ink/10 text-ink/60'
}

onMounted(fetchOrders)
</script>
