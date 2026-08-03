<template>
  <div>
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="font-bold text-ink text-lg">Pesanan Masuk</h2>
        <p class="text-xs text-ink/45 mt-0.5">Ikuti alurnya dari atas ke bawah — satu tombol untuk satu langkah berikutnya</p>
      </div>
    </div>

    <!-- Filter Tab -->
    <div class="flex gap-2 mb-5 overflow-x-auto pb-1">
      <button
        v-for="tab in tabs" :key="tab.key"
        @click="activeTab = tab.key; fetchOrders()"
        class="shrink-0 text-xs font-semibold px-4 py-2 rounded-full border transition cursor-pointer"
        :class="activeTab === tab.key
          ? 'bg-primary text-white border-primary'
          : 'bg-white text-ink/60 border-ink/15 hover:border-primary/40'"
      >
        {{ tab.label }}
        <span v-if="tab.count > 0" class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full" :class="activeTab === tab.key ? 'bg-white/25 text-white' : 'bg-ink/8 text-ink/50'">
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="glass-card rounded-xl2 p-4 animate-pulse">
        <div class="h-3.5 bg-ink/10 rounded w-40 mb-3"></div>
        <div class="h-3 bg-ink/10 rounded w-64"></div>
      </div>
    </div>

    <!-- Kosong -->
    <div v-else-if="orders.length === 0" class="glass-card rounded-xl2 p-10 text-center text-ink/40">
      <InboxIcon class="w-10 h-10 mx-auto mb-2 text-ink/20" stroke-width="1.5" />
      Tidak ada pesanan di kategori ini
    </div>

    <!-- Daftar Pesanan -->
    <div v-else class="space-y-3">
      <div
        v-for="o in orders" :key="o.id"
        class="glass-card rounded-xl2 overflow-hidden"
      >
        <div class="h-1 w-full" :class="stripeClass(o.status)"></div>

        <div class="p-4">
          <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
            <div>
              <div class="flex items-center gap-2 mb-0.5">
                <p class="font-bold text-ink text-sm">{{ o.order_number }}</p>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full" :class="statusClass(o.status)">
                  {{ statusText(o.status) }}
                </span>
              </div>
              <p class="text-xs text-ink/50">{{ o.user?.name }} · {{ formatDate(o.created_at) }}</p>
            </div>
            <span class="font-extrabold text-ink tabular-nums">Rp {{ formatPrice(o.total) }}</span>
          </div>

          <div class="flex items-center gap-2 text-xs text-ink/55 mb-4">
            <ShoppingBagIcon class="w-3.5 h-3.5 shrink-0" stroke-width="2" />
            <span>{{ o.items?.length ?? 0 }} item</span>
            <span class="text-ink/20">·</span>
            <component :is="o.fulfillment_type === 'delivery' ? TruckIcon : BuildingStorefrontIcon" class="w-3.5 h-3.5 shrink-0" stroke-width="2" />
            <span>{{ o.fulfillment_type === 'delivery' ? 'Delivery' : 'Pickup' }}</span>
            <template v-if="o.payment?.method">
              <span class="text-ink/20">·</span>
              <span class="uppercase">{{ paymentMethodText(o.payment.method) }}</span>
            </template>
          </div>

          <!-- Bukti transfer perlu diverifikasi -->
          <div
            v-if="o.status === 'menunggu_bayar' && o.payment?.method === 'transfer_bank' && o.payment?.proof_image"
            class="flex items-center gap-3 bg-warning/8 border border-warning/20 rounded-xl p-2.5 mb-3"
          >
            <img
              :src="`/storage/${o.payment.proof_image}`"
              alt="Bukti transfer"
              class="w-11 h-11 rounded-lg object-cover shrink-0 cursor-pointer ring-1 ring-ink/10"
              @click="openDetail(o)"
            />
            <p class="text-xs text-ink/70 flex-1">Bukti transfer sudah diunggah, perlu diverifikasi sebelum dikonfirmasi.</p>
          </div>
          <div
            v-else-if="o.status === 'menunggu_bayar' && o.payment?.method === 'transfer_bank'"
            class="flex items-center gap-2 bg-ink/5 rounded-xl p-2.5 mb-3 text-xs text-ink/50"
          >
            <ClockIcon class="w-4 h-4 shrink-0" stroke-width="1.75" />
            Menunggu pelanggan mengunggah bukti transfer.
          </div>

          <!-- Baris aksi -->
          <div class="flex items-center justify-between gap-2 pt-3 border-t border-ink/8">
            <div class="flex items-center gap-2">
              <button
                v-if="primaryAction(o)"
                @click="handlePrimaryAction(o)"
                :disabled="actionLoadingId === o.id"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-white px-4 py-2 rounded-full transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                :class="primaryAction(o).danger ? 'bg-danger hover:bg-red-700' : 'bg-primary hover:bg-primary-light'"
              >
                <component :is="primaryAction(o).icon" class="w-3.5 h-3.5" stroke-width="2.25" />
                {{ actionLoadingId === o.id ? 'Memproses...' : primaryAction(o).label }}
              </button>
              <button
                v-if="canCancel(o.status)"
                @click="cancelOrder(o)"
                :disabled="actionLoadingId === o.id"
                class="text-[11px] font-semibold text-danger/70 hover:text-danger px-2 cursor-pointer disabled:opacity-40"
              >
                Batalkan
              </button>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <ActionButton variant="primary" @click="openDetail(o)">Detail</ActionButton>
              <ActionButton variant="primary" @click="printInvoice(o)">
                <span class="inline-flex items-center gap-1"><PrinterIcon class="w-3.5 h-3.5" stroke-width="1.75" /> Invoice</span>
              </ActionButton>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== Modal Detail Pesanan ===== -->
    <Teleport to="body">
      <div v-if="detailOrder" class="fixed inset-0 z-[100] flex items-center justify-center p-4" @click.self="closeDetail">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-lg max-h-[85vh] overflow-y-auto bg-white rounded-2xl shadow-2xl">
          <div class="sticky top-0 bg-white border-b border-ink/8 px-5 py-4 flex items-center justify-between">
            <div>
              <p class="font-bold text-ink text-sm">{{ detailOrder.order_number }}</p>
              <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full inline-block mt-1" :class="statusClass(detailOrder.status)">
                {{ statusText(detailOrder.status) }}
              </span>
            </div>
            <button @click="closeDetail" class="text-ink/40 hover:text-ink cursor-pointer">
              <XMarkIcon class="w-5 h-5" stroke-width="2" />
            </button>
          </div>

          <div v-if="detailLoading" class="p-8 text-center text-ink/40 text-sm">Memuat detail...</div>

          <div v-else class="p-5 space-y-5">
            <!-- Pelanggan & Pengiriman -->
            <div>
              <p class="text-xs font-semibold text-ink/50 uppercase tracking-wide mb-2">Pelanggan</p>
              <p class="text-sm text-ink font-medium">{{ detailOrder.user?.name }}</p>
              <p class="text-xs text-ink/50">{{ detailOrder.user?.phone }}</p>
            </div>

            <div>
              <p class="text-xs font-semibold text-ink/50 uppercase tracking-wide mb-2">
                {{ detailOrder.fulfillment_type === 'delivery' ? 'Alamat Pengiriman' : 'Pickup di Toko' }}
              </p>
              <template v-if="detailOrder.fulfillment_type === 'delivery'">
                <p class="text-sm text-ink font-medium">{{ detailOrder.address?.recipient_name }} · {{ detailOrder.address?.phone }}</p>
                <p class="text-sm text-ink/70 leading-relaxed">{{ detailOrder.address?.full_address || '-' }}</p>
              </template>
              <p v-else class="text-sm text-ink/70">Diambil langsung oleh pelanggan</p>
              <p class="text-xs text-ink/45 mt-1">
                Jadwal: {{ formatDate(detailOrder.scheduled_date) }}
                <template v-if="detailOrder.delivery_slot">· {{ detailOrder.delivery_slot.start_time }}–{{ detailOrder.delivery_slot.end_time }}</template>
              </p>
            </div>

            <!-- Item -->
            <div>
              <p class="text-xs font-semibold text-ink/50 uppercase tracking-wide mb-2">Item Pesanan</p>
              <div class="space-y-1.5">
                <div v-for="item in detailOrder.items" :key="item.id" class="flex justify-between text-sm">
                  <span class="text-ink/75">{{ item.item_name }} <span class="text-ink/40">×{{ item.qty }}</span></span>
                  <span class="text-ink/75 tabular-nums">Rp {{ formatPrice(item.price * item.qty) }}</span>
                </div>
              </div>
              <div class="flex justify-between text-sm font-bold text-ink border-t border-ink/8 mt-2 pt-2">
                <span>Total</span>
                <span class="tabular-nums">Rp {{ formatPrice(detailOrder.total) }}</span>
              </div>
            </div>

            <!-- Pembayaran -->
            <div v-if="detailOrder.payment">
              <p class="text-xs font-semibold text-ink/50 uppercase tracking-wide mb-2">Pembayaran</p>
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-ink/70">{{ paymentMethodText(detailOrder.payment.method) }}</span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full" :class="paymentStatusClass(detailOrder.payment.status)">
                  {{ paymentStatusText(detailOrder.payment.status) }}
                </span>
              </div>

              <img
                v-if="detailOrder.payment.proof_image"
                :src="`/storage/${detailOrder.payment.proof_image}`"
                alt="Bukti transfer"
                class="w-full rounded-xl border border-ink/10 mb-3"
              />

              <div v-if="detailOrder.payment.method === 'transfer_bank' && detailOrder.payment.status === 'pending'" class="flex gap-2">
                <button
                  @click="confirmPayment(detailOrder.payment)"
                  :disabled="paymentActionLoading"
                  class="flex-1 bg-success text-white text-sm font-bold py-2.5 rounded-full hover:opacity-90 transition cursor-pointer disabled:opacity-50"
                >
                  Konfirmasi Pembayaran
                </button>
                <button
                  @click="rejectPayment(detailOrder.payment)"
                  :disabled="paymentActionLoading"
                  class="flex-1 bg-danger/10 text-danger text-sm font-bold py-2.5 rounded-full hover:bg-danger/20 transition cursor-pointer disabled:opacity-50"
                >
                  Tolak
                </button>
              </div>
            </div>

            <!-- Aksi lanjutan -->
            <div v-if="primaryAction(detailOrder)" class="pt-2">
              <button
                @click="handlePrimaryAction(detailOrder)"
                :disabled="actionLoadingId === detailOrder.id"
                class="w-full inline-flex items-center justify-center gap-1.5 text-sm font-bold text-white py-2.5 rounded-full transition cursor-pointer disabled:opacity-50"
                :class="primaryAction(detailOrder).danger ? 'bg-danger hover:bg-red-700' : 'bg-primary hover:bg-primary-light'"
              >
                <component :is="primaryAction(detailOrder).icon" class="w-4 h-4" stroke-width="2.25" />
                {{ actionLoadingId === detailOrder.id ? 'Memproses...' : primaryAction(detailOrder).label }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import ActionButton from '../../components/shared/ActionButton.vue'
import {
  PrinterIcon,
  InboxIcon,
  ShoppingBagIcon,
  TruckIcon,
  BuildingStorefrontIcon,
  ClockIcon,
  XMarkIcon,
  CheckCircleIcon,
  ArrowRightCircleIcon,
} from '@heroicons/vue/24/outline'

const orders = ref([])
const loading = ref(true)
const activeTab = ref('')
const actionLoadingId = ref(null)
const detailOrder = ref(null)
const detailLoading = ref(false)
const paymentActionLoading = ref(false)

const tabs = computed(() => [
  { key: '', label: 'Semua', count: orders.value.length },
  { key: 'menunggu_bayar', label: 'Menunggu Bayar', count: countByStatus('menunggu_bayar') },
  { key: 'dikonfirmasi', label: 'Dikonfirmasi', count: countByStatus('dikonfirmasi') },
  { key: 'diproses', label: 'Diproses', count: countByStatus('diproses') },
  { key: 'dikirim_siap_ambil', label: 'Dikirim/Siap Ambil', count: countByStatus('dikirim_siap_ambil') },
  { key: 'selesai', label: 'Selesai', count: countByStatus('selesai') },
  { key: 'dibatalkan', label: 'Dibatalkan', count: countByStatus('dibatalkan') },
])

// Dipakai untuk badge hitungan tab - dihitung dari daftar "semua" yang
// sudah pernah dimuat, supaya tidak perlu 6x request terpisah ke server.
const allOrdersCache = ref([])
function countByStatus(status) {
  return allOrdersCache.value.filter((o) => o.status === status).length
}

async function fetchOrders() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/orders', { params: { status: activeTab.value, per_page: 100 } })
    orders.value = data.data
    if (!activeTab.value) allOrdersCache.value = data.data
  } finally {
    loading.value = false
  }
}

