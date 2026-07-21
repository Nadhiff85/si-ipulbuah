<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-ink mb-6">Keranjang Belanja</h1>

    <div v-if="cart.items.length === 0" class="text-center py-16">
      <ShoppingCartIcon class="w-16 h-16 mx-auto mb-3 text-ink/20" stroke-width="1.25" />
      <p class="text-ink/50 mb-4">Keranjang Anda masih kosong.</p>
      <router-link to="/katalog" class="bg-primary text-white px-6 py-2.5 rounded-full font-medium hover:bg-primary-dark transition cursor-pointer">
        Mulai Belanja
      </router-link>
    </div>

    <div v-else class="grid md:grid-cols-3 gap-6">
      <!-- ===== Daftar Item ===== -->
      <div class="md:col-span-2 space-y-3">
        <div
          v-for="item in cart.items"
          :key="item.id"
          class="glass-card rounded-xl2 p-4 flex gap-4"
        >
          <div class="w-20 h-20 rounded-lg bg-primary/5 flex items-center justify-center overflow-hidden shrink-0">
            <img v-if="item.image" :src="item.image" class="w-full h-full object-cover" />
            <PhotoIcon v-else class="w-8 h-8 text-ink/20" stroke-width="1.5" />
          </div>

          <div class="flex-1">
            <p class="font-medium text-ink">{{ item.name }}</p>
            <p v-if="item.variant" class="text-xs text-ink/50">Ukuran: {{ item.variant }}</p>
            <p v-if="item.note" class="text-xs text-ink/50 italic">Catatan: "{{ item.note }}"</p>
            <p class="text-accent font-semibold mt-1 tabular-nums">Rp {{ formatPrice(item.price) }}</p>

            <div class="flex items-center gap-3 mt-2">
              <div class="flex items-center border border-ink/20 bg-white rounded-full overflow-hidden">
                <button @click="changeQty(item, item.qty - 1)" class="px-3 py-1 hover:bg-ink/5 cursor-pointer transition">-</button>
                <span class="px-3 text-sm tabular-nums">{{ item.qty }}</span>
                <button @click="changeQty(item, item.qty + 1)" class="px-3 py-1 hover:bg-ink/5 cursor-pointer transition">+</button>
              </div>
              <button @click="removeItem(item)" class="text-xs text-danger hover:underline cursor-pointer flex items-center gap-1">
                <TrashIcon class="w-3.5 h-3.5" stroke-width="1.75" /> Hapus
              </button>
            </div>
          </div>

          <p class="font-semibold text-ink whitespace-nowrap tabular-nums">Rp {{ formatPrice(item.price * item.qty) }}</p>
        </div>
      </div>

      <!-- ===== Ringkasan ===== -->
      <div class="glass-card rounded-xl2 p-5 h-fit sticky top-24">
        <p class="font-semibold text-ink mb-4">Ringkasan Belanja</p>
        <div class="flex justify-between text-sm text-ink/70 mb-2">
          <span>Subtotal ({{ cart.itemCount }} item)</span>
          <span class="tabular-nums">Rp {{ formatPrice(cart.subtotal) }}</span>
        </div>
        <div class="border-t border-ink/10 my-3"></div>
        <div class="flex justify-between font-bold text-ink mb-5">
          <span>Total Belanja</span>
          <span class="tabular-nums">Rp {{ formatPrice(cart.subtotal) }}</span>
        </div>
        <router-link
          to="/checkout"
          class="flex items-center justify-center gap-1.5 text-center bg-accent hover:bg-accent-light text-white font-semibold py-3 rounded-full transition cursor-pointer"
        >
          Lanjut ke Checkout <ArrowRightIcon class="w-4 h-4" stroke-width="1.75" />
        </router-link>
        <router-link to="/katalog" class="flex items-center justify-center gap-1.5 text-center text-primary text-sm mt-3 hover:underline cursor-pointer">
          <ArrowLeftIcon class="w-3.5 h-3.5" stroke-width="1.75" /> Lanjut Belanja
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useCartStore } from '../../stores/cart'
import {
  ShoppingCartIcon,
  PhotoIcon,
  TrashIcon,
  ArrowRightIcon,
  ArrowLeftIcon,
} from '@heroicons/vue/24/outline'

const cart = useCartStore()

function changeQty(item, newQty) {
  if (newQty < 1) return
  cart.updateItem(item.id, newQty)
}

function removeItem(item) {
  if (confirm('Hapus item ini dari keranjang?')) {
    cart.removeItem(item.id)
  }
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

onMounted(() => cart.fetchCart())
</script>
