<template>
  <div class="max-w-7xl mx-auto px-4 py-6 md:py-8">

    <!-- ===== Bar filter mobile (di atas segalanya) ===== -->
    <div class="md:hidden mb-4">
      <div class="flex items-center gap-2">
        <!-- Tombol toggle filter -->
        <button
          @click="showFilters = !showFilters"
          class="flex items-center gap-1.5 border border-ink/15 bg-white rounded-full px-4 py-2 text-sm font-semibold text-ink/70 hover:border-primary/40 transition shrink-0 cursor-pointer"
          :class="showFilters ? 'border-primary/40 text-primary' : ''"
        >
          <AdjustmentsHorizontalIcon class="w-4 h-4" stroke-width="1.75" />
          Filter
          <span
            v-if="activeFilterCount > 0"
            class="bg-primary text-white text-[9px] font-black min-w-[16px] h-4 rounded-full flex items-center justify-center px-0.5 leading-none ml-0.5"
          >{{ activeFilterCount }}</span>
          <ChevronDownIcon
            class="w-3.5 h-3.5 transition-transform ml-0.5"
            stroke-width="2"
            :class="showFilters ? 'rotate-180' : ''"
          />
        </button>

        <!-- Search bar inline di mobile -->
        <div class="relative flex-1">
          <MagnifyingGlassIcon class="w-4 h-4 text-ink/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" stroke-width="1.75" />
          <input
            v-model="filters.search"
            @keyup.enter="fetchProducts"
            type="text"
            placeholder="Cari buah..."
            class="w-full bg-white border border-ink/10 rounded-full pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40"
          />
        </div>
      </div>

      <!-- Active filter chips -->
      <div v-if="activeFilterCount > 0" class="flex flex-wrap gap-1.5 mt-2.5">
        <button
          v-if="filters.category_id"
          @click="filters.category_id = null; fetchProducts()"
          class="flex items-center gap-1 bg-primary/10 text-primary text-xs font-semibold px-3 py-1 rounded-full cursor-pointer hover:bg-primary/20 transition"
        >
          {{ categories.find(c => c.id === filters.category_id)?.name }}
          <span class="text-primary/70 font-bold">×</span>
        </button>
        <button
          v-if="filters.origin_type"
          @click="filters.origin_type = null; fetchProducts()"
          class="flex items-center gap-1 bg-primary/10 text-primary text-xs font-semibold px-3 py-1 rounded-full cursor-pointer hover:bg-primary/20 transition"
        >
          {{ filters.origin_type === 'lokal' ? 'Lokal' : 'Impor' }}
          <span class="text-primary/70 font-bold">×</span>
        </button>
        <button
          v-if="filters.label"
          @click="filters.label = null; fetchProducts()"
          class="flex items-center gap-1 bg-primary/10 text-primary text-xs font-semibold px-3 py-1 rounded-full cursor-pointer hover:bg-primary/20 transition"
        >
          {{ labelText(filters.label) }}
          <span class="text-primary/70 font-bold">×</span>
        </button>
        <button
          @click="resetAllFilters"
          class="text-xs text-ink/40 hover:text-danger transition px-2 py-1 cursor-pointer"
        >
          Reset semua
        </button>
      </div>

      <!-- Panel filter yang bisa collapse (mobile) -->
      <div
        v-show="showFilters"
        class="mt-3 glass-card rounded-xl2 p-4 space-y-5"
      >
        <div>
          <p class="font-semibold text-ink text-sm mb-2">Kategori</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="cat in categories"
              :key="cat.id"
              @click="filters.category_id = filters.category_id === cat.id ? null : cat.id; fetchProducts()"
              class="px-3 py-1.5 rounded-full text-xs font-semibold border transition cursor-pointer"
              :class="filters.category_id === cat.id ? 'bg-primary text-white border-primary' : 'bg-white border-ink/15 text-ink/60 hover:border-primary/40'"
            >
              {{ cat.name }}
            </button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="font-semibold text-ink text-sm mb-2">Asal Buah</p>
            <div class="flex gap-2">
              <button
                v-for="opt in [{v:'lokal',l:'Lokal'},{v:'impor',l:'Impor'}]"
                :key="opt.v"
                @click="filters.origin_type = filters.origin_type === opt.v ? null : opt.v; fetchProducts()"
                class="flex-1 py-1.5 rounded-full text-xs font-semibold border transition cursor-pointer"
                :class="filters.origin_type === opt.v ? 'bg-primary text-white border-primary' : 'bg-white border-ink/15 text-ink/60 hover:border-primary/40'"
              >
                {{ opt.l }}
              </button>
            </div>
          </div>
          <div>
            <p class="font-semibold text-ink text-sm mb-2">Label</p>
            <div class="flex flex-wrap gap-1.5">
              <button
                v-for="opt in [{v:'segar',l:'Segar'},{v:'best_seller',l:'Best Seller'},{v:'musiman',l:'Musiman'},{v:'promo',l:'Promo'}]"
                :key="opt.v"
                @click="filters.label = filters.label === opt.v ? null : opt.v; fetchProducts()"
                class="px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                :class="filters.label === opt.v ? 'bg-primary text-white border-primary' : 'bg-white border-ink/15 text-ink/60 hover:border-primary/40'"
              >
                {{ opt.l }}
              </button>
            </div>
          </div>
        </div>

        <div>
          <p class="font-semibold text-ink text-sm mb-2">Rentang Harga</p>
          <div class="flex gap-2 items-center">
            <input v-model.number="filters.min_price" type="number" placeholder="Min" class="input-sm flex-1" />
            <span class="text-ink/30 text-xs">—</span>
            <input v-model.number="filters.max_price" type="number" placeholder="Max" class="input-sm flex-1" />
            <button @click="fetchProducts" class="px-3 py-1.5 bg-primary/10 text-primary text-xs font-semibold rounded-full hover:bg-primary/20 transition cursor-pointer shrink-0">
              Terapkan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== Grid utama ===== -->
    <div class="grid md:grid-cols-4 gap-6">

      <!-- Sidebar Filter (desktop saja) -->
      <aside class="hidden md:block md:col-span-1 space-y-5">
        <div class="glass-card rounded-xl2 p-4">
          <p class="font-semibold text-ink mb-3">Kategori</p>
          <label
            v-for="cat in categories"
            :key="cat.id"
            class="flex items-center gap-2 py-1.5 text-sm cursor-pointer hover:text-primary transition-colors"
          >
            <input type="radio" :value="cat.id" v-model="filters.category_id" @change="fetchProducts" class="accent-primary" />
            {{ cat.name }}
          </label>
          <button @click="filters.category_id = null; fetchProducts()" class="text-xs text-primary mt-1 hover:underline cursor-pointer">
            Reset kategori
          </button>
        </div>

        <div class="glass-card rounded-xl2 p-4">
          <p class="font-semibold text-ink mb-3">Asal Buah</p>
          <label v-for="opt in [{v:'lokal',l:'Lokal'},{v:'impor',l:'Impor'}]" :key="opt.v" class="flex items-center gap-2 py-1.5 text-sm cursor-pointer hover:text-primary transition-colors">
            <input type="radio" :value="opt.v" v-model="filters.origin_type" @change="fetchProducts" class="accent-primary" />
            {{ opt.l }}
          </label>
        </div>

        <div class="glass-card rounded-xl2 p-4">
          <p class="font-semibold text-ink mb-3">Label</p>
          <label v-for="opt in [{v:'segar',l:'Segar'},{v:'best_seller',l:'Best Seller'},{v:'musiman',l:'Musiman'},{v:'promo',l:'Promo'}]" :key="opt.v" class="flex items-center gap-2 py-1.5 text-sm cursor-pointer hover:text-primary transition-colors">
            <input type="radio" :value="opt.v" v-model="filters.label" @change="fetchProducts" class="accent-primary" />
            {{ opt.l }}
          </label>
        </div>

        <div class="glass-card rounded-xl2 p-4">
          <p class="font-semibold text-ink mb-3">Rentang Harga</p>
          <div class="flex gap-2">
            <input v-model.number="filters.min_price" type="number" placeholder="Min" class="input-sm" />
            <input v-model.number="filters.max_price" type="number" placeholder="Max" class="input-sm" />
          </div>
          <button @click="fetchProducts" class="w-full mt-2 bg-primary/10 text-primary text-sm font-medium py-1.5 rounded-lg hover:bg-primary/20 transition-colors cursor-pointer">
            Terapkan
          </button>
        </div>
      </aside>

      <!-- ===== Hasil Produk ===== -->
      <section class="col-span-full md:col-span-3">
        <!-- Search + sort bar (desktop) -->
        <div class="hidden md:flex flex-col sm:flex-row gap-3 sm:items-center justify-between mb-5">
          <div class="relative flex-1">
            <MagnifyingGlassIcon class="w-4 h-4 text-ink/40 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" stroke-width="1.75" />
            <input
              v-model="filters.search"
              @keyup.enter="fetchProducts"
              type="text"
              placeholder="Cari buah favoritmu..."
              class="w-full bg-white border border-ink/10 rounded-full pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-accent/40"
            />
          </div>
          <select v-model="filters.sort" @change="fetchProducts" class="bg-white border border-ink/10 rounded-full px-4 py-2.5 text-sm cursor-pointer">
            <option value="terbaru">Terbaru</option>
            <option value="harga_termurah">Harga Terendah</option>
            <option value="harga_termahal">Harga Tertinggi</option>
          </select>
        </div>

        <!-- Sort (mobile saja) -->
        <div class="md:hidden mb-4 flex items-center justify-end">
          <select v-model="filters.sort" @change="fetchProducts" class="bg-white border border-ink/10 rounded-full px-3 py-2 text-xs font-medium cursor-pointer">
            <option value="terbaru">Terbaru</option>
            <option value="harga_termurah">Harga ↑</option>
            <option value="harga_termahal">Harga ↓</option>
          </select>
        </div>

        <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div v-for="i in 6" :key="i" class="glass-card rounded-xl2 overflow-hidden animate-pulse">
            <div class="aspect-square bg-ink/10"></div>
            <div class="p-3 space-y-2">
              <div class="h-3 bg-ink/10 rounded w-3/4"></div>
              <div class="h-3 bg-ink/10 rounded w-1/2"></div>
            </div>
          </div>
        </div>
        <p v-else-if="products.length === 0" class="text-ink/50 text-sm py-12 text-center">Produk tidak ditemukan.</p>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4 fade-in">
          <div
            v-for="p in products"
            :key="p.id"
            class="glass-card rounded-xl2 hover:shadow-lg hover:-translate-y-0.5 transition-all overflow-hidden group"
          >
            <router-link :to="`/produk/${p.slug}`">
              <div class="aspect-square bg-primary/5 flex items-center justify-center relative overflow-hidden">
                <img v-if="p.images?.[0]" :src="p.images[0].image_path" class="w-full h-full object-cover" />
                <PhotoIcon v-else class="w-10 h-10 text-ink/20" stroke-width="1.5" />
                <span
                  v-for="label in p.labels || []"
                  :key="label"
                  class="absolute top-2 left-2 bg-badge text-ink text-[10px] font-bold px-2 py-0.5 rounded-full uppercase"
                >
                  {{ labelText(label) }}
                </span>
              </div>
              <div class="p-3 pb-1.5">
                <p class="font-medium text-sm text-ink truncate">{{ p.name }}</p>
                <p class="text-xs text-ink/50">{{ p.unit }}</p>
                <p class="text-accent font-bold mt-1 text-sm">Rp {{ formatPrice(p.price_unit) }}</p>
                <p v-if="p.price_wholesale" class="text-[11px] text-ink/50">
                  Grosir Rp {{ formatPrice(p.price_wholesale) }} (min. {{ p.wholesale_min_qty }})
                </p>
              </div>
            </router-link>
            <div class="px-3 pb-3 pt-0">
              <button
                @click="quickAdd(p)"
                :disabled="addingId === p.id"
                class="w-full flex items-center justify-center gap-1.5 bg-primary hover:bg-primary-dark text-white text-xs font-semibold py-2 rounded-full transition cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
              >
                <ShoppingCartIcon class="w-3.5 h-3.5" stroke-width="2" />
                {{ addingId === p.id ? 'Menambahkan...' : '+ Keranjang' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="meta && meta.last_page > 1" class="flex justify-center gap-2 mt-8">
          <button
            v-for="page in meta.last_page"
            :key="page"
            @click="goToPage(page)"
            class="w-9 h-9 rounded-full text-sm font-medium transition-colors cursor-pointer"
            :class="page === meta.current_page ? 'bg-primary text-white' : 'bg-white border border-ink/20 text-ink/70 hover:border-accent/40'"
          >
            {{ page }}
          </button>
        </div>
      </section>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useCartStore } from '../../stores/cart'
import api from '../../services/api'
import {
  MagnifyingGlassIcon,
  PhotoIcon,
  AdjustmentsHorizontalIcon,
  ChevronDownIcon,
  ShoppingCartIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const auth = useAuthStore()
const cart = useCartStore()

const categories = ref([])
const products = ref([])
const meta = ref(null)
const loading = ref(false)
const showFilters = ref(false)
const addingId = ref(null)

async function quickAdd(p) {
  if (!auth.isLoggedIn) {
    router.push({ path: '/login', query: { redirect: '/katalog', reason: 'Masuk atau daftar dulu untuk mulai belanja 🍊' } })
    return
  }
  addingId.value = p.id
  try {
    await cart.addItem({ product_id: p.id, qty: 1 })
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menambahkan ke keranjang')
  } finally {
    addingId.value = null
  }
}

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

const activeFilterCount = computed(() => {
  let count = 0
  if (filters.value.category_id) count++
  if (filters.value.origin_type) count++
  if (filters.value.label) count++
  if (filters.value.min_price || filters.value.max_price) count++
  return count
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

function resetAllFilters() {
  filters.value.category_id = null
  filters.value.origin_type = null
  filters.value.label = null
  filters.value.min_price = null
  filters.value.max_price = null
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
  @apply w-full bg-white border border-ink/10 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40;
}
</style>