// ── Langkah berikutnya (satu tombol per status, bukan pilihan bebas) ────────
function primaryAction(o) {
  const isDelivery = o.fulfillment_type === 'delivery'
  const map = {
    dikonfirmasi: { label: 'Proses Pesanan', target: 'diproses', icon: ArrowRightCircleIcon },
    diproses: { label: isDelivery ? 'Tandai Dikirim' : 'Tandai Siap Diambil', target: 'dikirim_siap_ambil', icon: TruckIcon },
    dikirim_siap_ambil: { label: 'Selesaikan Pesanan', target: 'selesai', icon: CheckCircleIcon },
  }
  return map[o.status] ?? null
}

function canCancel(status) {
  return ['menunggu_bayar', 'dikonfirmasi', 'diproses'].includes(status)
}

async function handlePrimaryAction(o) {
  const action = primaryAction(o)
  if (!action) return
  await changeStatus(o, action.target)
}

async function cancelOrder(o) {
  if (!confirm(`Batalkan pesanan ${o.order_number}? Tindakan ini akan memberi tahu pelanggan.`)) return
  await changeStatus(o, 'dibatalkan')
}

async function changeStatus(o, status) {
  actionLoadingId.value = o.id
  try {
    await api.patch(`/admin/orders/${o.id}/status`, { status })
    await fetchOrders()
    if (detailOrder.value?.id === o.id) await openDetail({ id: o.id }, true)
  } finally {
    actionLoadingId.value = null
  }
}

