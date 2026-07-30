<template>
  <Teleport to="body">
    <Transition name="popup">
      <div v-if="visible" class="fixed inset-0 z-[200] flex items-center justify-center p-4" @click.self="close">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>

        <!-- Card -->
        <div class="relative w-full max-w-xs bg-white rounded-2xl shadow-2xl overflow-hidden">
          <!-- Gradient Header -->
          <div class="relative h-32 flex items-center justify-center" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 50%, #15803d 100%);">
            <!-- Decorative circles -->
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-white/10 rounded-full"></div>

            <!-- Animated check circle -->
            <div class="relative">
              <svg class="w-20 h-20 check-animation" viewBox="0 0 80 80">
                <circle class="check-circle" cx="40" cy="40" r="34" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="4" />
                <circle class="check-circle-fill" cx="40" cy="40" r="34" fill="none" stroke="white" stroke-width="4" stroke-linecap="round" />
                <path class="check-mark" d="M24 40 L35 51 L56 30" fill="none" stroke="white" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
          </div>

          <!-- Content -->
          <div class="px-6 pt-5 pb-6 text-center">
            <h3 class="text-lg font-bold text-ink mb-1">{{ title }}</h3>
            <p class="text-sm text-ink/60 mb-5">{{ message }}</p>

            <div class="flex gap-2.5">
              <button
                @click="close"
                class="flex-1 py-2.5 text-sm font-semibold text-ink/60 border border-ink/15 rounded-full hover:bg-ink/5 transition cursor-pointer"
              >
                Lanjut Belanja
              </button>
              <button
                @click="goToCart"
                class="flex-1 py-2.5 text-sm font-bold text-white rounded-full transition cursor-pointer"
                style="background: linear-gradient(135deg, #22c55e, #16a34a);"
              >
                Lihat Keranjang
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const visible = ref(false)
const title = ref('Berhasil!')
const message = ref('')

function show(msg = 'Produk ditambahkan ke keranjang', customTitle = 'Berhasil!') {
  title.value = customTitle
  message.value = msg
  visible.value = true
}

function close() {
  visible.value = false
}

function goToCart() {
  visible.value = false
  router.push('/keranjang')
}

defineExpose({ show, close })
</script>

<style scoped>
.popup-enter-active {
  transition: opacity 0.25s ease;
}
.popup-enter-active .relative {
  animation: popIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) 0.05s both;
}
.popup-leave-active {
  transition: opacity 0.2s ease;
}
.popup-enter-from,
.popup-leave-to {
  opacity: 0;
}

@keyframes popIn {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.check-circle-fill {
  stroke-dasharray: 214;
  stroke-dashoffset: 214;
  animation: circleIn 0.6s ease 0.3s forwards;
}
.check-mark {
  stroke-dasharray: 60;
  stroke-dashoffset: 60;
  animation: checkIn 0.4s ease 0.7s forwards;
}

@keyframes circleIn {
  to { stroke-dashoffset: 0; }
}
@keyframes checkIn {
  to { stroke-dashoffset: 0; }
}
</style>
