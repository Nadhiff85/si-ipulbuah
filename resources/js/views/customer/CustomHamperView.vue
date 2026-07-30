<template>
  <div class="max-w-6xl mx-auto px-4 py-8">

    <!-- ===== Hero Header ===== -->
    <div class="relative rounded-xl2 overflow-hidden mb-6 hero-in">
      <div class="absolute inset-0" style="background: linear-gradient(120deg, #123524 0%, #1F5C3D 55%, #2C7A4F 100%);"></div>
      <!-- Lingkaran dekoratif -->
      <div class="absolute -top-10 -right-6 w-44 h-44 rounded-full bg-white/8 blob-a"></div>
      <div class="absolute -bottom-12 left-16 w-36 h-36 rounded-full bg-badge/20 blob-b"></div>
      <div class="absolute top-6 right-32 w-16 h-16 rounded-full bg-accent/25 blob-c"></div>

      <div class="relative px-6 py-8 sm:px-9 sm:py-10">
        <span class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm text-white/90 text-[11px] font-semibold px-3 py-1 rounded-full mb-3">
          <SparklesIcon class="w-3.5 h-3.5" stroke-width="2" /> Rangkai sesukamu
        </span>
        <h1 class="text-2xl sm:text-3xl font-black text-white flex items-center gap-2.5 mb-1.5">
          <GiftIcon class="w-8 h-8 shrink-0" stroke-width="1.75" />
          Buat Parsel Kustom
        </h1>
        <p class="text-white/70 text-sm max-w-md leading-relaxed">
          Pilih buah favoritmu, tentukan wadah dan kartu ucapan — harga dihitung otomatis secara langsung.
        </p>

        <!-- Indikator langkah -->
        <ol class="flex items-center gap-1 sm:gap-2 mt-7">
          <template v-for="(s, i) in steps" :key="s.label">
            <li
              class="step-chip flex items-center gap-2.5 sm:gap-3 rounded-2xl px-3 py-2.5 sm:px-4 sm:py-3 min-w-0 flex-1 transition-all duration-300"
              :class="[
                s.done ? 'bg-badge shadow-lg shadow-badge/25' : 'bg-white/10 backdrop-blur-sm',
                s.active && !s.done ? 'ring-2 ring-white/45 step-active' : '',
              ]"
              :aria-current="s.active ? 'step' : undefined"
            >
              <!-- Lingkaran nomor / centang -->
              <span
                class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-full shrink-0 flex items-center justify-center font-black text-sm transition-colors duration-300"
                :class="s.done ? 'bg-primary-dark text-badge' : 'bg-white/15 text-white'"
              >
                <Transition name="pop" mode="out-in">
                  <CheckIcon v-if="s.done" key="c" class="w-4 h-4 sm:w-5 sm:h-5" stroke-width="3.5" />
                  <span v-else key="n">{{ i + 1 }}</span>
                </Transition>
              </span>

              <!-- Label + status -->
              <span class="min-w-0 leading-tight">
                <span
                  class="block text-sm sm:text-base font-extrabold truncate transition-colors duration-300"
                  :class="s.done ? 'text-primary-dark' : 'text-white'"
                >{{ s.label }}</span>
                <span
                  class="hidden sm:block text-[11px] font-medium truncate transition-colors duration-300"
                  :class="s.done ? 'text-primary-dark/70' : 'text-white/60'"
                >{{ s.hint }}</span>
              </span>
            </li>

            <!-- Garis penghubung -->
            <li v-if="i < steps.length - 1" class="relative h-1 w-3 sm:w-6 shrink-0 rounded-full bg-white/15 overflow-hidden" aria-hidden="true">
              <span
                class="absolute inset-0 rounded-full bg-badge origin-left transition-transform duration-500 ease-out"
                :class="s.done ? 'scale-x-100' : 'scale-x-0'"
              ></span>
            </li>
          </template>
        </ol>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-5">

        <!-- ===== 1. Pilih Buah ===== -->
        <section class="glass-card rounded-xl2 p-5">
          <header class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
              <span class="w-8 h-8 rounded-lg bg-primary text-white text-sm font-bold flex items-center justify-center shrink-0">1</span>
              <div>
                <h2 class="font-bold text-ink text-sm leading-tight">Pilih Kombinasi Buah</h2>
                <p class="text-[11px] text-ink/45">Bisa pilih lebih dari satu</p>
              </div>
            </div>
            <span
              v-if="selectedCount"
              class="text-[11px] font-bold bg-primary/10 text-primary px-2.5 py-1 rounded-full tabular-nums"
            >
              {{ selectedCount }} dipilih
            </span>
          </header>

          <!-- Skeleton -->
          <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div v-for="i in 6" :key="i" class="rounded-xl2 border border-ink/8 p-3 animate-pulse">
              <div class="w-11 h-11 rounded-full bg-ink/8 mb-2.5"></div>
              <div class="h-3 bg-ink/8 rounded w-4/5 mb-2"></div>
              <div class="h-2.5 bg-ink/8 rounded w-2/3"></div>
            </div>
          </div>

          <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <button
              v-for="(p, i) in products"
              :key="p.id"
              type="button"
              @click="toggleProduct(p)"
              class="fruit-card group relative text-left rounded-xl2 border-2 p-3 cursor-pointer"
              :class="isSelected(p) ? 'border-primary bg-primary/8 shadow-sm' : 'border-ink/10 bg-white hover:border-primary/35 hover:shadow-sm'"
              :style="{ animationDelay: `${Math.min(i * 35, 500)}ms` }"
              :aria-pressed="isSelected(p)"
            >
              <!-- Badge centang -->
              <Transition name="pop">
                <span
                  v-if="isSelected(p)"
                  class="absolute top-2 right-2 w-5 h-5 rounded-full bg-primary text-white flex items-center justify-center shadow-sm"
                >
                  <CheckIcon class="w-3 h-3" stroke-width="3" />
                </span>
              </Transition>

              <!-- Foto buah (jatuh ke gradien warna kalau produk belum berfoto) -->
              <span class="block w-12 h-12 rounded-full mb-2.5 overflow-hidden ring-1 ring-ink/10 shadow-sm transition-transform duration-200 group-hover:scale-105">
                <img
                  v-if="fruitImage(p)"
                  :src="fruitImage(p)"
                  :alt="p.name"
                  class="w-full h-full object-cover"
                  loading="lazy"
                />
                <span v-else class="block w-full h-full" :style="fruitGradient(p)" aria-hidden="true"></span>
              </span>

              <p class="font-semibold text-ink text-sm leading-snug pr-5">{{ p.name }}</p>
              <p class="text-ink/45 text-xs tabular-nums mt-0.5">Rp {{ formatPrice(p.price_unit) }}/{{ p.unit }}</p>

              <!-- Stepper jumlah -->
              <Transition name="expand">
                <div v-if="isSelected(p)" class="overflow-hidden">
                  <div
                    class="flex items-center justify-between gap-1 mt-2.5 pt-2.5 border-t border-primary/15"
                    @click.stop
                  >
                    <button
                      type="button"
                      @click.stop="changeQty(p, -0.5)"
                      :disabled="selectedProducts[p.id].qty <= 0.5"
                      class="w-7 h-7 rounded-full border border-primary/25 bg-white text-primary font-bold leading-none flex items-center justify-center hover:bg-primary/10 active:scale-90 transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                      aria-label="Kurangi jumlah"
                    >−</button>
                    <span class="text-xs font-bold text-ink tabular-nums">{{ formatQty(selectedProducts[p.id].qty) }} {{ p.unit }}</span>
                    <button
                      type="button"
                      @click.stop="changeQty(p, 0.5)"
                      class="w-7 h-7 rounded-full border border-primary/25 bg-white text-primary font-bold leading-none flex items-center justify-center hover:bg-primary/10 active:scale-90 transition cursor-pointer"
                      aria-label="Tambah jumlah"
                    >+</button>
                  </div>
                </div>
              </Transition>
            </button>
          </div>
        </section>

        <!-- ===== 2. Pilih Wadah ===== -->
        <section class="glass-card rounded-xl2 p-5">
          <header class="flex items-center gap-2.5 mb-4">
            <span class="w-8 h-8 rounded-lg bg-primary text-white text-sm font-bold flex items-center justify-center shrink-0">2</span>
            <div>
              <h2 class="font-bold text-ink text-sm leading-tight">Pilih Wadah</h2>
              <p class="text-[11px] text-ink/45">Kemasan untuk parselmu</p>
            </div>
          </header>

          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <button
              v-for="c in containers"
              :key="c.id"
              type="button"
              @click="selectedContainer = selectedContainer?.id === c.id ? null : c"
              class="option-card relative rounded-xl2 border-2 p-3.5 text-left cursor-pointer transition-all duration-200 active:scale-[0.97]"
              :class="selectedContainer?.id === c.id ? 'border-primary bg-primary/8 shadow-sm' : 'border-ink/10 bg-white hover:border-primary/35'"
              :aria-pressed="selectedContainer?.id === c.id"
            >
              <Transition name="pop">
                <span v-if="selectedContainer?.id === c.id" class="absolute top-2 right-2 w-5 h-5 rounded-full bg-primary text-white flex items-center justify-center">
                  <CheckIcon class="w-3 h-3" stroke-width="3" />
                </span>
              </Transition>
              <span class="w-9 h-9 rounded-lg bg-badge/15 flex items-center justify-center mb-2">
                <ArchiveBoxIcon class="w-5 h-5 text-badge" stroke-width="1.75" />
              </span>
              <p class="font-semibold text-ink text-sm leading-snug pr-5">{{ c.name }}</p>
              <p class="text-ink/45 text-xs tabular-nums mt-0.5">+Rp {{ formatPrice(c.extra_price) }}</p>
            </button>
          </div>
        </section>

        <!-- ===== 3. Kartu Ucapan ===== -->
        <section class="glass-card rounded-xl2 p-5">
          <header class="flex items-center gap-2.5 mb-4">
            <span class="w-8 h-8 rounded-lg bg-primary text-white text-sm font-bold flex items-center justify-center shrink-0">3</span>
            <div>
              <h2 class="font-bold text-ink text-sm leading-tight">Kartu Ucapan</h2>
              <p class="text-[11px] text-ink/45">Opsional — untuk pesan spesial</p>
            </div>
          </header>

          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <button
              type="button"
              @click="selectedCard = null; cardMessage = ''"
              class="option-card relative rounded-xl2 border-2 p-3.5 text-left cursor-pointer transition-all duration-200 active:scale-[0.97]"
              :class="!selectedCard ? 'border-primary bg-primary/8 shadow-sm' : 'border-ink/10 bg-white hover:border-primary/35'"
              :aria-pressed="!selectedCard"
            >
              <span class="w-9 h-9 rounded-lg bg-ink/5 flex items-center justify-center mb-2">
                <NoSymbolIcon class="w-5 h-5 text-ink/40" stroke-width="1.75" />
              </span>
              <p class="font-semibold text-ink text-sm leading-snug">Tanpa Kartu</p>
              <p class="text-ink/45 text-xs mt-0.5">Gratis</p>
            </button>

            <button
              v-for="c in cards"
              :key="c.id"
              type="button"
              @click="selectedCard = c"
              class="option-card relative rounded-xl2 border-2 p-3.5 text-left cursor-pointer transition-all duration-200 active:scale-[0.97]"
              :class="selectedCard?.id === c.id ? 'border-primary bg-primary/8 shadow-sm' : 'border-ink/10 bg-white hover:border-primary/35'"
              :aria-pressed="selectedCard?.id === c.id"
            >
              <Transition name="pop">
                <span v-if="selectedCard?.id === c.id" class="absolute top-2 right-2 w-5 h-5 rounded-full bg-primary text-white flex items-center justify-center">
                  <CheckIcon class="w-3 h-3" stroke-width="3" />
                </span>
              </Transition>
              <span class="w-9 h-9 rounded-lg bg-accent/10 flex items-center justify-center mb-2">
                <EnvelopeIcon class="w-5 h-5 text-accent" stroke-width="1.75" />
              </span>
              <p class="font-semibold text-ink text-sm leading-snug pr-5">{{ c.name }}</p>
              <p class="text-ink/45 text-xs tabular-nums mt-0.5">+Rp {{ formatPrice(c.extra_price) }}</p>
            </button>
          </div>

          <Transition name="expand">
            <div v-if="selectedCard" class="overflow-hidden">
              <div class="mt-4">
                <label for="card-message" class="block text-xs font-semibold text-ink/60 mb-1.5">Pesan Ucapan</label>
                <textarea
                  id="card-message"
                  v-model="cardMessage"
                  rows="3"
                  maxlength="200"
                  placeholder="mis. Selamat ulang tahun, semoga sehat selalu!"
                  class="w-full bg-white border border-ink/12 rounded-xl px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 transition"
                ></textarea>
                <p class="text-[11px] text-ink/40 text-right mt-1 tabular-nums">{{ cardMessage.length }}/200</p>
              </div>
            </div>
          </Transition>
        </section>
      </div>

      <!-- ===== Ringkasan (sticky) ===== -->
      <aside class="lg:sticky lg:top-24 h-fit">
        <div class="glass-card rounded-xl2 overflow-hidden">

          <!-- Pratinjau keranjang parsel -->
          <div class="relative h-60 overflow-hidden" style="background: linear-gradient(170deg, #FFF6EC 0%, #FFFFFF 70%);">
            <div class="absolute inset-x-0 top-3 text-center">
              <p class="text-[11px] font-semibold text-ink/40 tracking-wide uppercase">Pratinjau Parsel</p>
            </div>

            <!-- Keranjang + isinya. Buah diposisikan dalam persen terhadap kotak
                 ini, jadi tetap pas di dalam bowl berapa pun lebar sidebar. -->
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[215px]">
              <img
                src="/images/keranjang.webp"
                alt="Keranjang parsel"
                class="w-full h-auto select-none pointer-events-none"
                draggable="false"
              />

              <!-- Isi keranjang: foto buah asli -->
              <TransitionGroup name="fruit-pop" tag="div">
                <span
                  v-for="(sp, i) in previewFruits"
                  :key="sp.product.id"
                  class="absolute w-9 h-9"
                  :style="slotStyle(i)"
                  :title="sp.product.name"
                >
                  <span
                    class="block w-full h-full rounded-full overflow-hidden shadow-md ring-2 ring-white/90 float-fruit"
                    :style="{ animationDelay: `${i * 220}ms` }"
                  >
                    <img
                      v-if="fruitImage(sp.product)"
                      :src="fruitImage(sp.product)"
                      :alt="sp.product.name"
                      class="w-full h-full object-cover"
                      loading="lazy"
                    />
                    <span v-else class="block w-full h-full" :style="fruitGradient(sp.product)"></span>
                  </span>
                </span>
              </TransitionGroup>

              <!-- Kondisi kosong: ditaruh di dalam bowl keranjang -->
              <Transition name="fade">
                <p
                  v-if="selectedCount === 0"
                  class="absolute inset-x-0 top-[58%] -translate-y-1/2 text-center text-[11px] font-medium text-ink/40 px-10 leading-snug"
                >
                  Keranjang masih kosong
                </p>
              </Transition>
            </div>

            <span
              v-if="extraFruitCount > 0"
              class="absolute bottom-3 right-4 z-20 text-[11px] font-bold text-primary bg-white px-2 py-0.5 rounded-full shadow tabular-nums"
            >+{{ extraFruitCount }} lagi</span>
          </div>

          <div class="p-5">
            <h2 class="font-bold text-ink text-sm mb-3">Ringkasan Parsel</h2>

            <!-- Daftar item -->
            <div v-if="selectedCount === 0" class="text-ink/40 text-sm py-3 text-center border border-dashed border-ink/15 rounded-xl mb-4">
              Belum ada buah dipilih
            </div>

            <TransitionGroup v-else name="list" tag="div" class="space-y-2 mb-3">
              <div v-for="sp in selectedList" :key="sp.product.id" class="flex items-start justify-between gap-2 text-sm">
                <span class="flex items-start gap-2 min-w-0">
                  <span
                    class="w-3.5 h-3.5 rounded-full shrink-0 mt-0.5"
                    :style="{ background: `linear-gradient(135deg, ${fruitColor(sp.product.name).from}, ${fruitColor(sp.product.name).to})` }"
                  ></span>
                  <span class="text-ink/75 leading-snug min-w-0">
                    {{ sp.product.name }}
                    <span class="text-ink/40 tabular-nums">({{ formatQty(sp.qty) }} {{ sp.product.unit }})</span>
                  </span>
                </span>
                <span class="text-ink/75 tabular-nums shrink-0">Rp {{ formatPrice(sp.product.price_unit * sp.qty) }}</span>
              </div>
            </TransitionGroup>

            <!-- Tambahan -->
            <div v-if="selectedContainer || selectedCard" class="space-y-2 pt-3 border-t border-ink/8 mb-3 text-sm">
              <div v-if="selectedContainer" class="flex justify-between gap-2">
                <span class="text-ink/60 flex items-center gap-1.5 min-w-0">
                  <ArchiveBoxIcon class="w-3.5 h-3.5 shrink-0 text-badge" stroke-width="2" />
                  <span class="truncate">{{ selectedContainer.name }}</span>
                </span>
                <span class="text-ink/75 tabular-nums shrink-0">Rp {{ formatPrice(selectedContainer.extra_price) }}</span>
              </div>
              <div v-if="selectedCard" class="flex justify-between gap-2">
                <span class="text-ink/60 flex items-center gap-1.5 min-w-0">
                  <EnvelopeIcon class="w-3.5 h-3.5 shrink-0 text-accent" stroke-width="2" />
                  <span class="truncate">{{ selectedCard.name }}</span>
                </span>
                <span class="text-ink/75 tabular-nums shrink-0">Rp {{ formatPrice(selectedCard.extra_price) }}</span>
              </div>
            </div>

            <!-- Total -->
            <div class="border-t border-ink/10 pt-3 mb-4 flex items-end justify-between">
              <span class="text-sm font-semibold text-ink">Total Estimasi</span>
              <span :key="totalPrice" class="text-xl font-black text-accent tabular-nums total-bump">
                Rp {{ formatPrice(totalPrice) }}
              </span>
            </div>

            <button
              @click="addCustomHamperToCart"
              :disabled="selectedCount === 0 || adding"
              class="w-full inline-flex items-center justify-center gap-2 text-white font-bold py-3.5 rounded-full transition-all duration-200 active:scale-[0.97] disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer shadow-sm hover:shadow-md"
              style="background: linear-gradient(135deg, #FF5A36, #E8431F);"
            >
              <ShoppingCartIcon v-if="!adding" class="w-5 h-5" stroke-width="2" />
              <ArrowPathIcon v-else class="w-5 h-5 animate-spin" stroke-width="2" />
              {{ adding ? 'Menambahkan...' : 'Tambah ke Keranjang' }}
            </button>

            <p v-if="selectedCount === 0" class="text-[11px] text-ink/40 text-center mt-2">
              Pilih minimal satu buah untuk melanjutkan
            </p>
          </div>
        </div>
      </aside>
    </div>

    <SuccessPopup ref="successPopup" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import SuccessPopup from '../../components/shared/SuccessPopup.vue'
