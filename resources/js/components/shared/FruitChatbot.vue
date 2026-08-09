<template>
  <!-- Tombol Chatbot - sejajar dengan tombol WhatsApp di bawahnya -->
  <div class="fixed bottom-[10.25rem] md:bottom-[5.75rem] right-3 z-40">
    <!-- Tooltip -->
    <span v-if="!open" class="chatbot-tooltip">Tanya AI 🍊</span>
    <button
      @click="open = !open"
      class="relative w-[72px] h-[72px] flex items-center justify-center transition-transform duration-300 cursor-pointer hover:scale-110 active:scale-95"
      title="Tanya Asisten Buah AI"
    >
      <div v-if="open" class="w-14 h-14 rounded-full bg-red-500 flex items-center justify-center shadow-xl">
        <XMarkIcon class="w-6 h-6 text-white" stroke-width="2" />
      </div>
      <img v-else src="/images/ikon-ipul-square.png" alt="Asisten Buah" class="w-[72px] h-[72px] object-contain chatbot-logo" />
    </button>
  </div>

  <!-- Panel Chat -->
  <Transition name="chat">
    <div v-if="open" class="fixed bottom-[12rem] md:bottom-[8.5rem] right-3 z-40 w-80 sm:w-96 max-h-[70vh] flex flex-col bg-white rounded-2xl shadow-2xl border border-ink/10 overflow-hidden">
      <!-- Header -->
      <div class="bg-primary px-4 py-3 flex items-center gap-3 shrink-0">
        <img src="/images/ikon-ipul-square.png" alt="Asisten Buah" class="w-10 h-10 object-contain rounded-full bg-white/20 p-0.5" />
        <div>
          <p class="text-white font-bold text-sm">Asisten Buah IPUL</p>
          <p class="text-white/70 text-[11px]">AI · Rekomendasi buah & olahan</p>
        </div>
      </div>

      <!-- Messages -->
      <div ref="messagesRef" class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[200px] max-h-[45vh]">
        <div v-for="(msg, i) in messages" :key="i"
          :class="msg.from === 'bot' ? 'flex justify-start' : 'flex justify-end'"
        >
          <div
            :class="msg.from === 'bot'
              ? 'bg-surface-soft text-ink rounded-2xl rounded-tl-sm max-w-[85%]'
              : 'bg-primary text-white rounded-2xl rounded-tr-sm max-w-[85%]'"
            class="px-3.5 py-2.5 text-sm leading-relaxed"
          >
            <div v-html="msg.text"></div>
            <!-- Produk rekomendasi dari API -->
            <div v-if="msg.products?.length" class="mt-2 space-y-1.5">
              <button
                v-for="prod in msg.products" :key="prod.slug"
                @click="goToProduk(prod.slug)"
                class="w-full text-left bg-white/90 hover:bg-white text-ink rounded-lg px-3 py-2 text-xs flex items-center gap-2 transition cursor-pointer border border-ink/5"
              >
                <img
                  v-if="prod.image"
                  :src="prod.image"
                  class="w-11 h-11 rounded-lg object-cover shrink-0 bg-surface-soft"
                  :alt="prod.name"
                  @error="$event.target.style.display = 'none'"
                />
                <span v-else class="w-11 h-11 rounded-lg bg-surface-soft flex items-center justify-center text-lg shrink-0">🍊</span>
                <div class="flex-1 min-w-0">
                  <p class="font-semibold truncate">{{ prod.name }}</p>
                  <p class="text-ink/50">Rp {{ formatPrice(prod.price) }}/{{ prod.unit }}</p>
                </div>
                <ArrowRightIcon class="w-3.5 h-3.5 text-primary shrink-0" stroke-width="2" />
              </button>
            </div>
          </div>
        </div>
        <div v-if="typing" class="flex justify-start">
          <div class="bg-surface-soft text-ink rounded-2xl rounded-tl-sm px-4 py-3">
            <span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>
          </div>
        </div>
      </div>

      <!-- Quick Topics -->
      <div v-if="messages.length <= 1" class="px-4 pb-2 flex flex-wrap gap-1.5">
        <button
          v-for="q in quickTopics" :key="q.label"
          @click="sendMessage(q.label)"
          class="text-[11px] font-medium bg-primary/10 text-primary px-3 py-1.5 rounded-full hover:bg-primary/20 transition cursor-pointer"
        >
          {{ q.label }}
        </button>
      </div>

      <!-- Input -->
      <div class="border-t border-ink/10 p-3 flex gap-2 shrink-0">
        <input
          v-model="input"
          @keydown.enter="sendMessage(input)"
          type="text"
          placeholder="Tanya tentang buah..."
          class="flex-1 bg-surface-soft rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40"
          :disabled="typing"
        />
        <button
          @click="sendMessage(input)"
          :disabled="!input.trim() || typing"
          class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center hover:bg-primary-dark transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shrink-0"
        >
          <PaperAirplaneIcon class="w-4 h-4" stroke-width="2" />
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import {
  ChatBubbleBottomCenterTextIcon,
  XMarkIcon,
  PaperAirplaneIcon,
  ArrowRightIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const open = ref(false)
const input = ref('')
const typing = ref(false)
const messagesRef = ref(null)

const messages = ref([
  { from: 'bot', text: 'Halo! 👋 Saya asisten buah IPUL BUAH. Mau tanya soal buah apa? Saya bisa bantu rekomendasi buah dan ide olahannya!' },
])

const quickTopics = [
  { label: '🧃 Rekomendasi jus' },
  { label: '🥗 Buah untuk rujak' },
  { label: '🎁 Buah untuk parsel' },
  { label: '💪 Buah untuk diet' },
  { label: '👶 Buah untuk anak' },
  { label: '🍹 Ide smoothie' },
]

// ============ LOCAL FALLBACK KB ============
const fruitKB = [
  { name: 'Mangga Harum Manis', slug: 'mangga-harum-manis', emoji: '🥭', price: 22500, unit: 'kg',
    tags: ['jus', 'smoothie', 'rujak', 'es', 'manis', 'segar'], olahan: ['Jus mangga segar', 'Smoothie mangga-yogurt', 'Rujak manis'], manfaat: 'Kaya vitamin C & A.' },
  { name: 'Jeruk Baby Pontianak', slug: 'jeruk-baby-pontianak', emoji: '🍊', price: 18000, unit: 'kg',
    tags: ['jus', 'segar', 'vitamin', 'anak', 'diet'], olahan: ['Jus jeruk segar', 'Infused water'], manfaat: 'Vitamin C tinggi.' },
  { name: 'Semangka Belah', slug: 'semangka-belah', emoji: '🍉', price: 15000, unit: 'pcs',
    tags: ['jus', 'es', 'segar', 'diet'], olahan: ['Jus semangka', 'Es semangka'], manfaat: 'Hidrasi tinggi, rendah kalori.' },
  { name: 'Apel Fuji', slug: 'apel-fuji', emoji: '🍎', price: 38000, unit: 'kg',
    tags: ['jus', 'salad', 'diet', 'parsel'], olahan: ['Jus apel', 'Salad buah'], manfaat: 'Serat tinggi.' },
  { name: 'Pisang Kepok', slug: 'pisang-kepok', emoji: '🍌', price: 15000, unit: 'sisir',
    tags: ['goreng', 'kolak', 'anak', 'olahan'], olahan: ['Pisang goreng crispy', 'Kolak pisang'], manfaat: 'Sumber kalium & energi.' },
  { name: 'Alpukat', slug: 'alpukat', emoji: '🥑', price: 25000, unit: 'kg',
    tags: ['jus', 'smoothie', 'diet', 'sehat'], olahan: ['Jus alpukat coklat', 'Smoothie alpukat'], manfaat: 'Lemak sehat omega-3.' },
]

function localFallback(text) {
  const lower = text.toLowerCase()
  if (/^(halo|hi|hai|hey|selamat)/i.test(lower)) {
    return { text: 'Halo! 😊 Mau cari buah apa hari ini? Saya bisa bantu rekomendasi!', products: [] }
  }
  const matched = fruitKB.filter(f =>
    lower.includes(f.name.split(' ')[0].toLowerCase()) ||
    f.tags.some(t => lower.includes(t))
  ).slice(0, 4)
  if (matched.length) {
    return {
      text: 'Ini beberapa rekomendasi buah untuk Anda:<br><br>' +
        matched.map(f => `${f.emoji} <strong>${f.name}</strong> — ${f.manfaat}`).join('<br>'),
      products: matched.map(f => ({ name: f.name, slug: f.slug, price: f.price, unit: f.unit, image: null })),
    }
  }
  return {
    text: 'Maaf, koneksi ke AI sedang bermasalah. Coba tanya seperti:<br><br>• <em>"Buah apa yang enak dijus?"</em><br>• <em>"Rekomendasi buah untuk rujak"</em><br>• <em>"Buah murah untuk diet"</em>',
    products: [],
  }
}

// ============ SEND MESSAGE ============
async function sendMessage(text) {
  const trimmed = (typeof text === 'string' ? text : '').trim()
  if (!trimmed || typing.value) return

  messages.value.push({ from: 'user', text: trimmed })
  input.value = ''
  typing.value = true
  scrollBottom()

  try {
    const history = messages.value
      .filter(m => m.from === 'user' || m.from === 'bot')
      .slice(-8)
      .map(m => ({ from: m.from, text: m.text.replace(/<[^>]*>/g, '') }))

    const res = await axios.post('/api/chatbot', {
      message: trimmed,
      history,
    }, { timeout: 20000 })

    typing.value = false
    messages.value.push({
      from: 'bot',
      text: res.data.reply,
      products: res.data.products || [],
    })
  } catch {
    typing.value = false
    const fallback = localFallback(trimmed)
    messages.value.push({ from: 'bot', ...fallback })
  }

  scrollBottom()
}

function scrollBottom() {
  nextTick(() => {
    if (messagesRef.value) {
      messagesRef.value.scrollTop = messagesRef.value.scrollHeight
    }
  })
}

function goToProduk(slug) {
  router.push(`/produk/${slug}`)
  open.value = false
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

watch(open, (val) => {
  if (val) scrollBottom()
})
</script>

<style scoped>
/* === Chat panel transition === */
.chat-enter-active { animation: chat-in 0.25s ease-out; }
.chat-leave-active { animation: chat-in 0.2s ease-in reverse; }
@keyframes chat-in {
  from { opacity: 0; transform: translateY(16px) scale(0.95); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* === Logo: melayang + goyang halus, terus-menerus === */
.chatbot-logo {
  animation: logo-float 3.2s ease-in-out infinite;
  filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.18));
}
@keyframes logo-float {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  25%      { transform: translateY(-7px) rotate(-6deg); }
  50%      { transform: translateY(0) rotate(0deg); }
  75%      { transform: translateY(-4px) rotate(6deg); }
}

/* === Tooltip float === */
.chatbot-tooltip {
  position: absolute;
  right: 78px;
  top: 50%;
  transform: translateY(-50%);
  background: #FF5A36;
  color: white;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 20px;
  white-space: nowrap;
  box-shadow: 0 2px 12px rgba(255, 90, 54, 0.35);
  animation: tooltip-nudge 3s ease-in-out infinite;
  pointer-events: none;
}
.chatbot-tooltip::after {
  content: '';
  position: absolute;
  right: -6px;
  top: 50%;
  transform: translateY(-50%);
  border: 6px solid transparent;
  border-left-color: #FF5A36;
  border-right: none;
}
@keyframes tooltip-nudge {
  0%, 100% { opacity: 1; transform: translateY(-50%) translateX(0); }
  50% { opacity: 0.85; transform: translateY(-50%) translateX(-4px); }
}

/* === Typing dots === */
.typing-dots span {
  animation: blink 1.4s infinite both;
  font-size: 1.5rem;
  line-height: 1;
}
.typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes blink {
  0%, 80%, 100% { opacity: 0.2; }
  40% { opacity: 1; }
}
</style>
