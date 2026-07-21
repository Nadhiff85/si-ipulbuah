<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h2 class="font-bold text-ink">Laporan Toko</h2>
      <div class="flex gap-2">
        <a href="/api/admin/reports/sales/export-excel" target="_blank" class="bg-success text-white px-4 py-2 rounded-full text-sm flex items-center gap-1.5 hover:opacity-90 transition cursor-pointer">
          <TableCellsIcon class="w-4 h-4" stroke-width="1.75" /> Excel
        </a>
        <a href="/api/admin/reports/sales/export-pdf" target="_blank" class="bg-danger text-white px-4 py-2 rounded-full text-sm flex items-center gap-1.5 hover:opacity-90 transition cursor-pointer">
          <DocumentArrowDownIcon class="w-4 h-4" stroke-width="1.75" /> PDF
        </a>
      </div>
    </div>

    <div class="flex gap-2 mb-5 flex-wrap">
      <button v-for="t in tabs" :key="t.key" @click="tab = t.key; loadTab()" class="tab-btn cursor-pointer" :class="tab === t.key && 'tab-active'">{{ t.label }}</button>
    </div>

    <div v-if="tab === 'sales'" class="glass-card rounded-xl2 p-5">
      <p class="text-sm text-ink/60">Total Penjualan (Selesai)</p>
      <p class="text-2xl font-bold text-primary tabular-nums">Rp {{ formatPrice(salesData.total) }}</p>
      <p class="text-sm text-ink/60 mt-2">Jumlah Pesanan: <span class="tabular-nums">{{ salesData.jumlah_pesanan }}</span></p>
    </div>

    <div v-if="tab === 'products'" class="glass-card rounded-xl2 p-5">
      <p class="font-semibold mb-3">Produk Terlaris</p>
      <div v-for="(p, i) in productsData.terlaris" :key="p.id" class="flex justify-between items-center text-sm py-1.5 border-b border-ink/5">
        <span class="flex items-center gap-2"><span class="w-5 h-5 rounded-full bg-primary/10 text-primary text-[11px] font-bold flex items-center justify-center shrink-0">{{ i + 1 }}</span>{{ p.name }}</span>
        <span class="tabular-nums">{{ p.sold_count }} terjual</span>
      </div>
    </div>

    <div v-if="tab === 'stock'" class="glass-card rounded-xl2 p-5">
      <p class="font-semibold mb-3">Status Stok</p>
      <div v-for="p in stockData.products" :key="p.id" class="flex justify-between items-center text-sm py-1.5 border-b border-ink/5">
        <span>{{ p.name }}</span>
        <span class="tabular-nums flex items-center gap-1" :class="p.stock <= p.min_stock_alert ? 'text-danger font-semibold' : ''">
          <ExclamationTriangleIcon v-if="p.stock <= p.min_stock_alert" class="w-3.5 h-3.5" stroke-width="1.75" />
          {{ p.stock }}
        </span>
      </div>

      <p class="font-semibold mt-5 mb-3">Riwayat Perubahan Stok Terbaru</p>
      <div v-for="log in stockData.recent_changes" :key="log.id" class="text-xs py-1.5 border-b border-ink/5">
        <span class="font-medium">{{ log.product?.name }}</span> —
        <span class="tabular-nums">{{ log.stock_before }} → {{ log.stock_after }}</span>
        (<span class="tabular-nums" :class="log.change > 0 ? 'text-success' : 'text-danger'">{{ log.change > 0 ? '+' : '' }}{{ log.change }}</span>)
        oleh {{ log.user?.name || 'Sistem' }}, alasan: {{ log.reason }}
      </div>
    </div>

    <div v-if="tab === 'shipping'" class="glass-card rounded-xl2 p-5">
      <p class="font-semibold mb-3">Delivery vs Pickup</p>
      <div v-for="s in shippingData.per_tipe" :key="s.fulfillment_type" class="flex justify-between text-sm py-1.5 border-b border-ink/5">
        <span class="capitalize">{{ s.fulfillment_type }}</span><span class="tabular-nums">{{ s.jumlah }} pesanan</span>
      </div>
    </div>

    <div v-if="tab === 'customers'" class="glass-card rounded-xl2 p-5">
      <p class="font-semibold mb-3">Pelanggan Paling Aktif</p>
      <div v-for="c in customersData.paling_aktif" :key="c.id" class="text-sm py-1 border-b border-ink/5">{{ c.name }}</div>
    </div>

    <div v-if="tab === 'payments'" class="glass-card rounded-xl2 p-5">
      <p class="font-semibold mb-3">Rekap Pembayaran</p>
      <div v-for="r in paymentsData.rekap" :key="r.method + r.status" class="flex justify-between text-sm py-1.5 border-b border-ink/5">
        <span>{{ r.method }} ({{ r.status }})</span><span class="tabular-nums">Rp {{ formatPrice(r.total) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { TableCellsIcon, DocumentArrowDownIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const tabs = [
  { key: 'sales', label: 'Penjualan' }, { key: 'products', label: 'Produk' },
  { key: 'stock', label: 'Stok' }, { key: 'shipping', label: 'Pengiriman' },
  { key: 'customers', label: 'Pelanggan' }, { key: 'payments', label: 'Pembayaran' },
]
const tab = ref('sales')
const salesData = ref({})
const productsData = ref({})
const stockData = ref({ products: [], recent_changes: [] })
const shippingData = ref({})
const customersData = ref({})
const paymentsData = ref({})

async function loadTab() {
  if (tab.value === 'sales') salesData.value = (await api.get('/admin/reports/sales')).data
  if (tab.value === 'products') productsData.value = (await api.get('/admin/reports/products')).data
  if (tab.value === 'stock') stockData.value = (await api.get('/admin/reports/stock')).data
  if (tab.value === 'shipping') shippingData.value = (await api.get('/admin/reports/shipping')).data
  if (tab.value === 'customers') customersData.value = (await api.get('/admin/reports/customers')).data
  if (tab.value === 'payments') paymentsData.value = (await api.get('/admin/reports/payments')).data
}

function formatPrice(v) { return new Intl.NumberFormat('id-ID').format(v || 0) }

onMounted(loadTab)
</script>

<style scoped>
.tab-btn { @apply px-4 py-2 rounded-full text-sm font-medium text-ink/60 border border-ink/10; }
.tab-active { @apply bg-primary text-white border-primary; }
</style>
