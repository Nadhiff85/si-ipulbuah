<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h2 class="font-bold text-ink">Backup Database</h2>
      <button @click="runBackup" :disabled="running" class="bg-primary text-white px-4 py-2.5 rounded-full text-sm font-medium">
        {{ running ? 'Memproses...' : '+ Backup Manual Sekarang' }}
      </button>
    </div>

    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/70">
          <tr><th class="p-3 text-left">Tanggal</th><th class="p-3 text-left">Tipe</th><th class="p-3 text-left">Ukuran</th><th class="p-3 text-left">Dibuat oleh</th><th class="p-3 text-left">Aksi</th></tr>
        </thead>
        <tbody>
          <tr v-for="b in backups" :key="b.id" class="border-t border-ink/5">
            <td class="p-3">{{ b.created_at }}</td>
            <td class="p-3 capitalize">{{ b.type }}</td>
            <td class="p-3">{{ b.file_size ? Math.round(b.file_size / 1024) + ' KB' : '-' }}</td>
            <td class="p-3">{{ b.created_by?.name || 'Sistem (Terjadwal)' }}</td>
            <td class="p-3 space-x-2">
              <a :href="`/api/superadmin/backups/${b.id}/download`" class="text-primary hover:underline">Unduh</a>
              <button @click="restore(b)" class="text-danger hover:underline">Restore</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p class="text-xs text-ink/40 mt-3 flex items-center gap-1.5">
      <ExclamationTriangleIcon class="w-4 h-4 text-warning shrink-0" stroke-width="1.75" />
      Restore akan menimpa seluruh data saat ini. Pastikan Anda yakin sebelum melanjutkan.
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const backups = ref([])
const running = ref(false)

async function fetchBackups() {
  const { data } = await api.get('/superadmin/backups')
  backups.value = data.backups
}

async function runBackup() {
  running.value = true
  try {
    await api.post('/superadmin/backups')
    fetchBackups()
  } finally {
    running.value = false
  }
}

async function restore(b) {
  if (!confirm('Yakin ingin restore ke titik backup ini? Seluruh data saat ini akan ditimpa.')) return
  await api.post(`/superadmin/backups/${b.id}/restore`)
  alert('Restore berhasil dijalankan.')
}

onMounted(fetchBackups)
</script>
