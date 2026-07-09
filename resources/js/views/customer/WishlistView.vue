<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-ink mb-6">Wishlist Saya</h1>

    <p v-if="loading" class="text-ink/50 text-sm">Memuat wishlist...</p>
    <p v-else-if="wishlists.length === 0" class="text-ink/50 text-sm">Belum ada produk favorit tersimpan.</p>

    <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div v-for="w in wishlists" :key="w.id" class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
        <router-link :to="`/produk/${w.product.slug}`" class="block aspect-square bg-primary/5 flex items-center justify-center text-4xl">
          <img v-if="w.product.images?.[0]" :src="w.product.images[0].image_path" class="w-full h-full object-cover" />
          <span v-else>🍎</span>
        </router-link>
        <div class="p-3">
          <p class="font-medium text-sm truncate">{{ w.product.name }}</p>
          <p class="text-accent font-semibold text-sm mb-2">Rp {{ formatPrice(w.product.price_unit) }}</p>

          <p v-if="w.product.stock === 0" class="text-xs mb-2">
            <span v-if="w.notify_when_available" class="text-success">🔔 Anda akan diberitahu saat stok tersedia</span>
            <button v-else @click="notifyMe(w)" class="text-primary hover:underline">Beritahu Saya</button>
          </p>

          <button @click="remove(w)" class="w-full text-xs text-danger border border-danger/30 rounded-full py-1.5 hover:bg-danger/5">
            Hapus dari Wishlist
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const wishlists = ref([])
const loading = ref(true)

async function fetchWishlists() {
  loading.value = true
  try {
    const { data } = await api.get('/wishlist')
    wishlists.value = data.wishlists
  } finally {
    loading.value = false
  }
}

async function remove(w) {
  await api.delete(`/wishlist/${w.id}`)
  wishlists.value = wishlists.value.filter((x) => x.id !== w.id)
}

async function notifyMe(w) {
  await api.post(`/wishlist/${w.id}/notify-me`)
  w.notify_when_available = true
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

onMounted(fetchWishlists)
</script>