// ── Detail pesanan ───────────────────────────────────────────────────────────
async function openDetail(o, silent = false) {
  if (!silent) {
    detailOrder.value = {}
    detailLoading.value = true
  }
  try {
    const { data } = await api.get(`/admin/orders/${o.id}`)
    detailOrder.value = data.order
  } finally {
    detailLoading.value = false
  }
}

function closeDetail() {
  detailOrder.value = null
}

// ── Verifikasi pembayaran manual (transfer bank) ─────────────────────────────
async function confirmPayment(payment) {
  paymentActionLoading.value = true
  try {
    await api.patch(`/admin/payments/${payment.id}/status`, { status: 'confirmed' })
    await openDetail({ id: detailOrder.value.id }, true)
    await fetchOrders()
  } finally {
    paymentActionLoading.value = false
  }
}

async function rejectPayment(payment) {
  if (!confirm('Tolak bukti transfer ini? Pelanggan perlu mengunggah ulang.')) return
  paymentActionLoading.value = true
  try {
    await api.patch(`/admin/payments/${payment.id}/status`, { status: 'rejected' })
    await openDetail({ id: detailOrder.value.id }, true)
    await fetchOrders()
  } finally {
    paymentActionLoading.value = false
  }
}

function printInvoice(o) {
  window.open(`/api/admin/orders/${o.id}/invoice`, '_blank')
}

// ── Label & warna ─────────────────────────────────────────────────────────────
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

function paymentMethodText(m) {
  return { qris: 'QRIS', transfer_bank: 'Transfer Bank', bayar_di_tempat: 'Bayar di Tempat' }[m] || m
}

function paymentStatusText(s) {
  return { pending: 'Menunggu', confirmed: 'Terkonfirmasi', rejected: 'Ditolak' }[s] || s
}

function paymentStatusClass(s) {
  return {
    pending: 'bg-warning/15 text-warning',
    confirmed: 'bg-success/15 text-success',
    rejected: 'bg-danger/15 text-danger',
  }[s] || 'bg-ink/10 text-ink/60'
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v || 0)
}

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(fetchOrders)
</script>
