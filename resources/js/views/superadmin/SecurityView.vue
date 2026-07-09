<template>
  <div class="max-w-2xl">
    <h2 class="font-bold text-ink mb-5">Pengaturan Keamanan Sistem</h2>

    <form @submit.prevent="save" class="bg-white rounded-xl2 border border-ink/5 p-6 space-y-4">
      <div>
        <label class="label">Durasi Session Timeout (menit)</label>
        <input v-model.number="form.session_timeout_minutes" type="number" class="input" />
      </div>
      <div>
        <label class="label">Maksimal Percobaan Login Gagal</label>
        <input v-model.number="form.max_login_attempts" type="number" class="input" />
      </div>
      <div>
        <label class="label">Rate Limiting (percobaan per menit)</label>
        <input v-model.number="form.rate_limit_per_minute" type="number" class="input" />
      </div>
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" v-model="form.maintenance_mode" /> Aktifkan Mode Maintenance
      </label>

      <button type="submit" :disabled="saving" class="bg-primary text-white px-5 py-2.5 rounded-full text-sm font-medium">
        {{ saving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const saving = ref(false)
const form = ref({
  session_timeout_minutes: 60, max_login_attempts: 5, rate_limit_per_minute: 5, maintenance_mode: false,
  max_freshness_days_default: 7, delivery_globally_enabled: true,
})

async function fetchSettings() {
  const { data } = await api.get('/superadmin/system-settings')
  form.value = { ...form.value, ...data.settings }
}

async function save() {
  saving.value = true
  try {
    await api.put('/superadmin/system-settings', form.value)
    alert('Pengaturan keamanan berhasil disimpan!')
  } finally {
    saving.value = false
  }
}

onMounted(fetchSettings)
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm; }
.label { @apply text-xs font-medium text-ink/60 block mb-1; }
</style>