import {
  GiftIcon,
  ShoppingCartIcon,
  CheckIcon,
  SparklesIcon,
  ArchiveBoxIcon,
  EnvelopeIcon,
  NoSymbolIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'

const products = ref([])
const containers = ref([])
const cards = ref([])
const loading = ref(true)

const selectedProducts = ref({}) // { [product_id]: { product, qty } }
const selectedContainer = ref(null)
const selectedCard = ref(null)
const cardMessage = ref('')
const adding = ref(false)
const successPopup = ref(null)

// ── Warna buah ────────────────────────────────────────────────────────────────
// Tiap buah dapat gradien khas supaya pratinjau parsel terbaca sekilas
// tanpa perlu foto (endpoint custom-options hanya mengirim nama & harga).
const FRUIT_PALETTE = [
  { keys: ['mangga'], from: '#FBBF24', to: '#EA9A0B' },
  { keys: ['jeruk', 'sunkist'], from: '#FDBA74', to: '#F97316' },
  { keys: ['pisang'], from: '#FDE047', to: '#EAB308' },
  { keys: ['pepaya'], from: '#FDBA74', to: '#F43F5E' },
  { keys: ['nanas'], from: '#FDE047', to: '#CA8A04' },
  { keys: ['rambutan'], from: '#F87171', to: '#C81E1E' },
  { keys: ['apel'], from: '#FB7185', to: '#B91C1C' },
  { keys: ['anggur'], from: '#A78BFA', to: '#6D28D9' },
  { keys: ['kiwi'], from: '#A3E635', to: '#4D7C0F' },
  { keys: ['durian'], from: '#D9F99D', to: '#65A30D' },
  { keys: ['duku', 'salak', 'kelengkeng'], from: '#DDC29B', to: '#92703C' },
  { keys: ['semangka'], from: '#FB7185', to: '#15803D' },
  { keys: ['melon'], from: '#BBF7D0', to: '#22C55E' },
  { keys: ['alpukat'], from: '#86EFAC', to: '#166534' },
  { keys: ['naga'], from: '#F472B6', to: '#BE185D' },
  { keys: ['stroberi', 'strawberry'], from: '#FDA4AF', to: '#E11D48' },
  { keys: ['pir'], from: '#D9F99D', to: '#A3B518' },
  { keys: ['jambu'], from: '#FCA5A5', to: '#DC2626' },
  { keys: ['sawit'], from: '#F87171', to: '#7F1D1D' },
  { keys: ['nangka'], from: '#FDE68A', to: '#D97706' },
  { keys: ['sirsak'], from: '#BBF7D0', to: '#15803D' },
]
const FALLBACK_PALETTE = [
  { from: '#FDBA74', to: '#EA580C' },
  { from: '#A3E635', to: '#4D7C0F' },
  { from: '#FCA5A5', to: '#DC2626' },
  { from: '#FDE047', to: '#CA8A04' },
  { from: '#C4B5FD', to: '#7C3AED' },
]

function fruitColor(name = '') {
  const lower = name.toLowerCase()
  const found = FRUIT_PALETTE.find((p) => p.keys.some((k) => lower.includes(k)))
  if (found) return found
  // Hash sederhana supaya buah yang sama selalu dapat warna yang sama
  const hash = [...lower].reduce((acc, ch) => acc + ch.charCodeAt(0), 0)
  return FALLBACK_PALETTE[hash % FALLBACK_PALETTE.length]
}

// ── Seleksi ───────────────────────────────────────────────────────────────────
function isSelected(p) {
  return !!selectedProducts.value[p.id]
}

function toggleProduct(p) {
  if (selectedProducts.value[p.id]) {
    delete selectedProducts.value[p.id]
  } else {
    selectedProducts.value[p.id] = { product: p, qty: 1 }
  }
}

function changeQty(p, delta) {
  const item = selectedProducts.value[p.id]
  if (!item) return
  item.qty = Math.max(0.5, Math.round((item.qty + delta) * 10) / 10)
}

const selectedList = computed(() => Object.values(selectedProducts.value))
const selectedCount = computed(() => selectedList.value.length)

const steps = computed(() => {
  const list = [
    {
      label: 'Buah',
      done: selectedCount.value > 0,
      hint: selectedCount.value > 0 ? `${selectedCount.value} jenis dipilih` : 'Belum dipilih',
    },
    {
      label: 'Wadah',
      done: !!selectedContainer.value,
      hint: selectedContainer.value?.name ?? 'Belum dipilih',
    },
    {
      label: 'Kartu',
      done: !!selectedCard.value,
      hint: selectedCard.value?.name ?? 'Opsional',
    },
  ]

  // Sorot langkah pertama yang belum selesai supaya mata pengguna terarah
  const nextIndex = list.findIndex((s) => !s.done)
  return list.map((s, i) => ({ ...s, active: i === nextIndex }))
})

const totalPrice = computed(() => {
  let total = selectedList.value.reduce((sum, sp) => sum + sp.product.price_unit * sp.qty, 0)
  if (selectedContainer.value) total += Number(selectedContainer.value.extra_price) || 0
  if (selectedCard.value) total += Number(selectedCard.value.extra_price) || 0
  return total
})

// ── Pratinjau keranjang ───────────────────────────────────────────────────────
const MAX_PREVIEW = 8
const previewFruits = computed(() => selectedList.value.slice(0, MAX_PREVIEW))
const extraFruitCount = computed(() => Math.max(0, selectedCount.value - MAX_PREVIEW))

// Titik tengah tiap buah dalam PERSEN terhadap foto keranjang, jadi posisinya
// tetap pas di dalam bowl berapa pun lebar sidebar. Bowl foto ini kira-kira
// membentang di x 19-80% dan y 49-71%. Slot dibuat tetap per indeks supaya
// buah tidak "melompat" saat daftar pilihan berubah.
const FRUIT_SIZE = 36
const PREVIEW_SLOTS = [
  { x: 50, y: 52 },
  { x: 36, y: 54 },
  { x: 64, y: 54 },
  { x: 26, y: 60 },
  { x: 74, y: 60 },
  { x: 43, y: 61 },
  { x: 57, y: 61 },
  { x: 50, y: 67 },
]

// Posisi dipasang lewat left/top + margin (bukan transform) supaya transform
// tetap bebas dipakai animasi pop saat buah masuk/keluar.
function slotStyle(i) {
  const slot = PREVIEW_SLOTS[i] ?? PREVIEW_SLOTS[PREVIEW_SLOTS.length - 1]
  return {
    left: `${slot.x}%`,
    top: `${slot.y}%`,
    marginLeft: `-${FRUIT_SIZE / 2}px`,
    marginTop: `-${FRUIT_SIZE / 2}px`,
    // Makin ke depan (y besar) makin di atas tumpukan
    zIndex: Math.round(slot.y),
  }
}

// Foto produk asli; null kalau produk belum punya foto
function fruitImage(product) {
  return product?.images?.[0]?.image_path || null
}

// Cadangan warna saat produk belum punya foto
function fruitGradient(product) {
  const c = fruitColor(product?.name ?? '')
  return { background: `linear-gradient(135deg, ${c.from}, ${c.to})` }
}

// ── Aksi ──────────────────────────────────────────────────────────────────────
async function addCustomHamperToCart() {
  adding.value = true
  try {
    await api.post('/cart/items', {
      hamper_id: null,
      qty: 1,
      custom_hamper_config: {
        products: selectedList.value.map((sp) => ({ product_id: sp.product.id, qty: sp.qty })),
        container_id: selectedContainer.value?.id || null,
        card_id: selectedCard.value?.id || null,
        card_message: cardMessage.value || null,
        estimated_price: totalPrice.value,
      },
      note: 'Parsel Kustom',
    })
    successPopup.value?.show(`Parsel kustom berisi ${selectedCount.value} jenis buah ditambahkan ke keranjang`)
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal menambahkan parsel ke keranjang'
    successPopup.value?.show(msg, 'Oops!')
  } finally {
    adding.value = false
  }
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v || 0)
}

