<template>
  <div>
    <h2 class="font-bold text-ink mb-2">Kirim Notifikasi</h2>
    <p class="text-ink/60 text-sm mb-5">Kirim pesan WhatsApp & Email ke satu pelanggan atau broadcast ke semua</p>

    <div class="grid md:grid-cols-3 gap-6">
      <form @submit.prevent="send" class="md:col-span-1 bg-white rounded-xl2 border border-ink/5 p-5 space-y-3 h-fit">
        <div class="flex gap-2 mb-2">
          <button type="button" @click="form.target = 'single'" class="tab-btn flex-1" :class="form.target === 'single' && 'tab-active'">Satu Pelanggan</button>
          <button type="button" @click="form.target = 'broadcast'" class="tab-btn flex-1" :class="form.target === 'broadcast' && 'tab-active'">Broadcast Semua</button>
        </div>

        <div v-if="form.target === 'single'">
          <label class="label">Cari Pelanggan</label>
          <input v-model="search" @keyup.enter="searchCustomers" placeholder="Ketik nama..." class="input mb-2" />
          <select v-model="form.user_id" class="input">
            <option value="" disabled>Pilih pelanggan</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <p v-else class="text-xs text-ink/60 bg-warning/10 rounded-lg p-2 flex items-center gap-1.5">
          <ExclamationTriangleIcon class="w-4 h-4 text-warning shrink-0" stroke-width="1.75" />
          Pesan akan dikirim ke <strong>SEMUA</strong> pelanggan terdaftar via WhatsApp & Email.
        </p>

        <div>
          <label class="label">Pesan</label>
          <textarea v-model="form.message" rows="4" required placeholder="Tulis pesan Anda..." class="input"></textarea>
        </div>

        <button type="submit" :disabled="sending" class="w-full bg-accent text-white py-2.5 rounded-full text-sm font-semibold">
          {{ sending ? 'Mengirim...' : 'Kirim Notifikasi' }}
        </button>
      </form>

      <div class="md:col-span-2 bg-white rounded-xl2 border border-ink/5 overflow-hidden h-fit">
        <p class="font-semibold text-sm p-4 border-b border-ink/5">Riwayat Notifikasi Terkirim</p>
        <table class="w-full text-sm">
          <thead class="bg-primary/5 text-ink/70">
            <tr><th class="p-3 text-left">Waktu</th><th class="p-3 text-left">Penerima</th><th class="p-3 text-left">Channel</th><th class="p-3 text-left">Status</th></tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id" class="border-t border-ink/5">
              <td class="p-3 text-xs">{{ log.created_at }}</td>
              <td class="p-3">{{ log.user?.name || '-' }}</td>
              <td class="p-3 uppercase text-xs">{{ log.channel }}</td>
              <td class="p-3">
                <span :class="log.status === 'sent' ? 'text-success' : 'text-danger'">● {{ log.status }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const form = ref({ target: 'single', user_id: '', message: '' })
const search = ref('')
const customers = ref([])
const logs = ref([])
const sending = ref(false)

async function searchCustomers() {
  const { data } = await api.get('/admin/customers', { params: { search: search.value } })
  customers.value = data.data
}

async function fetchLogs() {
  const { data } = await api.get('/admin/notifications')
  logs.value = data.data
}

async function send() {
  sending.value = true
  try {
    const { data } = await api.post('/admin/notifications/send', form.value)
    alert(data.message)
    form.value.message = ''
    fetchLogs()
  } finally {
    sending.value = false
  }
}

onMounted(() => {
  searchCustomers()
  fetchLogs()
})
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm; }
.label { @apply text-xs font-medium text-ink/60 block mb-1; }
.tab-btn { @apply px-3 py-1.5 rounded-full text-xs font-medium text-ink/60 border border-ink/10; }
.tab-active { @apply bg-primary text-white border-primary; }
</style>
