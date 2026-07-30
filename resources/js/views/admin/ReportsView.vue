<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h2 class="font-bold text-ink">Laporan Toko</h2>
      <div class="flex gap-2">
        <button @click="exportFile('excel')" :disabled="exporting" class="bg-success text-white px-4 py-2 rounded-full text-sm flex items-center gap-1.5 hover:opacity-90 transition cursor-pointer disabled:opacity-60">
          <TableCellsIcon class="w-4 h-4" stroke-width="1.75" /> {{ exporting === 'excel' ? 'Mengunduh...' : 'Excel' }}
        </button>
        <button @click="exportFile('pdf')" :disabled="exporting" class="bg-danger text-white px-4 py-2 rounded-full text-sm flex items-center gap-1.5 hover:opacity-90 transition cursor-pointer disabled:opacity-60">
          <DocumentArrowDownIcon class="w-4 h-4" stroke-width="1.75" /> {{ exporting === 'pdf' ? 'Mengunduh...' : 'PDF' }}
        </button>
      </div>
    </div>

    <div class="flex gap-2 mb-5 flex-wrap">
      <button v-for="t in tabs" :key="t.key" @click="tab = t.key; loadTab()" class="tab-btn cursor-pointer" :class="tab === t.key && 'tab-active'">{{ t.label }}</button>
    </div>

    <!-- ===== TAB PENJUALAN ===== -->
    <div v-if="tab === 'sales'" class="space-y-4">

      <!-- Filter Waktu -->
      <div class="glass-card rounded-xl2 p-4">
        <div class="flex flex-wrap items-center gap-3">
          <p class="text-sm font-semibold text-ink/70 shrink-0">Filter Waktu:</p>
          <div class="flex gap-2 flex-wrap">
            <button
              v-for="f in timeFilters" :key="f.key"
              @click="activePeriod = f.key; applySalesFilter()"
              class="text-xs font-semibold px-3.5 py-1.5 rounded-full border transition cursor-pointer"
              :class="activePeriod === f.key
                ? 'bg-primary text-white border-primary'
                : 'bg-white text-ink/60 border-ink/15 hover:border-primary/40'"
            >
              {{ f.label }}
            </button>
          </div>
          <div v-if="activePeriod === 'custom'" class="flex items-center gap-2 ml-auto">
            <input type="date" v-model="customFrom" class="input text-xs px-2.5 py-1.5" />
            <span class="text-ink/40 text-xs">s/d</span>
            <input type="date" v-model="customTo" class="input text-xs px-2.5 py-1.5" />
            <button @click="applySalesFilter()" class="text-xs font-semibold bg-primary text-white px-3 py-1.5 rounded-full hover:bg-primary-dark transition cursor-pointer">Terapkan</button>
          </div>
        </div>
        <p class="text-[11px] text-ink/40 mt-2">
          Periode: <span class="font-medium text-ink/60">{{ periodLabel }}</span>
        </p>
      </div>

      <!-- Ringkasan -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="glass-card rounded-xl2 p-5">
          <p class="text-xs text-ink/50">Total Penjualan</p>
          <p class="text-2xl font-bold text-primary tabular-nums mt-1">Rp {{ formatPrice(salesData.total) }}</p>
        </div>
        <div class="glass-card rounded-xl2 p-5">
          <p class="text-xs text-ink/50">Jumlah Pesanan</p>
          <p class="text-2xl font-bold text-ink tabular-nums mt-1">{{ salesData.jumlah_pesanan ?? 0 }}</p>
        </div>
        <div class="glass-card rounded-xl2 p-5">
          <p class="text-xs text-ink/50">Rata-rata per Pesanan</p>
          <p class="text-2xl font-bold text-accent tabular-nums mt-1">Rp {{ formatPrice(avgPerOrder) }}</p>
        </div>
      </div>

      <!-- Tren Harian -->
      <div v-if="salesData.tren?.length" class="glass-card rounded-xl2 p-5">
        <p class="font-semibold text-sm text-ink mb-3">Tren Penjualan per Hari</p>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-ink/10">
                <th class="text-left py-2 px-3 text-ink/50 font-medium text-xs">Tanggal</th>
                <th class="text-right py-2 px-3 text-ink/50 font-medium text-xs">Total Penjualan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in salesData.tren" :key="t.tanggal" class="border-b border-ink/5 hover:bg-ink/3 transition">
                <td class="py-2.5 px-3 text-ink">{{ formatDateFull(t.tanggal) }}</td>
                <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-ink">Rp {{ formatPrice(t.total) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="bg-primary/5">
                <td class="py-2.5 px-3 font-bold text-ink">Total</td>
                <td class="py-2.5 px-3 text-right tabular-nums font-bold text-primary">Rp {{ formatPrice(salesData.total) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Tabel Detail Pesanan -->
      <div class="glass-card rounded-xl2 p-5">
        <div class="flex items-center justify-between mb-3">
          <p class="font-semibold text-sm text-ink">Detail Pesanan Selesai</p>
          <p class="text-[11px] text-ink/40">{{ salesOrders.length }} pesanan</p>
        </div>

        <div v-if="loadingSalesOrders" class="py-8 text-center text-ink/40 text-sm">Memuat data pesanan...</div>

        <div v-else-if="salesOrders.length === 0" class="py-8 text-center text-ink/40 text-sm">Tidak ada pesanan selesai di periode ini</div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-ink/10">
                <th class="text-left py-2 px-3 text-ink/50 font-medium text-xs">No. Pesanan</th>
                <th class="text-left py-2 px-3 text-ink/50 font-medium text-xs">Pelanggan</th>
                <th class="text-left py-2 px-3 text-ink/50 font-medium text-xs">Tanggal</th>
                <th class="text-left py-2 px-3 text-ink/50 font-medium text-xs">Item</th>
                <th class="text-left py-2 px-3 text-ink/50 font-medium text-xs">Metode</th>
                <th class="text-right py-2 px-3 text-ink/50 font-medium text-xs">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="o in salesOrders" :key="o.id" class="border-b border-ink/5 hover:bg-ink/3 transition">
                <td class="py-2.5 px-3 font-medium text-ink">{{ o.order_number }}</td>
                <td class="py-2.5 px-3 text-ink/70">{{ o.user?.name ?? '-' }}</td>
                <td class="py-2.5 px-3 text-ink/70">{{ formatDateShort(o.created_at) }}</td>
                <td class="py-2.5 px-3 text-ink/70">
                  <span class="truncate max-w-[180px] inline-block align-bottom" :title="o.items?.map(i => i.item_name).join(', ')">
                    {{ o.items?.map(i => i.item_name).join(', ') || '-' }}
                  </span>
                </td>
                <td class="py-2.5 px-3">
                  <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full uppercase" :class="o.fulfillment_type === 'delivery' ? 'bg-primary/10 text-primary' : 'bg-badge/15 text-accent'">
                    {{ o.fulfillment_type === 'delivery' ? 'Delivery' : 'Pickup' }}
                  </span>
                </td>
                <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-ink">Rp {{ formatPrice(o.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ===== TAB PRODUK ===== -->
    <div v-if="tab === 'products'" class="glass-card rounded-xl2 p-5">
      <p class="font-semibold mb-3">Produk Terlaris</p>
      <div v-for="(p, i) in productsData.terlaris" :key="p.id" class="flex justify-between items-center text-sm py-1.5 border-b border-ink/5">
        <span class="flex items-center gap-2"><span class="w-5 h-5 rounded-full bg-primary/10 text-primary text-[11px] font-bold flex items-center justify-center shrink-0">{{ i + 1 }}</span>{{ p.name }}</span>
        <span class="tabular-nums">{{ p.sold_count }} terjual</span>
      </div>
    </div>

    <!-- ===== TAB STOK ===== -->
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

    <!-- ===== TAB PENGIRIMAN ===== -->
    <div v-if="tab === 'shipping'" class="glass-card rounded-xl2 p-5">
      <p class="font-semibold mb-3">Delivery vs Pickup</p>
      <div v-for="s in shippingData.per_tipe" :key="s.fulfillment_type" class="flex justify-between text-sm py-1.5 border-b border-ink/5">
        <span class="capitalize">{{ s.fulfillment_type }}</span><span class="tabular-nums">{{ s.jumlah }} pesanan</span>
      </div>
    </div>

    <!-- ===== TAB PELANGGAN ===== -->
    <div v-if="tab === 'customers'" class="glass-card rounded-xl2 p-5">
      <p class="font-semibold mb-3">Pelanggan Paling Aktif</p>
      <div v-for="c in customersData.paling_aktif" :key="c.id" class="text-sm py-1 border-b border-ink/5">{{ c.name }}</div>
    </div>

    <!-- ===== TAB PEMBAYARAN ===== -->
    <div v-if="tab === 'payments'" class="glass-card rounded-xl2 p-5">
      <p class="font-semibold mb-3">Rekap Pembayaran</p>
      <div v-for="r in paymentsData.rekap" :key="r.method + r.status" class="flex justify-between text-sm py-1.5 border-b border-ink/5">
        <span>{{ r.method }} ({{ r.status }})</span><span class="tabular-nums">Rp {{ formatPrice(r.total) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import { TableCellsIcon, DocumentArrowDownIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const tabs = [
  { key: 'sales', label: 'Penjualan' }, { key: 'products', label: 'Produk' },
  { key: 'stock', label: 'Stok' }, { key: 'shipping', label: 'Pengiriman' },
  { key: 'customers', label: 'Pelanggan' }, { key: 'payments', label: 'Pembayaran' },
]
const tab = ref('sales')
const exporting = ref(null)
const salesData = ref({})
const salesOrders = ref([])
const loadingSalesOrders = ref(false)
const productsData = ref({})
const stockData = ref({ products: [], recent_changes: [] })
const shippingData = ref({})
const customersData = ref({})
const paymentsData = ref({})

const timeFilters = [
  { key: 'today', label: 'Hari Ini' },
  { key: 'week', label: 'Minggu Ini' },
  { key: 'month', label: 'Bulan Ini' },
  { key: 'all', label: 'Semua' },
  { key: 'custom', label: 'Kustom' },
]
const activePeriod = ref('all')
const customFrom = ref('')
const customTo = ref('')

function getDateRange(period) {
  const now = new Date()
  const yyyy = now.getFullYear()
  const mm = String(now.getMonth() + 1).padStart(2, '0')
  const dd = String(now.getDate()).padStart(2, '0')
  const today = `${yyyy}-${mm}-${dd}`

  if (period === 'today') return { from: today, to: today }
  if (period === 'week') {
    const dayOfWeek = now.getDay() || 7
    const monday = new Date(now)
    monday.setDate(now.getDate() - dayOfWeek + 1)
    const mStr = `${monday.getFullYear()}-${String(monday.getMonth() + 1).padStart(2, '0')}-${String(monday.getDate()).padStart(2, '0')}`
    return { from: mStr, to: today }
  }
  if (period === 'month') return { from: `${yyyy}-${mm}-01`, to: today }
  if (period === 'custom') return { from: customFrom.value, to: customTo.value }
  return { from: '', to: '' }
}

const periodLabel = computed(() => {
  const range = getDateRange(activePeriod.value)
  if (activePeriod.value === 'all') return 'Semua waktu'
  if (activePeriod.value === 'today') return `Hari ini (${formatDateFull(range.from)})`
  if (!range.from && !range.to) return 'Semua waktu'
  return `${formatDateFull(range.from)} — ${formatDateFull(range.to)}`
})

const avgPerOrder = computed(() => {
  const total = salesData.value.total || 0
  const count = salesData.value.jumlah_pesanan || 0
  return count > 0 ? Math.round(total / count) : 0
})

async function applySalesFilter() {
  const { from, to } = getDateRange(activePeriod.value)
  const params = {}
  if (from) params.from = from
  if (to) params.to = to

  salesData.value = (await api.get('/admin/reports/sales', { params })).data
  fetchSalesOrders(params)
}

async function fetchSalesOrders(params = {}) {
  loadingSalesOrders.value = true
  try {
    const { data } = await api.get('/admin/orders', { params: { status: 'selesai', per_page: 100, ...params } })
    salesOrders.value = data.data || data
  } catch {
    salesOrders.value = []
  } finally {
    loadingSalesOrders.value = false
  }
}

async function loadTab() {
  if (tab.value === 'sales') {
    await applySalesFilter()
  }
  if (tab.value === 'products') productsData.value = (await api.get('/admin/reports/products')).data
  if (tab.value === 'stock') stockData.value = (await api.get('/admin/reports/stock')).data
  if (tab.value === 'shipping') shippingData.value = (await api.get('/admin/reports/shipping')).data
  if (tab.value === 'customers') customersData.value = (await api.get('/admin/reports/customers')).data
  if (tab.value === 'payments') paymentsData.value = (await api.get('/admin/reports/payments')).data
}

async function exportFile(type) {
  exporting.value = type
  try {
    const { from, to } = getDateRange(activePeriod.value)
    const params = {}
    if (from) params.from = from
    if (to) params.to = to
    const endpoint = type === 'excel' ? '/admin/reports/sales/export-excel' : '/admin/reports/sales/export-pdf'
    const ext = type === 'excel' ? 'xlsx' : 'pdf'
    const { data } = await api.get(endpoint, { params, responseType: 'blob' })
    const url = URL.createObjectURL(data)
    const a = document.createElement('a')
    a.href = url
    a.download = `laporan-penjualan.${ext}`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    alert('Gagal mengunduh laporan. Coba lagi.')
  } finally {
    exporting.value = null
  }
}

function formatPrice(v) { return new Intl.NumberFormat('id-ID').format(v || 0) }

function formatDateFull(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

function formatDateShort(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(loadTab)
</script>

<style scoped>
.tab-btn { @apply px-4 py-2 rounded-full text-sm font-medium text-ink/60 border border-ink/10; }
.tab-active { @apply bg-primary text-white border-primary; }
</style>
