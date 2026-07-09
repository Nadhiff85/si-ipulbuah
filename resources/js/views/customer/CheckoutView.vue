<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-ink mb-6">Checkout</h1>

    <div class="grid md:grid-cols-3 gap-6">
      <div class="md:col-span-2 space-y-5">
        <!-- Step 1: Metode Pengiriman -->
        <div class="bg-white rounded-xl2 border border-ink/5 p-5">
          <p class="font-semibold text-ink mb-3">1. Cara Terima Barang</p>
          <div class="grid grid-cols-2 gap-3">
            <button
              @click="form.fulfillment_type = 'delivery'"
              class="border-2 rounded-xl2 p-4 text-left"
              :class="form.fulfillment_type === 'delivery' ? 'border-primary bg-primary/5' : 'border-ink/10'"
            >
              <p class="font-medium">🚚 Delivery</p>
              <p class="text-xs text-ink/50">Diantar ke rumah (Kota Palu/Sigi/Donggala)</p>
            </button>
            <button
              @click="form.fulfillment_type = 'pickup'"
              class="border-2 rounded-xl2 p-4 text-left"
              :class="form.fulfillment_type === 'pickup' ? 'border-primary bg-primary/5' : 'border-ink/10'"
            >
              <p class="font-medium">🏬 Pickup</p>
              <p class="text-xs text-ink/50">Ambil sendiri di toko</p>
            </button>
          </div>

          <!-- Pilih Alamat (jika delivery) -->
          <div v-if="form.fulfillment_type === 'delivery'" class="mt-4">
            <p class="text-sm font-medium text-ink mb-2">Alamat Pengiriman</p>
            <div v-if="addresses.length === 0" class="text-sm text-ink/50">
              Belum ada alamat tersimpan.
              <router-link to="/akun" class="text-primary hover:underline">Tambah alamat</router-link>
            </div>
            <label
              v-for="addr in addresses"
              :key="addr.id"
              class="flex items-start gap-2 border rounded-xl2 p-3 mb-2 cursor-pointer"
              :class="form.address_id === addr.id ? 'border-primary bg-primary/5' : 'border-ink/10'"
            >
              <input type="radio" :value="addr.id" v-model="form.address_id" class="mt-1 accent-primary" />
              <div class="text-sm">
                <p class="font-medium">{{ addr.label }} — {{ addr.recipient_name }}</p>
                <p class="text-ink/60">{{ addr.full_address }}</p>
                <p class="text-ink/40 text-xs">Ongkir: Rp {{ formatPrice(addr.delivery_region.shipping_cost) }} ({{ addr.delivery_region.district }})</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Step 2: Jadwal -->
        <div class="bg-white rounded-xl2 border border-ink/5 p-5">
          <p class="font-semibold text-ink mb-3">2. Jadwal {{ form.fulfillment_type === 'delivery' ? 'Pengiriman' : 'Pengambilan' }}</p>
          <input v-model="form.scheduled_date" type="date" :min="today" class="input mb-3" />
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="slot in slots"
              :key="slot.id"
              @click="form.delivery_slot_id = slot.id"
              class="border-2 rounded-xl2 py-2 text-sm"
              :class="form.delivery_slot_id === slot.id ? 'border-primary bg-primary/5' : 'border-ink/10'"
            >
              {{ slot.name }}<br /><span class="text-xs text-ink/50">{{ slot.start_time }}-{{ slot.end_time }}</span>
            </button>
          </div>
        </div>

        <!-- Step 3: Catatan -->
        <div class="bg-white rounded-xl2 border border-ink/5 p-5">
          <p class="font-semibold text-ink mb-3">3. Catatan Pesanan (opsional)</p>
          <textarea v-model="form.note" rows="2" class="input" placeholder="Catatan untuk admin toko..."></textarea>
        </div>

        <!-- Step 4: Pembayaran -->
        <div class="bg-white rounded-xl2 border border-ink/5 p-5">
          <p class="font-semibold text-ink mb-3">4. Metode Pembayaran</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <button
              type="button"
              @click="form.payment_method = 'qris'"
              class="border-2 rounded-xl2 p-3 text-center transition"
              :class="form.payment_method === 'qris' ? 'border-primary bg-primary/5' : 'border-ink/10 hover:border-ink/20'"
            >
              <p class="text-2xl mb-1">📱</p>
              <p class="text-sm font-medium">QRIS</p>
              <p class="text-[11px] text-ink/50">Semua e-wallet & m-banking</p>
            </button>
            <button
              type="button"
              @click="form.payment_method = 'transfer_bank'"
              class="border-2 rounded-xl2 p-3 text-center transition"
              :class="form.payment_method === 'transfer_bank' ? 'border-primary bg-primary/5' : 'border-ink/10 hover:border-ink/20'"
            >
              <p class="text-2xl mb-1">🏦</p>
              <p class="text-sm font-medium">Transfer Bank</p>
              <p class="text-[11px] text-ink/50">Konfirmasi manual admin</p>
            </button>
            <button
              v-if="form.fulfillment_type === 'pickup'"
              type="button"
              @click="form.payment_method = 'bayar_di_tempat'"
              class="border-2 rounded-xl2 p-3 text-center transition"
              :class="form.payment_method === 'bayar_di_tempat' ? 'border-primary bg-primary/5' : 'border-ink/10 hover:border-ink/20'"
            >
              <p class="text-2xl mb-1">💵</p>
              <p class="text-sm font-medium">Bayar di Tempat</p>
              <p class="text-[11px] text-ink/50">Khusus Pickup</p>
            </button>
          </div>

          <!-- Preview QRIS -->
          <div v-if="form.payment_method === 'qris'" class="bg-primary/5 rounded-xl2 p-4 text-center">
            <p class="text-sm font-medium mb-3">Scan kode QRIS berikut setelah pesanan dibuat</p>
            <img v-if="storeInfo.qrisImage" :src="storeInfo.qrisImage" class="w-40 h-40 mx-auto rounded-lg border border-ink/10" />
            <div v-else class="w-40 h-40 mx-auto rounded-lg border-2 border-dashed border-ink/20 flex items-center justify-center text-4xl">📱</div>
            <p class="text-xs text-ink/50 mt-3">Kode QR akan tampil kembali di halaman Detail Pesanan setelah Anda klik "Buat Pesanan"</p>
          </div>

          <!-- Preview Rekening Bank -->
          <div v-if="form.payment_method === 'transfer_bank'" class="bg-primary/5 rounded-xl2 p-4">
            <p class="text-sm font-medium mb-3">Transfer ke salah satu rekening berikut:</p>
            <div v-if="storeInfo.bankAccounts?.length" class="space-y-2">
              <div v-for="(bank, i) in storeInfo.bankAccounts" :key="i" class="bg-white rounded-lg p-3 text-sm border border-ink/5">
                <p class="font-semibold">{{ bank.bank }}</p>
                <p class="text-ink/70">{{ bank.no_rek }} a.n. {{ bank.atas_nama }}</p>
              </div>
            </div>
            <p v-else class="text-xs text-ink/50">Info rekening akan tersedia di halaman Detail Pesanan.</p>
            <p class="text-xs text-ink/50 mt-2">Setelah transfer, unggah bukti pembayaran di halaman Detail Pesanan.</p>
          </div>
        </div>
      </div>


      <!-- Ringkasan -->
      <div class="bg-white rounded-xl2 border border-ink/5 p-5 h-fit sticky top-24">
        <p class="font-semibold text-ink mb-4">Ringkasan Pesanan</p>
        <div class="flex justify-between text-sm mb-2">
          <span class="text-ink/60">Subtotal</span>
          <span>Rp {{ formatPrice(cart.subtotal) }}</span>
        </div>
        <div class="flex justify-between text-sm mb-2">
          <span class="text-ink/60">Ongkos Kirim</span>
          <span>{{ shippingCost ? 'Rp ' + formatPrice(shippingCost) : '-' }}</span>
        </div>
        <div class="border-t border-ink/10 my-3 pt-3 flex justify-between font-bold text-ink">
          <span>Total</span>
          <span>Rp {{ formatPrice(cart.subtotal + shippingCost) }}</span>
        </div>

        <p v-if="error" class="text-danger text-sm mb-3">{{ error }}</p>

        <button
          @click="submitOrder"
          :disabled="submitting || !canSubmit"
          class="w-full bg-accent hover:bg-accent-light text-white font-semibold py-3 rounded-full transition disabled:opacity-50"
        >
          {{ submitting ? 'Memproses...' : 'Buat Pesanan' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../../stores/cart'
import { useStoreInfoStore } from '../../stores/store'
import api from '../../services/api'

const router = useRouter()
const cart = useCartStore()
const storeInfo = useStoreInfoStore()

const addresses = ref([])
const slots = ref([])
const submitting = ref(false)
const error = ref('')

const form = ref({
  fulfillment_type: 'delivery',
  address_id: null,
  delivery_slot_id: null,
  scheduled_date: '',
  note: '',
  payment_method: 'qris',
})

const today = new Date().toISOString().split('T')[0]

const shippingCost = computed(() => {
  if (form.value.fulfillment_type !== 'delivery' || !form.value.address_id) return 0
  const addr = addresses.value.find((a) => a.id === form.value.address_id)
  return addr?.delivery_region?.shipping_cost || 0
})

const canSubmit = computed(() => {
  if (!form.value.delivery_slot_id || !form.value.scheduled_date) return false
  if (form.value.fulfillment_type === 'delivery' && !form.value.address_id) return false
  return true
})

async function submitOrder() {
  submitting.value = true
  error.value = ''
  try {
    const { data } = await api.post('/checkout', form.value)
    router.push(`/pesanan/${data.order.id}`)
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal membuat pesanan.'
  } finally {
    submitting.value = false
  }
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

onMounted(async () => {
  await cart.fetchCart()
  await storeInfo.fetchStoreInfo()
  const [addrRes, slotRes] = await Promise.all([api.get('/addresses'), api.get('/delivery-slots')])
  addresses.value = addrRes.data.addresses
  slots.value = slotRes.data.slots ?? slotRes.data
  if (addresses.value.find((a) => a.is_default)) {
    form.value.address_id = addresses.value.find((a) => a.is_default).id
  }
})
</script>

<style scoped>
.input {
  @apply w-full border border-ink/15 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/40;
}
</style>
