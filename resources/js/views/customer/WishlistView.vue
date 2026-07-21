<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-ink mb-6">Wishlist Saya</h1>

    <p v-if="loading" class="text-ink/50 text-sm">Memuat wishlist...</p>
    <div v-else-if="wishlists.length === 0" class="glass-card-soft rounded-xl2 py-10 text-center">
      <HeartIcon class="w-8 h-8 mx-auto mb-2 text-ink/30" stroke-width="1.5" />
      <p class="text-ink/50 text-sm">Belum ada produk favorit tersimpan.</p>
    </div>

    <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div v-for="w in wishlists" :key="w.id" class="glass-card rounded-xl2 overflow-hidden">
        <router-link :to="`/produk/${w.product.slug}`" class="block aspect-square bg-primary/5 flex items-center justify-center text-4xl cursor-pointer">
          <img v-if="w.product.images?.[0]" :src="w.product.images[0].image_path" class="w-full h-full object-cover" />
          <PhotoIcon v-else class="w-10 h-10 text-primary/40" stroke-width="1.5" />
        </router-link>
        <div class="p-3">
          <p class="font-medium text-sm truncate">{{ w.product.name }}</p>
          <p class="text-accent font-semibold text-sm mb-2">Rp {{ formatPrice(w.product.price_unit) }}</p>

          <p v-if="w.product.stock === 0" class="text-xs mb-2">
            <span v-if="w.notify_when_available" class="inline-flex items-center gap-1 text-success">
              <BellIcon class="w-3.5 h-3.5" stroke-width="1.75" /> Anda akan diberitahu saat stok tersedia
            </span>
            <button v-else @click="notifyMe(w)" class="text-primary hover:underline transition-colors cursor-pointer">Beritahu Saya</button>
          </p>

          <button @click="remove(w)" class="w-full text-xs text-danger border border-danger/30 rounded-full py-1.5 hover:bg-danger/5 transition-colors cursor-pointer">
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
import { HeartIcon, PhotoIcon, BellIcon } from '@heroicons/vue/24/outline'

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
