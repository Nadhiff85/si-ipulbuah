<template>
  <div class="py-8">
    <div class="max-w-4xl mx-auto px-4">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-ink">Checkout</h1>
        <p class="text-sm text-ink/60 mt-1">Lengkapi detail pesanan, lalu bayar dengan QRIS atau transfer bank.</p>
      </div>

      <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-5">
          <!-- Step 1: Metode Pengiriman -->
          <div class="glass-card rounded-xl2 p-5">
            <p class="font-semibold text-ink mb-3">1. Cara Terima Barang</p>
            <div class="grid grid-cols-2 gap-3">
              <button
                @click="setFulfillment('delivery')"
                class="border-2 rounded-xl2 p-4 text-left transition cursor-pointer"
                :class="form.fulfillment_type === 'delivery' ? 'border-primary bg-primary/10' : 'border-ink/20 bg-white hover:border-accent/40'"
              >
                <p class="font-medium flex items-center gap-1.5"><TruckIcon class="w-5 h-5 text-primary" stroke-width="1.75" /> Delivery</p>
                <p class="text-xs text-ink/50">Diantar ke rumah (Kota Palu/Sigi/Donggala)</p>
              </button>
              <button
                @click="setFulfillment('pickup')"
                class="border-2 rounded-xl2 p-4 text-left transition cursor-pointer"
                :class="form.fulfillment_type === 'pickup' ? 'border-primary bg-primary/10' : 'border-ink/20 bg-white hover:border-accent/40'"
              >
                <p class="font-medium flex items-center gap-1.5"><BuildingStorefrontIcon class="w-5 h-5 text-primary" stroke-width="1.75" /> Pickup</p>
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
                class="flex items-start gap-2 border rounded-xl2 p-3 mb-2 cursor-pointer transition"
                :class="form.address_id === addr.id ? 'border-primary bg-primary/10' : 'border-ink/20 bg-white'"
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
          <div class="glass-card rounded-xl2 p-5">
            <p class="font-semibold text-ink mb-3">2. Jadwal {{ form.fulfillment_type === 'delivery' ? 'Pengiriman' : 'Pengambilan' }}</p>
            <input v-model="form.scheduled_date" type="date" :min="today" class="input mb-3" />
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="slot in slots"
                :key="slot.id"
                @click="form.delivery_slot_id = slot.id"
                class="border-2 rounded-xl2 py-2 text-sm transition cursor-pointer"
                :class="form.delivery_slot_id === slot.id ? 'border-primary bg-primary/10' : 'border-ink/20 bg-white'"
              >
                {{ slot.name }}<br /><span class="text-xs text-ink/50">{{ slot.start_time }}-{{ slot.end_time }}</span>
              </button>
            </div>
          </div>

          <!-- Step 3: Catatan -->
          <div class="glass-card rounded-xl2 p-5">
            <p class="font-semibold text-ink mb-3">3. Catatan Pesanan (opsional)</p>
            <textarea v-model="form.note" rows="2" class="input" placeholder="Catatan untuk admin toko..."></textarea>
          </div>

          <!-- Step 4: Pembayaran -->
          <div class="glass-card rounded-xl2 p-5">
            <p class="font-semibold text-ink mb-3">4. Metode Pembayaran</p>
            <div class="grid grid-cols-1 gap-3 mb-4" :class="form.fulfillment_type === 'pickup' ? 'sm:grid-cols-2' : ''">
              <button
                type="button"
                @click="form.payment_method = 'qris'"
                class="border-2 rounded-xl2 p-4 text-left transition cursor-pointer flex items-center gap-3"
                :class="form.payment_method === 'qris' ? 'border-primary bg-primary/10' : 'border-ink/20 bg-white hover:border-accent/40'"
              >
                <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                  <CreditCardIcon class="w-6 h-6 text-primary" stroke-width="1.5" />
                </div>
                <div>
                  <p class="text-sm font-semibold">Pembayaran Online</p>
                  <p class="text-[11px] text-ink/50 leading-snug">QRIS, transfer bank, e-wallet & kartu</p>
                </div>
              </button>
              <button
                v-if="form.fulfillment_type === 'pickup'"
                type="button"
                @click="form.payment_method = 'bayar_di_tempat'"
                class="border-2 rounded-xl2 p-4 text-left transition cursor-pointer flex items-center gap-3"
                :class="form.payment_method === 'bayar_di_tempat' ? 'border-primary bg-primary/10' : 'border-ink/20 bg-white hover:border-accent/40'"
              >
                <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                  <BanknotesIcon class="w-6 h-6 text-primary" stroke-width="1.5" />
                </div>
                <div>
                  <p class="text-sm font-semibold">Bayar di Tempat</p>
                  <p class="text-[11px] text-ink/50 leading-snug">Khusus ambil sendiri</p>
                </div>
              </button>
            </div>

            <!-- Info Pembayaran Online -->
            <div v-if="form.payment_method === 'qris'" class="glass-card-soft rounded-xl2 p-4">
              <p class="text-sm font-medium text-ink mb-2.5">Pilih metode bayar setelah pesanan dibuat</p>
              <div class="flex flex-wrap gap-1.5 mb-3">
                <span v-for="m in onlineMethods" :key="m" class="text-[11px] font-medium bg-white border border-ink/10 text-ink/60 px-2.5 py-1 rounded-full">
                  {{ m }}
                </span>
              </div>
              <p class="text-xs text-ink/50 leading-relaxed">
                Halaman pembayaran akan terbuka otomatis di Detail Pesanan. Pembayaran terverifikasi otomatis begitu selesai.
              </p>
            </div>
          </div>
        </div>


        <!-- Ringkasan -->
        <div class="glass-card rounded-xl2 p-5 h-fit sticky top-24">
          <p class="font-semibold text-ink mb-4">Ringkasan Pesanan</p>
          <div class="flex justify-between text-sm mb-2">
            <span class="text-ink/60">Subtotal</span>
            <span class="tabular-nums">Rp {{ formatPrice(cart.subtotal) }}</span>
          </div>
          <div class="flex justify-between text-sm mb-2">
            <span class="text-ink/60">Ongkos Kirim</span>
            <span class="tabular-nums">{{ shippingCost ? 'Rp ' + formatPrice(shippingCost) : '-' }}</span>
          </div>
          <div class="border-t border-ink/10 my-3 pt-3 flex justify-between font-bold text-ink">
            <span>Total</span>
            <span class="tabular-nums">Rp {{ formatPrice(cart.subtotal + shippingCost) }}</span>
          </div>

          <p v-if="error" class="text-danger text-sm mb-3">{{ error }}</p>

          <button
            @click="submitOrder"
            :disabled="submitting || !canSubmit"
            class="w-full bg-accent hover:bg-accent-light text-white font-semibold py-3 rounded-full transition disabled:opacity-50 shadow-sm hover:shadow-md cursor-pointer disabled:cursor-not-allowed"
          >
            {{ submitting ? 'Memproses...' : 'Buat Pesanan' }}
          </button>
        </div>
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
import {
  TruckIcon,
  BuildingStorefrontIcon,
  CreditCardIcon,
  BanknotesIcon,
} from '@heroicons/vue/24/outline'

// Metode yang tersedia di dalam halaman pembayaran Midtrans
const onlineMethods = ['QRIS', 'Transfer Bank / VA', 'GoPay', 'ShopeePay', 'Kartu Kredit']

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
  return Number(addr?.delivery_region?.shipping_cost) || 0
})

// "Bayar di Tempat" hanya berlaku untuk pickup, jadi saat pindah ke delivery
// metode bayarnya dikembalikan ke online supaya tidak ditolak backend (422).
function setFulfillment(type) {
  form.value.fulfillment_type = type
  if (type !== 'pickup' && form.value.payment_method === 'bayar_di_tempat') {
    form.value.payment_method = 'qris'
  }
}

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
  @apply w-full bg-white border border-ink/10 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-accent/40;
}
</style>
