<template>
  <div class="space-y-5">

    <!-- ===== BARIS 1: Statistik Utama ===== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="glass-card rounded-xl2 p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-ink/50 font-medium">Penjualan Hari Ini</p>
          <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
            <BanknotesIcon class="w-4 h-4 text-primary" stroke-width="1.75" />
          </div>
        </div>
        <p class="text-xl font-bold text-ink tabular-nums">Rp {{ formatPrice(stats.penjualan_hari_ini ?? stats.total_penjualan) }}</p>
        <p class="text-[11px] text-ink/40 mt-0.5">Total: Rp {{ formatPrice(stats.total_penjualan) }}</p>
      </div>

      <div class="glass-card rounded-xl2 p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-ink/50 font-medium">Pesanan Masuk</p>
          <div class="w-8 h-8 bg-accent/10 rounded-lg flex items-center justify-center">
            <ShoppingCartIcon class="w-4 h-4 text-accent" stroke-width="1.75" />
          </div>
        </div>
        <p class="text-xl font-bold text-ink tabular-nums">{{ stats.total_pesanan ?? 0 }}</p>
        <p class="text-[11px] mt-0.5">
          <span class="text-warning font-semibold">{{ stats.pesanan_diproses ?? 0 }}</span>
          <span class="text-ink/40"> sedang diproses</span>
        </p>
      </div>

      <div class="glass-card rounded-xl2 p-4 cursor-pointer hover:shadow-md transition" @click="showQuickAdd = true">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-ink/50 font-medium">Produk Aktif</p>
          <div class="w-8 h-8 bg-badge/15 rounded-lg flex items-center justify-center">
            <PlusCircleIcon class="w-4 h-4 text-badge" stroke-width="1.75" />
          </div>
        </div>
        <p class="text-xl font-bold text-ink tabular-nums">{{ stats.produk_aktif ?? allProducts.length }}</p>
        <p class="text-[11px] text-primary font-semibold mt-0.5">+ Tambah produk baru →</p>
      </div>

      <div class="glass-card rounded-xl2 p-4" :class="lowStockProducts.length > 0 ? 'ring-1 ring-danger/30' : ''">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-ink/50 font-medium">Stok Menipis</p>
          <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="lowStockProducts.length > 0 ? 'bg-danger/10' : 'bg-ink/5'">
            <ExclamationTriangleIcon class="w-4 h-4" :class="lowStockProducts.length > 0 ? 'text-danger' : 'text-ink/40'" stroke-width="1.75" />
          </div>
        </div>
        <p class="text-xl font-bold tabular-nums" :class="lowStockProducts.length > 0 ? 'text-danger' : 'text-ink'">{{ lowStockProducts.length }}</p>
        <p class="text-[11px] text-ink/40 mt-0.5">{{ lowStockProducts.length > 0 ? 'perlu restock segera' : 'semua stok aman' }}</p>
      </div>
    </div>

    <!-- ===== BARIS 2: Chart + Aksi Cepat ===== -->
    <div class="grid lg:grid-cols-3 gap-5">

      <!-- Grafik Penjualan -->
      <div class="lg:col-span-2 glass-card rounded-xl2 p-5">
        <div class="flex items-center justify-between mb-4">
          <p class="font-semibold text-ink text-sm">Grafik Penjualan (7 Hari Terakhir)</p>
          <router-link to="/admin/laporan" class="text-xs text-primary font-semibold hover:underline">Laporan Lengkap →</router-link>
        </div>
        <div class="h-44">
          <Line v-if="chartData" :data="chartData" :options="chartOptions" />
          <div v-else class="h-full flex items-center justify-center text-ink/30 text-sm">Belum ada data penjualan</div>
        </div>
      </div>

      <!-- Aksi Cepat -->
      <div class="glass-card rounded-xl2 p-5">
        <p class="font-semibold text-ink text-sm mb-3">Aksi Cepat</p>
        <div class="space-y-2">
          <button
            @click="showQuickAdd = true"
            class="w-full flex items-center gap-3 p-3 rounded-xl bg-primary/8 hover:bg-primary/15 transition text-left cursor-pointer group"
          >
            <div class="w-9 h-9 bg-primary rounded-lg flex items-center justify-center shrink-0">
              <PlusIcon class="w-5 h-5 text-white" stroke-width="2" />
            </div>
            <div>
              <p class="text-sm font-semibold text-ink">Tambah Produk Baru</p>
              <p class="text-[11px] text-ink/50">Daftarkan buah baru ke katalog</p>
            </div>
          </button>

          <router-link
            to="/admin/pesanan"
            class="w-full flex items-center gap-3 p-3 rounded-xl bg-accent/8 hover:bg-accent/15 transition text-left cursor-pointer"
          >
            <div class="w-9 h-9 bg-accent rounded-lg flex items-center justify-center shrink-0">
              <ClipboardDocumentListIcon class="w-5 h-5 text-white" stroke-width="2" />
            </div>
            <div>
              <p class="text-sm font-semibold text-ink">Kelola Pesanan</p>
              <p class="text-[11px] text-ink/50">
                <span v-if="stats.pesanan_diproses > 0" class="text-warning font-bold">{{ stats.pesanan_diproses }} menunggu</span>
                <span v-else>Semua diproses</span>
              </p>
            </div>
          </router-link>

          <router-link
            to="/admin/pembayaran"
            class="w-full flex items-center gap-3 p-3 rounded-xl bg-badge/8 hover:bg-badge/15 transition text-left cursor-pointer"
          >
            <div class="w-9 h-9 bg-badge rounded-lg flex items-center justify-center shrink-0">
              <CreditCardIcon class="w-5 h-5 text-ink" stroke-width="2" />
            </div>
            <div>
              <p class="text-sm font-semibold text-ink">Verifikasi Pembayaran</p>
              <p class="text-[11px] text-ink/50">Cek bukti transfer masuk</p>
            </div>
          </router-link>

          <router-link
            to="/admin/ulasan"
            class="w-full flex items-center gap-3 p-3 rounded-xl bg-ink/5 hover:bg-ink/10 transition text-left cursor-pointer"
          >
            <div class="w-9 h-9 bg-ink/15 rounded-lg flex items-center justify-center shrink-0">
              <StarIcon class="w-5 h-5 text-ink/70" stroke-width="2" />
            </div>
            <div>
              <p class="text-sm font-semibold text-ink">Moderasi Ulasan</p>
              <p class="text-[11px] text-ink/50">
                <span v-if="pendingReviews.length > 0" class="text-warning font-bold">{{ pendingReviews.length }} menunggu moderasi</span>
                <span v-else>Semua bersih</span>
              </p>
            </div>
          </router-link>
        </div>
      </div>
    </div>

    <!-- ===== BARIS 3: Pesanan Terbaru + Stok Menipis ===== -->
    <div class="grid lg:grid-cols-2 gap-5">

      <!-- Pesanan Terbaru -->
      <div class="glass-card rounded-xl2 p-5">
        <div class="flex items-center justify-between mb-4">
          <p class="font-semibold text-ink text-sm">Pesanan Terbaru</p>
          <router-link to="/admin/pesanan" class="text-xs text-primary font-semibold hover:underline">Lihat Semua →</router-link>
        </div>
        <div v-if="loadingOrders" class="space-y-3">
          <div v-for="i in 4" :key="i" class="h-12 bg-ink/5 rounded-lg animate-pulse"></div>
        </div>
        <div v-else-if="recentOrders.length === 0" class="text-center py-6 text-ink/40 text-sm">Belum ada pesanan</div>
        <div v-else class="space-y-2">
          <div
            v-for="order in recentOrders"
            :key="order.id"
            class="flex items-center justify-between py-2.5 border-b border-ink/5 last:border-0"
          >
            <div class="min-w-0">
              <p class="text-sm font-semibold text-ink truncate">{{ order.order_number }}</p>
              <p class="text-[11px] text-ink/45">{{ order.user?.name?.split(' ')[0] }} · {{ formatDate(order.created_at) }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0 ml-2">
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="statusClass(order.status)">
                {{ statusText(order.status) }}
              </span>
              <router-link :to="`/admin/pesanan`" class="text-[11px] text-primary font-semibold hover:underline">Lihat</router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Stok Menipis -->
      <div class="glass-card rounded-xl2 p-5">
        <div class="flex items-center justify-between mb-4">
          <p class="font-semibold text-ink text-sm flex items-center gap-1.5">
            Stok Perlu Restock
            <span v-if="lowStockProducts.length > 0" class="text-[10px] bg-danger/15 text-danger font-bold px-1.5 py-0.5 rounded-full">{{ lowStockProducts.length }}</span>
          </p>
          <router-link to="/admin/produk" class="text-xs text-primary font-semibold hover:underline">Kelola Produk →</router-link>
        </div>
        <div v-if="loadingProducts" class="space-y-3">
          <div v-for="i in 4" :key="i" class="h-12 bg-ink/5 rounded-lg animate-pulse"></div>
        </div>
        <div v-else-if="lowStockProducts.length === 0" class="text-center py-6">
          <CheckCircleIcon class="w-8 h-8 text-success mx-auto mb-2" stroke-width="1.5" />
          <p class="text-sm text-ink/50">Semua stok dalam kondisi aman</p>
        </div>
        <div v-else class="space-y-2">
          <div
            v-for="p in lowStockProducts"
            :key="p.id"
            class="flex items-center justify-between py-2.5 border-b border-ink/5 last:border-0"
          >
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="w-8 h-8 rounded-lg bg-primary/8 overflow-hidden shrink-0 flex items-center justify-center">
                <img v-if="p.images?.[0]" :src="p.images[0].image_path" class="w-full h-full object-cover" />
                <span v-else class="text-xs text-ink/30">🍑</span>
              </div>
              <div class="min-w-0">
                <p class="text-sm font-medium text-ink truncate">{{ p.name }}</p>
                <p class="text-[11px] text-ink/45">Satuan: {{ p.unit }}</p>
              </div>
            </div>
            <div class="text-right shrink-0 ml-2">
              <p class="text-sm font-bold text-danger tabular-nums">{{ p.stock }} {{ p.unit }}</p>
              <p class="text-[10px] text-ink/40">Min: {{ p.min_stock_alert }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== BARIS 4: Ulasan Pending + Metode Bayar ===== -->
    <div class="grid lg:grid-cols-2 gap-5">

      <!-- Ulasan menunggu moderasi -->
      <div class="glass-card rounded-xl2 p-5">
        <div class="flex items-center justify-between mb-4">
          <p class="font-semibold text-ink text-sm">Ulasan Menunggu Moderasi</p>
          <router-link to="/admin/ulasan" class="text-xs text-primary font-semibold hover:underline">Moderasi →</router-link>
        </div>
        <div v-if="pendingReviews.length === 0" class="text-center py-6">
          <CheckCircleIcon class="w-8 h-8 text-success mx-auto mb-2" stroke-width="1.5" />
          <p class="text-sm text-ink/50">Tidak ada ulasan yang menunggu</p>
        </div>
        <div v-else class="space-y-3">
          <div v-for="r in pendingReviews.slice(0, 3)" :key="r.id" class="flex gap-3 py-2 border-b border-ink/5 last:border-0">
            <div class="w-8 h-8 rounded-full bg-primary/15 flex items-center justify-center text-xs font-bold text-primary shrink-0">
              {{ r.user?.name?.[0]?.toUpperCase() ?? '?' }}
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between gap-2">
                <p class="text-xs font-semibold text-ink truncate">{{ r.user?.name }} · {{ r.product?.name }}</p>
                <div class="flex shrink-0">
                  <span v-for="s in 5" :key="s" class="text-[10px]" :class="s <= r.rating ? 'text-badge' : 'text-ink/20'">★</span>
                </div>
              </div>
              <p class="text-[11px] text-ink/55 mt-0.5 line-clamp-1">{{ r.comment }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Penjualan per Metode -->
      <div class="glass-card rounded-xl2 p-5">
        <p class="font-semibold text-ink text-sm mb-4">Penjualan per Metode Pembayaran</p>
        <div class="relative h-36 mb-3">
          <Doughnut v-if="paymentChartData" :data="paymentChartData" :options="doughnutOptions" />
          <div v-else class="h-full flex items-center justify-center text-ink/30 text-sm">Belum ada data</div>
        </div>
        <div v-for="m in stats.metode_pembayaran" :key="m.method" class="flex justify-between items-center text-xs py-1.5 border-t border-ink/5">
          <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full" :style="{ background: methodColor(m.method) }"></span>
            {{ methodLabel(m.method) }}
          </span>
          <span class="font-semibold text-ink tabular-nums">Rp {{ formatPrice(m.total) }}</span>
        </div>
      </div>
    </div>

    <!-- ===== MODAL: Tambah Produk Cepat ===== -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showQuickAdd" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" @click="closeQuickAdd"></div>
          <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">

            <div class="bg-primary px-6 py-4 flex items-center justify-between">
              <div>
                <h2 class="font-bold text-white text-lg">Tambah Produk Buah</h2>
                <p class="text-white/70 text-xs mt-0.5">Isi data pokok dulu, detail bisa dilengkapi di halaman Produk</p>
              </div>
              <button @click="closeQuickAdd" class="text-white/70 hover:text-white text-2xl font-bold leading-none cursor-pointer">×</button>
            </div>

            <form @submit.prevent="saveQuickProduct" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
              <!-- Nama + Kategori -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="label">Nama Produk *</label>
                  <input v-model="qForm.name" required placeholder="mis. Mangga Harum Manis" class="input" />
                </div>
                <div>
                  <label class="label">Kategori *</label>
                  <select v-model="qForm.category_id" required class="input">
                    <option value="" disabled>Pilih kategori</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </select>
                </div>
              </div>

              <!-- Asal + Satuan -->
              <div class="grid grid-cols-3 gap-3">
                <div>
                  <label class="label">Asal</label>
                  <select v-model="qForm.origin_type" class="input">
                    <option value="lokal">Lokal</option>
                    <option value="impor">Impor</option>
                  </select>
                </div>
                <div>
                  <label class="label">Satuan</label>
                  <select v-model="qForm.unit" class="input">
                    <option value="kg">kg</option>
                    <option value="pcs">pcs</option>
                  </select>
                </div>
                <div>
                  <label class="label">Asal Daerah</label>
                  <input v-model="qForm.origin_region" class="input" placeholder="mis. Palu" />
                </div>
              </div>

              <!-- Harga + Stok -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="label">Harga Satuan (Rp) *</label>
                  <input v-model.number="qForm.price_unit" type="number" min="0" required class="input" placeholder="25000" />
                </div>
                <div>
                  <label class="label">Stok Awal *</label>
                  <input v-model.number="qForm.stock" type="number" min="0" required class="input" placeholder="50" />
                </div>
              </div>

              <!-- Deskripsi -->
              <div>
                <label class="label">Deskripsi Singkat</label>
                <textarea v-model="qForm.description" rows="2" class="input" placeholder="Buah segar pilihan..."></textarea>
              </div>

              <!-- Foto -->
              <div>
                <label class="label">Foto Produk</label>
                <input type="file" accept="image/*" @change="onQFileChange" class="text-sm w-full" />
                <p class="text-[11px] text-ink/40 mt-1">Foto tambahan bisa diupload di halaman Kelola Produk</p>
              </div>

              <!-- Checkbox -->
              <div class="flex gap-4">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                  <input type="checkbox" v-model="qForm.is_active" class="accent-primary" /> Produk Aktif
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                  <input type="checkbox" v-model="qForm.is_featured" class="accent-primary" /> Tampil di Beranda
                </label>
              </div>

              <p v-if="qError" class="text-danger text-sm bg-danger/10 rounded-lg px-3 py-2">{{ qError }}</p>

              <div class="flex gap-2 pt-1">
                <button type="button" @click="closeQuickAdd" class="flex-1 py-2.5 text-sm text-ink/60 border border-ink/15 rounded-full hover:bg-ink/5 transition cursor-pointer">
                  Batal
                </button>
                <button type="submit" :disabled="qSaving" class="flex-1 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-dark rounded-full transition disabled:opacity-60 cursor-pointer">
                  {{ qSaving ? 'Menyimpan...' : '+ Simpan Produk' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Line, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS, Title, Tooltip, Legend, LineElement, LinearScale,
  PointElement, CategoryScale, ArcElement, Filler,
} from 'chart.js'
import api from '../../services/api'
import {
  BanknotesIcon, ShoppingCartIcon, ExclamationTriangleIcon, PlusCircleIcon,
  ClipboardDocumentListIcon, CreditCardIcon, StarIcon, CheckCircleIcon, PlusIcon,
} from '@heroicons/vue/24/outline'

ChartJS.register(Title, Tooltip, Legend, LineElement, LinearScale, PointElement, CategoryScale, ArcElement, Filler)

// ── State ─────────────────────────────────────────────────────────────────────
const stats = ref({ metode_pembayaran: [], grafik_penjualan: [] })
const recentOrders = ref([])
const allProducts = ref([])
const pendingReviews = ref([])
const categories = ref([])
const loadingOrders = ref(true)
const loadingProducts = ref(true)

// quick-add modal
const showQuickAdd = ref(false)
const qSaving = ref(false)
const qError = ref('')
const qFile = ref(null)
const qForm = ref(emptyQForm())

function emptyQForm() {
  return {
    name: '', category_id: '', origin_type: 'lokal', unit: 'kg',
    origin_region: '', price_unit: '', stock: '', description: '',
    is_active: true, is_featured: false,
  }
}

// ── Computed ──────────────────────────────────────────────────────────────────
const lowStockProducts = computed(() =>
  allProducts.value.filter(p => p.stock <= (p.min_stock_alert ?? 5)).slice(0, 6)
)

const chartData = computed(() => {
  if (!stats.value.grafik_penjualan?.length) return null
  return {
    labels: stats.value.grafik_penjualan.map(d => d.tanggal),
    datasets: [{
      label: 'Penjualan (Rp)',
      data: stats.value.grafik_penjualan.map(d => d.total),
      borderColor: '#FF5A36',
      backgroundColor: 'rgba(255,90,54,0.10)',
      tension: 0.35, fill: true, pointRadius: 3,
      pointBackgroundColor: '#FF5A36',
    }],
  }
})

const chartOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { grid: { color: '#f1f1ef' }, ticks: { font: { size: 10 } } },
    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
  },
}

const paletteMap = { qris: '#FF5A36', transfer_bank: '#123524', bayar_di_tempat: '#7CA300' }
const paymentChartData = computed(() => {
  if (!stats.value.metode_pembayaran?.length) return null
  return {
    labels: stats.value.metode_pembayaran.map(m => methodLabel(m.method)),
    datasets: [{
      data: stats.value.metode_pembayaran.map(m => m.total),
      backgroundColor: stats.value.metode_pembayaran.map(m => methodColor(m.method)),
      borderWidth: 0,
    }],
  }
})

const doughnutOptions = {
  responsive: true, maintainAspectRatio: false, cutout: '68%',
  plugins: { legend: { display: false } },
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function methodColor(m) { return paletteMap[m] || '#94a3b8' }
function methodLabel(m) {
  return { qris: 'QRIS', transfer_bank: 'Transfer Bank', bayar_di_tempat: 'Bayar di Tempat' }[m] || m
}
function formatPrice(v) { return new Intl.NumberFormat('id-ID').format(v || 0) }
function formatDate(d) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
}
function statusText(s) {
  return {
    menunggu_bayar: 'Menunggu Bayar', dikonfirmasi: 'Dikonfirmasi', diproses: 'Diproses',
    dikirim_siap_ambil: 'Dikirim', selesai: 'Selesai', dibatalkan: 'Dibatalkan',
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

// ── Quick Add Product ─────────────────────────────────────────────────────────
function closeQuickAdd() {
  showQuickAdd.value = false
  qForm.value = emptyQForm()
  qFile.value = null
  qError.value = ''
}

function onQFileChange(e) { qFile.value = e.target.files[0] || null }

async function saveQuickProduct() {
  qSaving.value = true
  qError.value = ''
  try {
    const payload = new FormData()
    Object.entries(qForm.value).forEach(([k, v]) => {
      if (v !== null && v !== undefined && v !== '') {
        payload.append(k, typeof v === 'boolean' ? (v ? 1 : 0) : v)
      }
    })
    if (qFile.value) payload.append('images[]', qFile.value)

    await api.post('/admin/products', payload, { headers: { 'Content-Type': 'multipart/form-data' } })
    closeQuickAdd()
    fetchProducts()
  } catch (err) {
    if (err.response?.status === 422) {
      const errors = err.response.data.errors || {}
      qError.value = Object.values(errors).flat().join(' ') || 'Data tidak valid.'
    } else {
      qError.value = err.response?.data?.message || 'Gagal menyimpan produk.'
    }
  } finally {
    qSaving.value = false
  }
}

// ── Fetch Data ────────────────────────────────────────────────────────────────
async function fetchStats() {
  const { data } = await api.get('/admin/dashboard')
  stats.value = data
}

async function fetchOrders() {
  loadingOrders.value = true
  try {
    const { data } = await api.get('/admin/orders', { params: { per_page: 6 } })
    recentOrders.value = data.data ?? []
  } finally {
    loadingOrders.value = false
  }
}

async function fetchProducts() {
  loadingProducts.value = true
  try {
    const { data } = await api.get('/admin/products', { params: { per_page: 100 } })
    allProducts.value = data.data ?? []
  } finally {
    loadingProducts.value = false
  }
}

async function fetchReviews() {
  try {
    const { data } = await api.get('/admin/reviews', { params: { status: 'pending', per_page: 5 } })
    pendingReviews.value = data.data ?? []
  } catch (_) {}
}

async function fetchCategories() {
  try {
    const { data } = await api.get('/admin/categories')
    categories.value = data.categories ?? []
  } catch (_) {}
}

onMounted(() => {
  fetchStats()
  fetchOrders()
  fetchProducts()
  fetchReviews()
  fetchCategories()
})
</script>

<style scoped>
.input {
  @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 bg-white;
}
.label {
  @apply text-xs font-medium text-ink/55 block mb-1;
}
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>
