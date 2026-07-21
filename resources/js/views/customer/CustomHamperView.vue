<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-ink mb-1 flex items-center gap-2">
      <GiftIcon class="w-6 h-6 text-primary" stroke-width="1.75" /> Buat Parsel Kustom
    </h1>
    <p class="text-ink/60 text-sm mb-6">Rangkai sendiri hampers buah sesuai keinginanmu — harga dihitung otomatis</p>

    <div class="grid md:grid-cols-3 gap-6">
      <div class="md:col-span-2 space-y-6">
        <!-- Pilih Buah -->
        <div class="glass-card rounded-xl2 p-5">
          <p class="font-semibold text-ink mb-3">1. Pilih Kombinasi Buah</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <label
              v-for="p in products"
              :key="p.id"
              class="border rounded-xl2 p-3 text-sm cursor-pointer transition-colors"
              :class="selectedProducts[p.id] ? 'border-primary bg-primary/10' : 'border-ink/20 bg-white hover:border-accent/40'"
            >
              <div class="flex justify-between items-center mb-1">
                <span class="font-medium">{{ p.name }}</span>
                <input type="checkbox" :checked="!!selectedProducts[p.id]" @change="toggleProduct(p)" class="accent-primary cursor-pointer" />
              </div>
              <p class="text-ink/50 text-xs mb-2">Rp {{ formatPrice(p.price_unit) }}/{{ p.unit }}</p>
              <div v-if="selectedProducts[p.id]" class="flex items-center gap-2">
                <button @click.prevent="changeQty(p, -0.5)" class="w-6 h-6 border border-primary/30 bg-white rounded-full text-xs hover:bg-primary/10 transition-colors cursor-pointer">-</button>
                <span class="text-xs">{{ selectedProducts[p.id].qty }} {{ p.unit }}</span>
                <button @click.prevent="changeQty(p, 0.5)" class="w-6 h-6 border border-primary/30 bg-white rounded-full text-xs hover:bg-primary/10 transition-colors cursor-pointer">+</button>
              </div>
            </label>
          </div>
        </div>

        <!-- Pilih Wadah -->
        <div class="glass-card rounded-xl2 p-5">
          <p class="font-semibold text-ink mb-3">2. Pilih Wadah</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <button
              v-for="c in containers"
              :key="c.id"
              @click="selectedContainer = c"
              class="border-2 rounded-xl2 p-3 text-sm text-left transition-colors cursor-pointer"
              :class="selectedContainer?.id === c.id ? 'border-primary bg-primary/10' : 'border-ink/20 bg-white hover:border-accent/40'"
            >
              <p class="font-medium">{{ c.name }}</p>
              <p class="text-ink/50 text-xs">+Rp {{ formatPrice(c.extra_price) }}</p>
            </button>
          </div>
        </div>

        <!-- Pilih Kartu Ucapan -->
        <div class="glass-card rounded-xl2 p-5">
          <p class="font-semibold text-ink mb-3">3. Kartu Ucapan (opsional)</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <button
              @click="selectedCard = null"
              class="border-2 rounded-xl2 p-3 text-sm text-left transition-colors cursor-pointer"
              :class="!selectedCard ? 'border-primary bg-primary/10' : 'border-ink/20 bg-white hover:border-accent/40'"
            >
              Tanpa Kartu
            </button>
            <button
              v-for="c in cards"
              :key="c.id"
              @click="selectedCard = c"
              class="border-2 rounded-xl2 p-3 text-sm text-left transition-colors cursor-pointer"
              :class="selectedCard?.id === c.id ? 'border-primary bg-primary/10' : 'border-ink/20 bg-white hover:border-accent/40'"
            >
              <p class="font-medium">{{ c.name }}</p>
              <p class="text-ink/50 text-xs">+Rp {{ formatPrice(c.extra_price) }}</p>
            </button>
          </div>
          <textarea
            v-if="selectedCard"
            v-model="cardMessage"
            rows="2"
            placeholder="Tulis pesan ucapan Anda..."
            class="w-full bg-white border border-ink/10 rounded-lg px-3 py-2 text-sm mt-3 focus:outline-none focus:ring-2 focus:ring-accent/40"
          ></textarea>
        </div>
      </div>

      <!-- Ringkasan Real-time -->
      <div class="glass-card rounded-xl2 p-5 h-fit sticky top-24">
        <p class="font-semibold text-ink mb-4">Ringkasan Parsel</p>

        <div v-if="Object.keys(selectedProducts).length === 0" class="text-ink/40 text-sm mb-4">
          Pilih buah terlebih dahulu.
        </div>
        <div v-else class="space-y-1.5 mb-3 text-sm">
          <div v-for="sp in Object.values(selectedProducts)" :key="sp.product.id" class="flex justify-between">
            <span>{{ sp.product.name }} ({{ sp.qty }})</span>
            <span>Rp {{ formatPrice(sp.product.price_unit * sp.qty) }}</span>
          </div>
        </div>

        <div v-if="selectedContainer" class="flex justify-between text-sm mb-1.5">
          <span>Wadah: {{ selectedContainer.name }}</span>
          <span>Rp {{ formatPrice(selectedContainer.extra_price) }}</span>
        </div>
        <div v-if="selectedCard" class="flex justify-between text-sm mb-1.5">
          <span>Kartu: {{ selectedCard.name }}</span>
          <span>Rp {{ formatPrice(selectedCard.extra_price) }}</span>
        </div>

        <div class="border-t border-ink/10 my-3 pt-3 flex justify-between font-bold text-ink">
          <span>Total Estimasi</span>
          <span class="text-accent">Rp {{ formatPrice(totalPrice) }}</span>
        </div>

        <button
          @click="addCustomHamperToCart"
          :disabled="Object.keys(selectedProducts).length === 0 || adding"
          class="w-full inline-flex items-center justify-center gap-1.5 bg-accent hover:bg-accent-light text-white font-semibold py-3 rounded-full transition disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
        >
          <ShoppingCartIcon v-if="!adding" class="w-4 h-4" stroke-width="1.75" />
          {{ adding ? 'Menambahkan...' : 'Tambah ke Keranjang' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import { GiftIcon, ShoppingCartIcon } from '@heroicons/vue/24/outline'

const products = ref([])
const containers = ref([])
const cards = ref([])

const selectedProducts = ref({}) // { [product_id]: { product, qty } }
const selectedContainer = ref(null)
const selectedCard = ref(null)
const cardMessage = ref('')
const adding = ref(false)

function toggleProduct(p) {
  if (selectedProducts.value[p.id]) {
    delete selectedProducts.value[p.id]
  } else {
    selectedProducts.value[p.id] = { product: p, qty: 1 }
  }
}

function changeQty(p, delta) {
  const item = selectedProducts.value[p.id]
  if (!item) return
  item.qty = Math.max(0.5, item.qty + delta)
}

const totalPrice = computed(() => {
  let total = Object.values(selectedProducts.value).reduce((sum, sp) => sum + sp.product.price_unit * sp.qty, 0)
  if (selectedContainer.value) total += selectedContainer.value.extra_price
  if (selectedCard.value) total += selectedCard.value.extra_price
  return total
})

async function addCustomHamperToCart() {
  adding.value = true
  try {
    await api.post('/cart/items', {
      hamper_id: null,
      qty: 1,
      custom_hamper_config: {
        products: Object.values(selectedProducts.value).map((sp) => ({ product_id: sp.product.id, qty: sp.qty })),
        container_id: selectedContainer.value?.id || null,
        card_id: selectedCard.value?.id || null,
        card_message: cardMessage.value || null,
        estimated_price: totalPrice.value,
      },
      note: 'Parsel Kustom',
    })
    alert('Parsel kustom berhasil ditambahkan ke keranjang!')
  } finally {
    adding.value = false
  }
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

onMounted(async () => {
  const { data } = await api.get('/hampers/custom-options')
  products.value = data.products
  containers.value = data.containers
  cards.value = data.cards
})
</script>
