<template>
  <div>
    <!-- Kartu Statistik -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <StatCard label="Total Penjualan" :value="`Rp ${formatPrice(stats.total_penjualan)}`" :icon="BanknotesIcon" color="primary" />
      <StatCard label="Total Pesanan" :value="stats.total_pesanan" :icon="ShoppingCartIcon" color="accent" />
      <StatCard label="Pesanan Diproses" :value="stats.pesanan_diproses" :icon="ClockIcon" color="badge" />
      <StatCard label="Stok Hampir Habis" :value="stats.stok_hampir_habis" :icon="ExclamationTriangleIcon" color="danger" />
    </div>

    <div class="grid md:grid-cols-3 gap-4">
      <!-- Grafik Tren -->
      <div class="md:col-span-2 bg-white rounded-xl2 border border-ink/5 p-5">
        <p class="font-semibold text-ink text-sm mb-4">Grafik Penjualan (7 Hari Terakhir)</p>
        <Line v-if="chartData" :data="chartData" :options="chartOptions" />
      </div>

      <!-- Metode Pembayaran - Doughnut Chart -->
      <div class="bg-white rounded-xl2 border border-ink/5 p-5">
        <p class="font-semibold text-ink text-sm mb-4">Penjualan per Metode</p>
        <div class="relative h-40 mb-4">
          <Doughnut v-if="paymentChartData" :data="paymentChartData" :options="doughnutOptions" />
        </div>
        <div v-for="m in stats.metode_pembayaran" :key="m.method" class="flex justify-between items-center text-xs py-1.5 border-t border-ink/5">
          <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full" :style="{ background: methodColor(m.method) }"></span>
            {{ methodLabel(m.method) }}
          </span>
          <span class="font-medium text-ink">Rp {{ formatPrice(m.total) }}</span>
        </div>
      </div>
    </div>

    <!-- Akses Cepat -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
      <QuickLink to="/admin/produk" :icon="ShoppingBagIcon" label="Kelola Produk" desc="Tambah, edit, hapus produk" />
      <QuickLink to="/admin/pesanan" :icon="ClipboardDocumentListIcon" label="Pesanan Masuk" desc="Lihat & proses pesanan" />
      <QuickLink to="/admin/pembayaran" :icon="CreditCardIcon" label="Verifikasi Bayar" desc="Cek bukti transfer" />
      <QuickLink to="/admin/laporan" :icon="ChartBarIcon" label="Lihat Laporan" desc="Analisis performa toko" />
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
  BanknotesIcon, ShoppingCartIcon, ClockIcon, ExclamationTriangleIcon,
  ShoppingBagIcon, ClipboardDocumentListIcon, CreditCardIcon, ChartBarIcon,
} from '@heroicons/vue/24/outline'

ChartJS.register(Title, Tooltip, Legend, LineElement, LinearScale, PointElement, CategoryScale, ArcElement)

const stats = ref({ metode_pembayaran: [], grafik_penjualan: [] })

const chartData = computed(() => {
  if (!stats.value.grafik_penjualan?.length) return null
  return {
    labels: stats.value.grafik_penjualan.map((d) => d.tanggal),
    datasets: [{
      label: 'Penjualan (Rp)',
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

const chartOptions = {
  responsive: true,
  plugins: { legend: { display: false } },
  scales: { y: { grid: { color: '#f1f1ef' } }, x: { grid: { display: false } } },
}

const paletteMap = { qris: '#2E7D32', transfer_bank: '#FB8C00', bayar_di_tempat: '#FFC107' }
function methodColor(m) { return paletteMap[m] || '#94a3b8' }

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

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '70%',
  plugins: { legend: { display: false } },
}

function methodLabel(m) {
  return { qris: 'QRIS', transfer_bank: 'Transfer Bank', bayar_di_tempat: 'Bayar di Tempat' }[m] || m
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v || 0)
}

onMounted(async () => {
  const { data } = await api.get('/admin/dashboard')
  stats.value = data
})
</script>