function formatQty(v) {
  return Number.isInteger(v) ? v : v.toFixed(1).replace('.', ',')
}

onMounted(async () => {
  try {
    const { data } = await api.get('/hampers/custom-options')
    products.value = data.products
    containers.value = data.containers
    cards.value = data.cards
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* ── Masuknya konten ─────────────────────────────────────────────────────── */
.hero-in {
  animation: slideUp 340ms cubic-bezier(0.22, 1, 0.36, 1) both;
}
.fruit-card {
  animation: slideUp 320ms cubic-bezier(0.22, 1, 0.36, 1) both;
  transition: border-color 200ms ease, background-color 200ms ease, box-shadow 200ms ease, transform 160ms ease;
}
.fruit-card:active {
  transform: scale(0.97);
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: none; }
}

/* ── Indikator langkah ───────────────────────────────────────────────────── */
.step-chip:nth-of-type(1) { animation: slideUp 340ms cubic-bezier(0.22, 1, 0.36, 1) 120ms both; }
.step-chip:nth-of-type(2) { animation: slideUp 340ms cubic-bezier(0.22, 1, 0.36, 1) 190ms both; }
.step-chip:nth-of-type(3) { animation: slideUp 340ms cubic-bezier(0.22, 1, 0.36, 1) 260ms both; }

/* Langkah yang sedang berjalan berdenyut halus untuk menarik perhatian */
.step-active { animation: haloPulse 2.4s ease-in-out infinite 700ms; }
@keyframes haloPulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
  50%      { box-shadow: 0 0 0 5px rgba(255, 255, 255, 0.12); }
}

/* ── Badge centang ───────────────────────────────────────────────────────── */
.pop-enter-active { transition: transform 220ms cubic-bezier(0.34, 1.56, 0.64, 1), opacity 160ms ease; }
.pop-leave-active { transition: transform 140ms ease-in, opacity 140ms ease-in; }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: scale(0.4); }

