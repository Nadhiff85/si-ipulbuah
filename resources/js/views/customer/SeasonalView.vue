<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-ink mb-1">🥭 Buah Musiman Sulawesi Tengah</h1>
    <p class="text-ink/60 text-sm mb-6">Segar, enak, dan sedang musim panen sekarang - fitur A.15</p>

    <p v-if="loading" class="text-ink/50 text-sm">Memuat...</p>
    <p v-else-if="products.length === 0" class="text-ink/50 text-sm">Belum ada buah musiman saat ini.</p>

    <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <router-link
        v-for="p in products"
        :key="p.id"
        :to="`/produk/${p.slug}`"
        class="glass-card rounded-xl2 overflow-hidden hover:shadow-md transition cursor-pointer"
      >
        <div class="aspect-square bg-primary/5 flex items-center justify-center text-4xl">
          <img v-if="p.images?.[0]" :src="p.images[0].image_path" class="w-full h-full object-cover" />
          <PhotoIcon v-else class="w-10 h-10 text-primary/40" stroke-width="1.5" />
        </div>
        <div class="p-3">
          <p class="font-medium text-sm truncate">{{ p.name }}</p>
          <p class="text-accent font-semibold text-sm">Rp {{ formatPrice(p.price_unit) }}</p>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { PhotoIcon } from '@heroicons/vue/24/outline'

const products = ref([])
const loading = ref(true)

async function fetchSeasonal() {
  loading.value = true
  try {
    const { data } = await api.get('/products', { params: { label: 'musiman' } })
    products.value = data.data
  } finally {
    loading.value = false
  }
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

onMounted(fetchSeasonal)
</script>
