<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-xl font-bold text-ink mb-1">🧺 Paket Buah & Hampers</h1>
        <p class="text-ink/60 text-sm">Bundel pilihan dengan harga spesial, sudah dikurasi untuk Anda</p>
      </div>
      <router-link to="/parsel-kustom" class="bg-primary text-white px-4 py-2.5 rounded-full text-sm font-medium whitespace-nowrap">
        🎁 Buat Kustom
      </router-link>
    </div>

    <p v-if="loading" class="text-ink/50 text-sm">Memuat paket...</p>
    <p v-else-if="hampers.length === 0" class="text-ink/50 text-sm">Belum ada paket tersedia.</p>

    <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      <div v-for="h in hampers" :key="h.id" class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
        <div class="aspect-square bg-primary/5 flex items-center justify-center text-4xl">
          <img v-if="h.image" :src="h.image" class="w-full h-full object-cover" />
          <span v-else>🧺</span>
        </div>
        <div class="p-3">
          <p class="font-medium text-sm">{{ h.name }}</p>
          <p class="text-xs text-ink/50 mb-2">{{ h.items?.map(i => i.product.name).join(', ') }}</p>
          <p class="text-accent font-bold mb-2">Rp {{ formatPrice(h.base_price) }}</p>
          <button @click="addHamperToCart(h)" class="w-full bg-accent hover:bg-accent-light text-white text-xs font-semibold py-2 rounded-full">
            🛒 Tambah ke Keranjang
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const hampers = ref([])
const loading = ref(true)

async function fetchHampers() {
  loading.value = true
  try {
    const { data } = await api.get('/hampers')
    hampers.value = data.hampers
  } finally {
    loading.value = false
  }
}

async function addHamperToCart(hamper) {
  await api.post('/cart/items', { hamper_id: hamper.id, qty: 1 })
  alert('Paket berhasil ditambahkan ke keranjang!')
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

onMounted(fetchHampers)
</script>