/* ── Buka/tutup stepper & textarea ───────────────────────────────────────── */
.expand-enter-active { transition: max-height 240ms ease-out, opacity 200ms ease-out; }
.expand-leave-active { transition: max-height 160ms ease-in, opacity 140ms ease-in; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { opacity: 1; max-height: 220px; }

/* ── Buah muncul di pratinjau ────────────────────────────────────────────── */
.fruit-pop-enter-active { transition: transform 300ms cubic-bezier(0.34, 1.56, 0.64, 1), opacity 200ms ease; }
.fruit-pop-leave-active { transition: transform 180ms ease-in, opacity 180ms ease-in; position: absolute; }
.fruit-pop-enter-from { opacity: 0; transform: translateY(18px) scale(0.4); }
.fruit-pop-leave-to   { opacity: 0; transform: translateY(-10px) scale(0.5); }
.fruit-pop-move { transition: transform 260ms ease; }

.float-fruit {
  animation: bob 3.4s ease-in-out infinite;
}
@keyframes bob {
  0%, 100% { transform: translateY(0); }
  50%      { transform: translateY(-5px); }
}

/* ── Daftar ringkasan ────────────────────────────────────────────────────── */
.list-enter-active { transition: transform 240ms ease-out, opacity 200ms ease-out; }
.list-leave-active { transition: transform 160ms ease-in, opacity 160ms ease-in; position: absolute; }
.list-enter-from { opacity: 0; transform: translateX(-10px); }
.list-leave-to   { opacity: 0; transform: translateX(10px); }
.list-move { transition: transform 220ms ease; }

/* ── Total berdenyut saat berubah ────────────────────────────────────────── */
.total-bump { animation: bump 260ms ease-out; }
@keyframes bump {
  0%   { transform: scale(1); }
  45%  { transform: scale(1.07); }
  100% { transform: scale(1); }
}

.fade-enter-active, .fade-leave-active { transition: opacity 200ms ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── Dekorasi hero ───────────────────────────────────────────────────────── */
.blob-a { animation: drift 9s ease-in-out infinite; }
.blob-b { animation: drift 11s ease-in-out infinite reverse; }
.blob-c { animation: drift 7s ease-in-out infinite 0.5s; }
@keyframes drift {
  0%, 100% { transform: translate(0, 0); }
  50%      { transform: translate(-10px, 12px); }
}

/* ── Hormati preferensi kurangi gerak ────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
  .hero-in, .fruit-card, .float-fruit, .blob-a, .blob-b, .blob-c, .total-bump,
  .step-chip, .step-active {
    animation: none !important;
  }
  .pop-enter-active, .pop-leave-active,
  .expand-enter-active, .expand-leave-active,
  .fruit-pop-enter-active, .fruit-pop-leave-active, .fruit-pop-move,
  .list-enter-active, .list-leave-active, .list-move,
  .fade-enter-active, .fade-leave-active {
    transition-duration: 1ms !important;
  }
}
</style>
