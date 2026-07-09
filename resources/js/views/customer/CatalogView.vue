<template>
  <div class="max-w-7xl mx-auto px-4 py-8 grid md:grid-cols-4 gap-6">
    <!-- ===== Sidebar Filter ===== -->
    <aside class="md:col-span-1 space-y-6">
      <div class="bg-white rounded-xl2 border border-ink/5 p-4">
        <p class="font-semibold text-ink mb-3">Kategori</p>
        <label
          v-for="cat in categories"
          :key="cat.id"
          class="flex items-center gap-2 py-1.5 text-sm cursor-pointer"
        >
          <input type="radio" :value="cat.id" v-model="filters.category_id" @change="fetchProducts" class="accent-primary" />
          {{ cat.name }}
        </label>
        <button @click="filters.category_id = null; fetchProducts()" class="text-xs text-primary mt-1 hover:underline">
          Reset kategori
        </button>
      </div>

      <div class="bg-white rounded-xl2 border border-ink/5 p-4">
        <p class="font-semibold text-ink mb-3">Asal Buah</p>
        <label v-for="opt in [{v:'lokal',l:'Lokal'},{v:'impor',l:'Impor'}]" :key="opt.v" class="flex items-center gap-2 py-1.5 text-sm cursor-pointer">
          <input type="radio" :value="opt.v" v-model="filters.origin_type" @change="fetchProducts" class="accent-primary" />
          {{ opt.l }}
        </label>
      </div>

      <div class="bg-white rounded-xl2 border border-ink/5 p-4">
        <p class="font-semibold text-ink mb-3">Label</p>
        <label v-for="opt in [{v:'segar',l:'Segar'},{v:'best_seller',l:'Best Seller'},{v:'musiman',l:'Musiman'},{v:'promo',l:'Promo'}]" :key="opt.v" class="flex items-center gap-2 py-1.5 text-sm cursor-pointer">
          <input type="radio" :value="opt.v" v-model="filters.label" @change="fetchProducts" class="accent-primary" />
          {{ opt.l }}
        </label>
      </div>

      <div class="bg-white rounded-xl2 border border-ink/5 p-4">
        <p class="font-semibold text-ink mb-3">Rentang Harga</p>
        <div class="flex gap-2">
          <input v-model.number="filters.min_price" type="number" placeholder="Min" class="input-sm" />
          <input v-model.number="filters.max_price" type="number" placeholder="Max" class="input-sm" />
        </div>
        <button @click="fetchProducts" class="w-full mt-2 bg-primary/10 text-primary text-sm font-medium py-1.5 rounded-lg hover:bg-primary/20">
          Terapkan
        </button>
      </div>
    </aside>

    <!-- ===== Hasil ===== -->
    <section class="md:col-span-3">
      <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between mb-5">
        <div class="relative flex-1">
          <input
            v-model="filters.search"
            @keyup.enter="fetchProducts"
            type="text"
            placeholder="Cari buah favoritmu..."
            class="w-full border border-ink/15 rounded-full px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/40"
          />
        </div>
        <select v-model="filters.sort" @change="fetchProducts" class="border border-ink/15 rounded-full px-4 py-2.5 text-sm">
          <option value="terbaru">Terbaru</option>
          <option value="harga_termurah">Harga Terendah</option>
          <option value="harga_termahal">Harga Tertinggi</option>
        </select>
      </div>

      <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div v-for="i in 6" :key="i" class="bg-white rounded-xl2 overflow-hidden animate-pulse">
          <div class="aspect-square bg-ink/10"></div>
          <div class="p-3 space-y-2">
            <div class="h-3 bg-ink/10 rounded w-3/4"></div>
            <div class="h-3 bg-ink/10 rounded w-1/2"></div>
          </div>
        </div>
      </div>
      <p v-else-if="products.length === 0" class="text-ink/50 text-sm">Produk tidak ditemukan.</p>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-4 fade-in">
        <router-link
          v-for="p in products"
          :key="p.id"
          :to="`/produk/${p.slug}`"
          class="bg-white rounded-xl2 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all overflow-hidden"
        >
          <div class="aspect-square bg-primary/5 flex items-center justify-center text-5xl relative overflow-hidden">
            <img v-if="p.images?.[0]" :src="p.images[0].image_path" class="w-full h-full object-cover" />
            <span v-else>🍎</span>
            <span
              v-for="label in p.labels || []"
              :key="label"
              class="absolute top-2 left-2 bg-badge text-ink text-[10px] font-bold px-2 py-0.5 rounded-full uppercase"
            >
              {{ labelText(label) }}
            </span>
          </div>
          <div class="p-3">
            <p class="font-medium text-sm text-ink truncate">{{ p.name }}</p>
            <p class="text-xs text-ink/50">{{ p.unit }}</p>
            <p class="text-accent font-bold mt-1">Rp {{ formatPrice(p.price_unit) }}</p>
            <p v-if="p.price_wholesale" class="text-[11px] text-ink/50">
              Grosir Rp {{ formatPrice(p.price_wholesale) }} (min. {{ p.wholesale_min_qty }})
            </p>
          </div>
        </router-link>
      </div>

      <!-- Pagination sederhana -->
      <div v-if="meta && meta.last_page > 1" class="flex justify-center gap-2 mt-8">
        <button
          v-for="page in meta.last_page"
          :key="page"
          @click="goToPage(page)"
          class="w-9 h-9 rounded-full text-sm font-medium"
          :class="page === meta.current_page ? 'bg-primary text-white' : 'bg-white border border-ink/10 text-ink/70'"
        >
          {{ page }}
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const categories = ref([])
const products = ref([])
const meta = ref(null)
const loading = ref(false)

const filters = ref({
  search: '',
  category_id: null,
  origin_type: null,
  label: null,
  min_price: null,
  max_price: null,
  sort: 'terbaru',
  page: 1,
})

async function fetchCategories() {
  const { data } = await api.get('/categories')
  categories.value = data.categories
}

async function fetchProducts() {
  loading.value = true
  try {
    const { data } = await api.get('/products', { params: filters.value })
    products.value = data.data
    meta.value = data
  } finally {
    loading.value = false
  }
}

function goToPage(page) {
  filters.value.page = page
  fetchProducts()
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

function labelText(label) {
  return { segar: 'Segar', best_seller: 'Best Seller', musiman: 'Musiman', promo: 'Promo' }[label] || label
}

onMounted(() => {
  fetchCategories()
  fetchProducts()
})
</script>

<style scoped>
.input-sm {
  @apply w-1/2 border border-ink/15 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40;
}
</style>
