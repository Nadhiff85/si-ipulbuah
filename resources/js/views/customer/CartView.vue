<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-ink mb-6">Keranjang Belanja</h1>

    <div v-if="cart.items.length === 0" class="text-center py-16">
      <p class="text-5xl mb-3">🛒</p>
      <p class="text-ink/50 mb-4">Keranjang Anda masih kosong.</p>
      <router-link to="/katalog" class="bg-primary text-white px-6 py-2.5 rounded-full font-medium hover:bg-primary-dark transition">
        Mulai Belanja
      </router-link>
    </div>

    <div v-else class="grid md:grid-cols-3 gap-6">
      <!-- ===== Daftar Item ===== -->
      <div class="md:col-span-2 space-y-3">
        <div
          v-for="item in cart.items"
          :key="item.id"
          class="bg-white rounded-xl2 border border-ink/5 p-4 flex gap-4"
        >
          <div class="w-20 h-20 rounded-lg bg-primary/5 flex items-center justify-center text-3xl overflow-hidden shrink-0">
            <img v-if="item.image" :src="item.image" class="w-full h-full object-cover" />
            <span v-else>🍎</span>
          </div>

          <div class="flex-1">
            <p class="font-medium text-ink">{{ item.name }}</p>
            <p v-if="item.variant" class="text-xs text-ink/50">Ukuran: {{ item.variant }}</p>
            <p v-if="item.note" class="text-xs text-ink/50 italic">Catatan: "{{ item.note }}"</p>
            <p class="text-accent font-semibold mt-1">Rp {{ formatPrice(item.price) }}</p>

            <div class="flex items-center gap-3 mt-2">
              <div class="flex items-center border border-ink/15 rounded-full overflow-hidden">
                <button @click="changeQty(item, item.qty - 1)" class="px-3 py-1 hover:bg-ink/5">-</button>
                <span class="px-3 text-sm">{{ item.qty }}</span>
                <button @click="changeQty(item, item.qty + 1)" class="px-3 py-1 hover:bg-ink/5">+</button>
              </div>
              <button @click="removeItem(item)" class="text-xs text-danger hover:underline">Hapus</button>
            </div>
          </div>

          <p class="font-semibold text-ink whitespace-nowrap">Rp {{ formatPrice(item.price * item.qty) }}</p>
        </div>
      </div>

      <!-- ===== Ringkasan ===== -->
      <div class="bg-white rounded-xl2 border border-ink/5 p-5 h-fit sticky top-24">
        <p class="font-semibold text-ink mb-4">Ringkasan Belanja</p>
        <div class="flex justify-between text-sm text-ink/70 mb-2">
          <span>Subtotal ({{ cart.itemCount }} item)</span>
          <span>Rp {{ formatPrice(cart.subtotal) }}</span>
        </div>
        <div class="border-t border-ink/10 my-3"></div>
        <div class="flex justify-between font-bold text-ink mb-5">
          <span>Total Belanja</span>
          <span>Rp {{ formatPrice(cart.subtotal) }}</span>
        </div>
        <router-link
          to="/checkout"
          class="block text-center bg-accent hover:bg-accent-light text-white font-semibold py-3 rounded-full transition"
        >
          Lanjut ke Checkout →
        </router-link>
        <router-link to="/katalog" class="block text-center text-primary text-sm mt-3 hover:underline">
          ← Lanjut Belanja
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useCartStore } from '../../stores/cart'

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
