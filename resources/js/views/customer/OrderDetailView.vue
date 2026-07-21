<template>
  <div v-if="order" class="max-w-3xl mx-auto px-4 py-8">
    <router-link to="/pesanan" class="inline-flex items-center gap-1.5 text-primary text-sm hover:underline cursor-pointer">
      <ArrowLeftIcon class="w-3.5 h-3.5" stroke-width="1.75" /> Kembali ke Pesanan Saya
    </router-link>

    <div class="glass-card rounded-xl2 p-6 mt-4">
      <div class="flex justify-between items-start mb-6">
        <div>
          <p class="text-xs text-ink/50">No. Invoice</p>
          <p class="font-bold text-lg text-ink">{{ order.order_number }}</p>
        </div>
        <span class="text-xs font-semibold px-3 py-1.5 rounded-full" :class="statusClass(order.status)">
          {{ statusText(order.status) }}
        </span>
      </div>

      <!-- Timeline Status -->
      <div class="flex justify-between mb-8 relative">
        <div class="absolute top-3 left-0 right-0 h-0.5 bg-ink/10 -z-10"></div>
        <div
          v-for="(label, key, idx) in timeline"
          :key="key"
          class="flex flex-col items-center text-center w-1/5"
        >
          <div
            class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
            :class="isStepDone(key) ? 'bg-primary text-white' : 'bg-white border-2 border-ink/20 text-ink/30'"
          >
            <CheckIcon v-if="isStepDone(key)" class="w-3.5 h-3.5" stroke-width="2.5" />
            <span v-else>{{ idx + 1 }}</span>
          </div>
          <span class="text-[11px] mt-1.5 text-ink/60">{{ label }}</span>
        </div>
      </div>

      <!-- Info Pengiriman -->
      <div class="grid grid-cols-2 gap-4 text-sm mb-6">
        <div>
          <p class="text-ink/50">Cara Terima</p>
          <p class="font-medium flex items-center gap-1.5">
            <TruckIcon v-if="order.fulfillment_type === 'delivery'" class="w-4 h-4 text-primary" stroke-width="1.75" />
            <BuildingStorefrontIcon v-else class="w-4 h-4 text-primary" stroke-width="1.75" />
            {{ order.fulfillment_type === 'delivery' ? 'Delivery' : 'Pickup' }}
          </p>
        </div>
        <div>
          <p class="text-ink/50">Jadwal</p>
          <p class="font-medium">{{ formatDate(order.scheduled_date) }} · {{ order.delivery_slot?.name }}</p>
        </div>
        <div v-if="order.address">
          <p class="text-ink/50">Alamat</p>
          <p class="font-medium">{{ order.address.full_address }}</p>
        </div>
        <div>
          <p class="text-ink/50">Metode Bayar</p>
          <p class="font-medium">{{ paymentMethodText(order.payment?.method) }}</p>
        </div>
      </div>

      <!-- Instruksi Pembayaran QRIS (otomatis via Midtrans Snap) -->
      <div v-if="order.payment?.method === 'qris' && order.payment?.status === 'pending'" class="glass-card-soft rounded-xl2 p-5 mb-6 text-center">
        <p class="text-sm font-medium mb-3 flex items-center justify-center gap-1.5">
          <QrCodeIcon class="w-4 h-4 text-primary" stroke-width="1.75" /> Scan QRIS untuk membayar
          <span class="font-bold text-primary tabular-nums">Rp {{ formatPrice(order.total) }}</span>
        </p>

        <div v-if="snapError" class="text-sm text-danger bg-danger/10 rounded-lg p-3">{{ snapError }}</div>
        <div v-else-if="loadingSnap" class="text-sm text-ink/50 py-8">Menyiapkan kode QRIS...</div>
        <div v-else id="midtrans-snap-container" class="min-h-[300px] flex justify-center"></div>

        <p class="text-xs text-ink/50 mt-3">Pembayaran akan terverifikasi otomatis begitu Anda selesai scan & bayar.</p>
      </div>

      <!-- Instruksi Transfer Bank -->
      <div v-if="order.payment?.method === 'transfer_bank' && order.payment?.status === 'pending' && !order.payment?.proof_image" class="glass-card-soft rounded-xl2 p-4 mb-6">
        <p class="text-sm font-medium mb-3 flex items-center gap-1.5">
          <BuildingLibraryIcon class="w-4 h-4 text-primary" stroke-width="1.75" /> Transfer
          <span class="font-bold text-primary tabular-nums">Rp {{ formatPrice(order.total) }}</span> ke salah satu rekening berikut:
        </p>
        <div v-if="storeInfo.bankAccounts?.length" class="space-y-2 mb-3">
          <div v-for="(bank, i) in storeInfo.bankAccounts" :key="i" class="bg-white rounded-lg p-3 text-sm border border-ink/10">
            <p class="font-semibold">{{ bank.bank }}</p>
            <p class="text-ink/70">{{ bank.no_rek }} a.n. {{ bank.atas_nama }}</p>
          </div>
        </div>
      </div>

      <!-- Upload Bukti Transfer -->
      <div v-if="order.payment?.method === 'transfer_bank' && order.payment?.status === 'pending' && !order.payment?.proof_image" class="bg-warning/10 border border-warning/30 rounded-xl2 p-4 mb-6">
        <p class="text-sm font-medium mb-2 flex items-center gap-1.5"><ArrowUpTrayIcon class="w-4 h-4 text-warning" stroke-width="1.75" /> Unggah Bukti Transfer</p>
        <input type="file" @change="uploadProof" accept="image/*" class="text-sm cursor-pointer" />
      </div>

      <!-- Item Pesanan -->
      <p class="font-semibold text-ink mb-3">Item Pesanan</p>
      <div class="space-y-3 mb-4">
        <div v-for="item in order.items" :key="item.id" class="border-b border-ink/5 pb-3">
          <div class="flex justify-between text-sm">
            <span>{{ item.item_name }} × {{ item.qty }}</span>
            <span class="font-medium tabular-nums">Rp {{ formatPrice(item.price * item.qty) }}</span>
          </div>

          <!-- Form Ulasan & Rating (fitur A.11) - tampil setelah pesanan selesai -->
          <div v-if="order.status === 'selesai' && !item.is_reviewed" class="mt-2 bg-primary/5 rounded-lg p-3">
            <p class="text-xs font-medium mb-2">Bagaimana kualitas buah ini?</p>
            <div class="flex gap-1 mb-2">
              <button
                v-for="star in 5" :key="star"
                @click="reviewForms[item.id] = { ...reviewForms[item.id], rating: star }"
                class="cursor-pointer"
              >
                <StarIconSolid v-if="(reviewForms[item.id]?.rating || 0) >= star" class="w-5 h-5 text-badge" />
                <StarIcon v-else class="w-5 h-5 text-ink/30" stroke-width="1.5" />
              </button>
            </div>
            <textarea
              v-model="reviewForms[item.id].comment"
              rows="2"
              placeholder="Ceritakan pengalaman Anda (opsional)"
              class="w-full bg-white border border-ink/10 rounded-lg px-3 py-2 text-xs mb-2"
            ></textarea>
            <button
              @click="submitReview(item)"
              :disabled="!reviewForms[item.id]?.rating"
              class="bg-accent text-white text-xs font-semibold px-4 py-1.5 rounded-full disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
            >
              Kirim Ulasan
            </button>
          </div>
          <p v-else-if="order.status === 'selesai' && item.is_reviewed" class="text-xs text-success mt-1 flex items-center gap-1">
            <CheckCircleIcon class="w-3.5 h-3.5" stroke-width="1.75" /> Sudah diulas, terima kasih!
          </p>
        </div>
      </div>

      <div class="border-t border-ink/10 pt-3 space-y-1 text-sm">
        <div class="flex justify-between"><span class="text-ink/60">Subtotal</span><span class="tabular-nums">Rp {{ formatPrice(order.subtotal) }}</span></div>
        <div class="flex justify-between"><span class="text-ink/60">Ongkos Kirim</span><span class="tabular-nums">Rp {{ formatPrice(order.shipping_cost) }}</span></div>
        <div class="flex justify-between font-bold text-ink text-base"><span>Total</span><span class="tabular-nums">Rp {{ formatPrice(order.total) }}</span></div>
      </div>
    </div>
  </div>

  <div v-else class="text-center py-16 text-ink/50">Memuat pesanan...</div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'
