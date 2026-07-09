<template>
  <div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <StatCard label="Total Penjualan" :value="`Rp ${formatPrice(stats.total_penjualan)}`" :icon="BanknotesIcon" color="primary" />
      <StatCard label="Total Pesanan" :value="stats.total_pesanan" :icon="ShoppingCartIcon" color="accent" />
      <StatCard label="Admin Toko Aktif" :value="monitoring.total_admin" :icon="UsersIcon" color="badge" />
      <StatCard label="Stok Hampir Habis" :value="stats.stok_hampir_habis" :icon="ExclamationTriangleIcon" color="danger" />
    </div>

    <!-- Grafik Pendapatan (tren penjualan) -->
    <div class="bg-white rounded-xl2 border border-ink/5 p-5 mb-4">
      <p class="font-semibold text-ink text-sm mb-4">Grafik Pendapatan (7 Hari Terakhir)</p>
      <Line v-if="revenueChartData" :data="revenueChartData" :options="lineOptions" />
    </div>

    <div class="grid md:grid-cols-2 gap-4 mb-6">
      <div class="bg-white rounded-xl2 border border-ink/5 p-5">
        <p class="font-semibold text-ink text-sm mb-4">Statistik Sistem</p>
        <div class="space-y-2.5 text-sm">
          <div class="flex justify-between"><span class="text-ink/55">Total Pengguna</span><span class="font-medium text-ink">{{ monitoring.total_pengguna }}</span></div>
          <div class="flex justify-between"><span class="text-ink/55">Pengguna Aktif (24 jam)</span><span class="font-medium text-ink">{{ monitoring.pengguna_aktif_24jam }}</span></div>
          <div class="flex justify-between"><span class="text-ink/55">Notifikasi Terkirim Hari Ini</span><span class="font-medium text-ink">{{ monitoring.notifikasi_terkirim_hari_ini }}</span></div>
          <div class="flex justify-between"><span class="text-ink/55">Notifikasi Gagal Hari Ini</span><span class="font-medium text-danger">{{ monitoring.notifikasi_gagal_hari_ini }}</span></div>
        </div>
      </div>

      <!-- Diagram Penjualan (doughnut per metode bayar) -->
      <div class="bg-white rounded-xl2 border border-ink/5 p-5">
        <p class="font-semibold text-ink text-sm mb-4">Diagram Penjualan per Metode Bayar</p>
        <div class="grid grid-cols-2 gap-4 items-center">
          <div class="relative h-32">
            <Doughnut v-if="paymentChartData" :data="paymentChartData" :options="doughnutOptions" />
          </div>
          <div>
            <div v-for="m in stats.metode_pembayaran" :key="m.method" class="flex items-center gap-2 text-xs py-1">
              <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: methodColor(m.method) }"></span>
              <span class="flex-1 text-ink/60">{{ methodLabel(m.method) }}</span>
              <span class="font-medium text-ink">Rp {{ formatPrice(m.total) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <QuickLink to="/superadmin/admin-toko" :icon="UserCircleIcon" label="Kelola Admin" desc="Tambah/nonaktifkan admin" />
      <QuickLink to="/superadmin/audit-trail-pelanggan" :icon="MagnifyingGlassCircleIcon" label="Audit Trail" desc="Cek aktivitas pelanggan" />
      <QuickLink to="/superadmin/wilayah-global" :icon="GlobeAltIcon" label="Wilayah Global" desc="Atur layanan delivery" />
      <QuickLink to="/superadmin/backup-database" :icon="CircleStackIcon" label="Backup DB" desc="Backup/restore data" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Line, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS, Title, Tooltip, Legend, LineElement, LinearScale, PointElement,
  CategoryScale, ArcElement,
} from 'chart.js'
import api from '../../services/api'
import StatCard from '../../components/admin/StatCard.vue'
import QuickLink from '../../components/admin/QuickLink.vue'
import {
  BanknotesIcon, ShoppingCartIcon, UsersIcon, ExclamationTriangleIcon,
  UserCircleIcon, MagnifyingGlassCircleIcon, GlobeAltIcon, CircleStackIcon,
} from '@heroicons/vue/24/outline'

ChartJS.register(Title, Tooltip, Legend, LineElement, LinearScale, PointElement, CategoryScale, ArcElement)

const stats = ref({ metode_pembayaran: [], grafik_penjualan: [] })
const monitoring = ref({})

const revenueChartData = computed(() => {
  if (!stats.value.grafik_penjualan?.length) return null
  return {
    labels: stats.value.grafik_penjualan.map((d) => d.tanggal),
    datasets: [{
      label: 'Pendapatan (Rp)',
      data: stats.value.grafik_penjualan.map((d) => d.total),
      borderColor: '#2E7D32',
      backgroundColor: 'rgba(46,125,50,0.08)',
      tension: 0.35,
      fill: true,
      pointRadius: 3,
      pointBackgroundColor: '#2E7D32',
    }],
  }
})

const lineOptions = {
  responsive: true,
  plugins: { legend: { display: false } },
  scales: { y: { grid: { color: '#f1f1ef' } }, x: { grid: { display: false } } },
}

const paletteMap = { qris: '#2E7D32', transfer_bank: '#FB8C00', bayar_di_tempat: '#FFC107' }
function methodColor(m) { return paletteMap[m] || '#94a3b8' }
function methodLabel(m) { return { qris: 'QRIS', transfer_bank: 'Transfer Bank', bayar_di_tempat: 'Bayar di Tempat' }[m] || m }

const paymentChartData = computed(() => {
  if (!stats.value.metode_pembayaran?.length) return null
  return {
    labels: stats.value.metode_pembayaran.map((m) => methodLabel(m.method)),
    datasets: [{
      data: stats.value.metode_pembayaran.map((m) => m.total),
      backgroundColor: stats.value.metode_pembayaran.map((m) => methodColor(m.method)),
      borderWidth: 0,
    }],
  }
})

const doughnutOptions = { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false } } }

function formatPrice(v) { return new Intl.NumberFormat('id-ID').format(v || 0) }

onMounted(async () => {
  const [dashRes, monRes] = await Promise.all([
    api.get('/admin/dashboard'),
    api.get('/superadmin/monitoring'),
  ])
  stats.value = dashRes.data
  monitoring.value = monRes.data
})
</script>
