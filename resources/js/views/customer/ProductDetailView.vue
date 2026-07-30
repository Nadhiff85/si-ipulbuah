<template>
  <div v-if="product" class="max-w-6xl mx-auto px-4 py-8 grid md:grid-cols-2 gap-8">
    <!-- ===== Galeri Foto ===== -->
    <div>
      <div class="aspect-square bg-primary/5 rounded-xl2 overflow-hidden flex items-center justify-center mb-3">
        <img v-if="activeImage" :src="activeImage" class="w-full h-full object-cover" />
        <PhotoIcon v-else class="w-16 h-16 text-ink/20" stroke-width="1.5" />
      </div>
      <div class="flex gap-2">
        <button
          v-for="img in product.images"
          :key="img.id"
          @click="activeImage = img.image_path"
          class="w-16 h-16 rounded-lg overflow-hidden border-2 cursor-pointer transition"
          :class="activeImage === img.image_path ? 'border-primary' : 'border-transparent'"
        >
          <img :src="img.image_path" class="w-full h-full object-cover" />
        </button>
      </div>
    </div>

    <!-- ===== Info Produk ===== -->
    <div>
      <div class="flex gap-2 mb-2">
        <span v-for="label in product.labels || []" :key="label" class="bg-badge text-ink text-xs font-bold px-2.5 py-1 rounded-full uppercase">
          {{ labelText(label) }}
        </span>
      </div>

      <h1 class="text-2xl font-bold text-ink mb-1">{{ product.name }}</h1>
      <p class="text-ink/50 text-sm mb-4">
        Asal: {{ product.origin_region || '-' }} · {{ product.origin_type === 'lokal' ? 'Buah Lokal' : 'Buah Impor' }}
      </p>

      <div class="flex items-baseline gap-3 mb-1">
        <span class="text-3xl font-extrabold text-accent">Rp {{ formatPrice(product.price_unit) }}</span>
        <span class="text-ink/50 text-sm">/ {{ product.unit }}</span>
      </div>
      <p v-if="wholesale" class="text-sm text-primary font-medium mb-4 flex items-center gap-1.5">
        <TagIcon class="w-4 h-4 shrink-0" stroke-width="1.75" /> Harga grosir Rp {{ formatPrice(wholesale.price) }}/{{ product.unit }} — min. beli {{ wholesale.min_qty }} {{ product.unit }}
      </p>

      <!-- Estimasi Kesegaran -->
      <div v-if="freshnessRemaining !== null" class="glass-card-soft border-success/30 rounded-xl2 p-3 mb-4 text-sm flex items-center gap-1.5">
        <ClockIcon class="w-4 h-4 shrink-0 text-success" stroke-width="1.75" /> Segar hingga <strong>{{ Math.round(freshnessRemaining) }} hari</strong> ke depan
      </div>

      <!-- Varian Ukuran -->
      <div v-if="product.variants?.length" class="mb-4">
        <p class="text-sm font-medium text-ink mb-2">Pilih Ukuran</p>
        <div class="flex gap-2">
          <button
            v-for="v in product.variants"
            :key="v.id"
            @click="selectedVariant = v"
            class="px-4 py-2 rounded-full text-sm border transition cursor-pointer"
            :class="selectedVariant?.id === v.id ? 'bg-primary text-white border-primary' : 'border-ink/20 bg-white text-ink/70 hover:border-accent/40'"
          >
            {{ v.size }} {{ v.price_adjustment > 0 ? `(+Rp${formatPrice(v.price_adjustment)})` : '' }}
          </button>
        </div>
      </div>

      <!-- Qty & Catatan -->
      <div class="flex items-center gap-3 mb-3">
        <p class="text-sm font-medium text-ink">Jumlah</p>
        <div class="flex items-center border border-ink/20 bg-white rounded-full overflow-hidden">
          <button @click="qty > 1 && qty--" class="px-3 py-1.5 hover:bg-ink/5 cursor-pointer transition">-</button>
          <span class="px-4">{{ qty }}</span>
          <button @click="qty++" class="px-3 py-1.5 hover:bg-ink/5 cursor-pointer transition">+</button>
        </div>
      </div>
      <textarea
        v-model="note"
        rows="2"
        placeholder='Catatan khusus (mis. "jangan terlalu matang")'
        class="w-full bg-white border border-ink/20 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-accent/40"
      ></textarea>

      <div class="flex gap-3 mb-6">
        <button
          @click="addToCart"
          :disabled="adding"
          class="flex-1 bg-accent hover:bg-accent-light text-white font-semibold py-3 rounded-full transition disabled:opacity-60 cursor-pointer disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <ShoppingCartIcon class="w-5 h-5" stroke-width="1.75" /> {{ adding ? 'Menambahkan...' : 'Tambah ke Keranjang' }}
        </button>
        <button @click="toggleWishlist" class="w-12 h-12 rounded-full border border-ink/20 bg-white flex items-center justify-center hover:bg-danger/10 cursor-pointer transition">
          <HeartIconSolid v-if="inWishlist" class="w-5 h-5 text-danger" />
          <HeartIcon v-else class="w-5 h-5 text-ink/60" stroke-width="1.75" />
        </button>
      </div>

      <!-- Tips Penyimpanan -->
      <div v-if="product.storage_tips" class="glass-card-soft rounded-xl2 p-4 text-sm text-ink/70 mb-6">
        <p class="font-semibold text-ink mb-1 flex items-center gap-1.5"><LightBulbIcon class="w-4 h-4" stroke-width="1.75" /> Tips Penyimpanan</p>
        {{ product.storage_tips }}
      </div>

      <!-- Ulasan -->
      <div>
        <p class="font-semibold text-ink mb-3">Ulasan Pelanggan ({{ product.reviews?.length || 0 }})</p>
        <div v-if="!product.reviews?.length" class="text-ink/50 text-sm">Belum ada ulasan.</div>
        <div v-for="r in product.reviews" :key="r.id" class="border-t border-ink/5 py-3">
          <div class="flex items-center gap-2 mb-1">
            <span class="font-medium text-sm">{{ r.user.name }}</span>
            <span class="text-badge text-xs">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}</span>
          </div>
          <p class="text-sm text-ink/70">{{ r.comment }}</p>
          <p v-if="r.admin_reply" class="text-xs bg-primary/5 rounded-lg p-2 mt-2 text-ink/60 flex items-start gap-1.5">
            <ChatBubbleLeftRightIcon class="w-3.5 h-3.5 shrink-0 mt-0.5" stroke-width="1.75" /> Balasan Toko: {{ r.admin_reply }}
          </p>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="max-w-6xl mx-auto px-4 py-16 text-center text-ink/50">Memuat produk...</div>

  <SuccessPopup ref="successPopup" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'