import { useStoreInfoStore } from '../../stores/store'
import {
  ArrowLeftIcon,
  CheckIcon,
  TruckIcon,
  BuildingStorefrontIcon,
  QrCodeIcon,
  BuildingLibraryIcon,
  ArrowUpTrayIcon,
  StarIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid'

const route = useRoute()
const storeInfo = useStoreInfoStore()
const order = ref(null)
const timeline = ref({})
const reviewForms = ref({})
const loadingSnap = ref(true)
const snapError = ref('')

const statusOrder = ['menunggu_bayar', 'dikonfirmasi', 'diproses', 'dikirim_siap_ambil', 'selesai']

async function fetchOrder() {
  const { data } = await api.get(`/orders/${route.params.id}`)
  order.value = data.order
  timeline.value = data.timeline
  data.order.items.forEach((item) => {
    if (!reviewForms.value[item.id]) reviewForms.value[item.id] = { rating: 0, comment: '' }
  })

  if (data.order.payment?.method === 'qris' && data.order.payment?.status === 'pending') {
    initMidtransSnap()
  }
}

// Muat Snap.js Midtrans sekali saja, lalu embed widget QRIS langsung di
// halaman ini (bukan popup) supaya menyatu dengan tampilan struk.
function loadSnapScript() {
  return new Promise((resolve, reject) => {
    if (window.snap) return resolve()
    const existing = document.getElementById('midtrans-snap-js')
    if (existing) {
      existing.addEventListener('load', resolve)
      existing.addEventListener('error', reject)
      return
    }
    const script = document.createElement('script')
    script.id = 'midtrans-snap-js'
    script.src = import.meta.env.VITE_MIDTRANS_IS_PRODUCTION === 'true'
      ? 'https://app.midtrans.com/snap/snap.js'
      : 'https://app.sandbox.midtrans.com/snap/snap.js'
    script.setAttribute('data-client-key', import.meta.env.VITE_MIDTRANS_CLIENT_KEY || '')
    script.onload = resolve
    script.onerror = reject
    document.head.appendChild(script)
  })
}

async function initMidtransSnap() {
  loadingSnap.value = true
  snapError.value = ''
  try {
    const [{ data }] = await Promise.all([
      api.get(`/orders/${order.value.id}/snap-token`),
      loadSnapScript(),
    ])
    // Matikan status loading DULU supaya div #midtrans-snap-container
    // benar-benar dirender Vue, baru setelah itu panggil snap.embed - kalau
    // dipanggil sebelum nextTick, elemennya belum ada di DOM dan embed gagal senyap.
    loadingSnap.value = false
    await nextTick()
    window.snap.embed(data.snap_token, {
      embedId: 'midtrans-snap-container',
      onSuccess: fetchOrder,
      onPending: () => {},
      onError: () => { snapError.value = 'Gagal memuat pembayaran QRIS. Coba muat ulang halaman.' },
    })
  } catch (e) {
    snapError.value = e.response?.data?.message || 'QRIS belum bisa dimuat. Coba muat ulang halaman atau hubungi admin toko.'
    loadingSnap.value = false
  }
}

async function submitReview(item) {
  const form = reviewForms.value[item.id]
  await api.post('/reviews', { order_item_id: item.id, rating: form.rating, comment: form.comment })
  item.is_reviewed = true
  alert('Terima kasih atas ulasan Anda!')
}

function isStepDone(key) {
  return statusOrder.indexOf(order.value.status) >= statusOrder.indexOf(key)
}

async function uploadProof(e) {
  const file = e.target.files[0]
  if (!file) return
  const formData = new FormData()
  formData.append('proof_image', file)
  await api.post(`/orders/${order.value.id}/payment-proof`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  alert('Bukti transfer berhasil diunggah!')
  fetchOrder()
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}

function statusText(s) {
  return {
    menunggu_bayar: 'Menunggu Bayar', dikonfirmasi: 'Dikonfirmasi', diproses: 'Diproses',
    dikirim_siap_ambil: 'Dikirim/Siap Ambil', selesai: 'Selesai', dibatalkan: 'Dibatalkan',
  }[s] || s
}

function statusClass(s) {
  return {
    menunggu_bayar: 'bg-warning/15 text-warning', dikonfirmasi: 'bg-primary/15 text-primary',
    diproses: 'bg-badge/20 text-accent', dikirim_siap_ambil: 'bg-success/15 text-success',
    selesai: 'bg-success/25 text-success', dibatalkan: 'bg-danger/15 text-danger',
  }[s] || 'bg-ink/10 text-ink/60'
}

function paymentMethodText(m) {
  return { qris: 'QRIS', transfer_bank: 'Transfer Bank', bayar_di_tempat: 'Bayar di Tempat' }[m] || '-'
}

onMounted(() => {
  fetchOrder()
  storeInfo.fetchStoreInfo()
})
</script>
