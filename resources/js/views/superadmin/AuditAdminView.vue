<template>
  <div>
    <h2 class="font-bold text-ink mb-2">Audit Trail Admin</h2>
    <p class="text-ink/60 text-sm mb-5">Siapa mengubah apa, kapan, dan dari IP mana — untuk audit internal</p>

    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/70">
          <tr><th class="p-3 text-left">Waktu</th><th class="p-3 text-left">Admin</th><th class="p-3 text-left">Aksi</th><th class="p-3 text-left">IP Address</th></tr>
        </thead>
        <tbody>
          <tr v-for="log in logs" :key="log.id" class="border-t border-ink/5">
            <td class="p-3 text-xs">{{ log.created_at }}</td>
            <td class="p-3 font-medium">{{ log.user?.name }}</td>
            <td class="p-3">{{ log.action }}</td>
            <td class="p-3 text-xs">{{ log.ip_address }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const logs = ref([])

async function fetchLogs() {
  const { data } = await api.get('/superadmin/audit-trail/admins')
  logs.value = data.data
}

onMounted(fetchLogs)
</script>
