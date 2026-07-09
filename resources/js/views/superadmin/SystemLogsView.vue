<template>
  <div>
    <h2 class="font-bold text-ink mb-2">Log Aktivitas Sistem</h2>
    <p class="text-ink/60 text-sm mb-5">Cron job, notifikasi gagal terkirim, dan error teknis untuk troubleshooting</p>

    <div class="flex gap-2 mb-5">
      <button @click="tab = 'notifikasi'" class="tab-btn" :class="tab === 'notifikasi' && 'tab-active'">Notifikasi Gagal</button>
      <button @click="tab = 'error'" class="tab-btn" :class="tab === 'error' && 'tab-active'">Error Log (laravel.log)</button>
    </div>

    <div v-if="tab === 'notifikasi'" class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/70">
          <tr><th class="p-3 text-left">Waktu</th><th class="p-3 text-left">Penerima</th><th class="p-3 text-left">Channel</th><th class="p-3 text-left">Template</th><th class="p-3 text-left">Status</th></tr>
        </thead>
        <tbody>
          <tr v-for="log in notifLogs" :key="log.id" class="border-t border-ink/5">
            <td class="p-3 text-xs">{{ log.created_at }}</td>
            <td class="p-3">{{ log.user?.name || '-' }}</td>
            <td class="p-3 uppercase text-xs">{{ log.channel }}</td>
            <td class="p-3 text-xs">{{ log.template_type }}</td>
            <td class="p-3"><span :class="log.status === 'sent' ? 'text-success' : 'text-danger'">● {{ log.status }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="bg-ink text-white rounded-xl2 p-4 font-mono text-xs overflow-x-auto max-h-[500px] overflow-y-auto">
      <p v-if="errorLines.length === 0" class="text-white/40">Tidak ada error log tercatat.</p>
      <p v-for="(line, i) in errorLines" :key="i" class="whitespace-pre-wrap mb-1">{{ line }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const tab = ref('notifikasi')
const notifLogs = ref([])
const errorLines = ref([])

async function fetchNotifLogs() {
  const { data } = await api.get('/superadmin/system-logs/notifications', { params: { status: 'failed' } })
  notifLogs.value = data.data
}

async function fetchErrorLogs() {
  const { data } = await api.get('/superadmin/system-logs/errors')
  errorLines.value = data.lines
}

onMounted(() => {
  fetchNotifLogs()
  fetchErrorLogs()
})
</script>

<style scoped>
.tab-btn { @apply px-4 py-2 rounded-full text-sm font-medium text-ink/60 border border-ink/10; }
.tab-active { @apply bg-primary text-white border-primary; }
</style>
