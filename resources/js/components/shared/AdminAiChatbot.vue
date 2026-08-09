<template>
  <!-- Tombol Asisten AI Admin -->
  <div class="fixed bottom-6 right-6 z-40">
    <span v-if="!open" class="admin-ai-tooltip">Asisten AI Admin</span>
    <button
      @click="open = !open"
      class="relative w-14 h-14 rounded-full shadow-xl flex items-center justify-center transition-transform duration-300 cursor-pointer hover:scale-110 active:scale-95"
      :class="open ? 'bg-red-500' : 'bg-gradient-to-br from-primary to-primary-dark'"
      title="Asisten AI (Beta)"
    >
      <XMarkIcon v-if="open" class="w-6 h-6 text-white" stroke-width="2" />
      <template v-else>
        <SparklesIcon class="w-7 h-7 text-white admin-ai-icon" stroke-width="2" />
        <span class="absolute -top-1 -right-1 bg-accent text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">AI</span>
      </template>
    </button>
  </div>

  <!-- Panel Chat -->
  <Transition name="chat">
    <div
      v-if="open"
      class="fixed bottom-24 right-6 z-40 w-[380px] sm:w-[420px] max-h-[75vh] flex flex-col bg-white rounded-2xl shadow-2xl border border-ink/10 overflow-hidden"
    >
      <!-- Header -->
      <div class="bg-gradient-to-r from-primary to-primary-dark px-4 py-3 flex items-center gap-3 shrink-0">
        <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center">
          <SparklesIcon class="w-5 h-5 text-white" stroke-width="2" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-white font-bold text-sm flex items-center gap-2">
            Asisten AI Admin
            <span class="bg-accent/90 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">BETA</span>
          </p>
          <p class="text-white/70 text-[11px]">Read-only · Data langsung dari database</p>
        </div>
      </div>

      <!-- Messages -->
      <div ref="messagesRef" class="flex-1 overflow-y-auto p-4 space-y-3 bg-surface-soft/30 min-h-[240px] max-h-[50vh]">
        <div
          v-for="(msg, i) in messages" :key="i"
          :class="msg.from === 'bot' ? 'flex justify-start' : 'flex justify-end'"
        >
          <div
            :class="msg.from === 'bot'
              ? 'bg-white text-ink rounded-2xl rounded-tl-sm max-w-[88%] border border-ink/5'
              : 'bg-primary text-white rounded-2xl rounded-tr-sm max-w-[88%]'"
            class="px-4 py-2.5 text-sm leading-relaxed shadow-sm"
          >
            <div class="whitespace-pre-wrap">{{ msg.text }}</div>
          </div>
        </div>
        <div v-if="typing" class="flex justify-start">
          <div class="bg-white border border-ink/5 text-ink rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
            <span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>
          </div>
        </div>
      </div>

      <!-- Quick Topics -->
      <div v-if="messages.length <= 1" class="px-4 pb-2 flex flex-wrap gap-1.5 shrink-0">
        <button
          v-for="q in quickTopics" :key="q"
          @click="sendMessage(q)"
          class="text-[11px] font-medium bg-primary/10 text-primary px-3 py-1.5 rounded-full hover:bg-primary/20 transition cursor-pointer"
        >
          {{ q }}
        </button>
      </div>

      <!-- Disclaimer -->
      <div class="px-4 py-1.5 bg-yellow-50 border-t border-yellow-200 shrink-0">
        <p class="text-[10px] text-yellow-800 leading-tight">
          ⚠️ AI dapat keliru. Selalu verifikasi angka di menu Laporan sebelum ambil keputusan.
        </p>
      </div>

      <!-- Input -->
      <div class="border-t border-ink/10 p-3 flex gap-2 shrink-0 bg-white">
        <input
          v-model="input"
          @keydown.enter="sendMessage(input)"
          type="text"
          placeholder="Tanya omzet, stok, saran restock..."
          class="flex-1 bg-surface-soft rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
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
import api from '../../services/api'
import {
  SparklesIcon,
  XMarkIcon,
  PaperAirplaneIcon,
} from '@heroicons/vue/24/outline'

const open = ref(false)
const input = ref('')
const typing = ref(false)
const messagesRef = ref(null)

const messages = ref([
  {
    from: 'bot',
    text: 'Halo! Saya Asisten AI Admin IPUL BUAH. Saya bisa bantu:\n\n• Ringkasan omzet & pesanan\n• Cek stok kritis atau habis\n• Cek kesegaran buah — mana yang masih segar, hampir layu, atau sudah harus ditarik\n• Saran buah apa yang perlu di-restock\n\nSilakan tanya apa saja.',
  },
])

const quickTopics = [
  'Ringkasan omzet hari ini',
  'Buah apa yang perlu di-restock?',
  'Buah apa yang masih segar?',
  'Buah apa yang harus ditarik dari etalase?',
  'Buah apa yang perlu didiskon?',
  'Stok apa yang menipis?',
]

async function sendMessage(text) {
  const trimmed = (typeof text === 'string' ? text : '').trim()
  if (!trimmed || typing.value) return

  messages.value.push({ from: 'user', text: trimmed })
  input.value = ''
  typing.value = true
  scrollBottom()

  try {
    const history = messages.value
      .slice(-8)
      .map(m => ({ from: m.from, text: m.text }))

    const res = await api.post('/admin/ai-assistant', {
      message: trimmed,
      history,
    }, { timeout: 25000 })

    typing.value = false
    messages.value.push({ from: 'bot', text: res.data.reply })
  } catch (err) {
    typing.value = false
    const msg = err.response?.status === 429
      ? 'Terlalu banyak pertanyaan dalam waktu singkat. Tunggu sebentar ya.'
      : 'Maaf, koneksi ke AI bermasalah. Coba lagi sebentar.'
    messages.value.push({ from: 'bot', text: msg })
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

watch(open, (val) => {
  if (val) scrollBottom()
})
</script>

<style scoped>
.chat-enter-active { animation: chat-in 0.25s ease-out; }
.chat-leave-active { animation: chat-in 0.2s ease-in reverse; }
@keyframes chat-in {
  from { opacity: 0; transform: translateY(16px) scale(0.95); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Ikon Sparkles: berkilau lembut terus-menerus */
.admin-ai-icon {
  animation: sparkle-pulse 2.5s ease-in-out infinite;
}
@keyframes sparkle-pulse {
  0%, 100% { transform: scale(1) rotate(0deg); opacity: 1; }
  50%      { transform: scale(1.15) rotate(15deg); opacity: 0.85; }
}

.admin-ai-tooltip {
  position: absolute;
  right: 68px;
  top: 50%;
  transform: translateY(-50%);
  background: #1a1a1a;
  color: white;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 8px;
  white-space: nowrap;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
  opacity: 0;
  animation: tooltip-appear 3s ease-in-out 1s infinite;
  pointer-events: none;
}
.admin-ai-tooltip::after {
  content: '';
  position: absolute;
  right: -5px;
  top: 50%;
  transform: translateY(-50%);
  border: 5px solid transparent;
  border-left-color: #1a1a1a;
  border-right: none;
}
@keyframes tooltip-appear {
  0%, 100% { opacity: 0; transform: translateY(-50%) translateX(-6px); }
  15%, 85% { opacity: 1; transform: translateY(-50%) translateX(0); }
}

.typing-dots span {
  animation: blink 1.4s infinite both;
  font-size: 1.5rem;
  line-height: 1;
}
.typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes blink {
  0%, 80%, 100% { opacity: 0.2; }
  40%           { opacity: 1; }
}
</style>