import {
  PhotoIcon,
  TagIcon,
  ClockIcon,
  ShoppingCartIcon,
  HeartIcon,
  LightBulbIcon,
  ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline'
import { HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid'
import SuccessPopup from '../../components/shared/SuccessPopup.vue'

const route = useRoute()
const successPopup = ref(null)
const product = ref(null)
const wholesale = ref(null)
const freshnessRemaining = ref(null)
const activeImage = ref(null)
const selectedVariant = ref(null)
const qty = ref(1)
const note = ref('')
const adding = ref(false)
const inWishlist = ref(false)

async function fetchProduct() {
  const { data } = await api.get(`/products/${route.params.slug}`)
  product.value = data.product
  wholesale.value = data.wholesale
  freshnessRemaining.value = data.freshness_remaining
  activeImage.value = data.product.images?.[0]?.image_path || null
  selectedVariant.value = data.product.variants?.[0] || null
}

async function addToCart() {
  adding.value = true
  try {
    await api.post('/cart/items', {
      product_id: product.value.id,
      product_variant_id: selectedVariant.value?.id || null,
      qty: qty.value,
      note: note.value || null,
    })
    successPopup.value?.show(`${product.value.name} (${qty.value}x) ditambahkan ke keranjang`)
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal menambahkan ke keranjang'
    successPopup.value?.show(msg, 'Oops!')
  } finally {
    adding.value = false
  }
}

async function toggleWishlist() {
  await api.post('/wishlist', { product_id: product.value.id })
  inWishlist.value = !inWishlist.value
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

function labelText(label) {
  return { segar: 'Segar', best_seller: 'Best Seller', musiman: 'Musiman', promo: 'Promo' }[label] || label
}

onMounted(fetchProduct)
</script>
