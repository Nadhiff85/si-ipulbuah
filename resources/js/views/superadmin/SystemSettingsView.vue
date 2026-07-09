<template>
  <div class="max-w-2xl">
    <h2 class="font-bold text-ink mb-5">Pengaturan Sistem</h2>

    <form @submit.prevent="save" class="bg-white rounded-xl2 border border-ink/5 p-6 space-y-4">
      <div>
        <label class="label">Batas Maksimal Hari Kesegaran Produk (Default)</label>
        <input v-model.number="form.max_freshness_days_default" type="number" class="input" />
        <p class="text-xs text-ink/40 mt-1">Produk yang melewati batas ini akan otomatis dinonaktifkan agar tidak bisa dipesan.</p>
      </div>

      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" v-model="form.delivery_globally_enabled" /> Layanan Delivery Aktif Secara Global
      </label>

      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" v-model="form.maintenance_mode" /> Mode Maintenance (situs tidak bisa diakses publik)
      </label>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label">Session Timeout (menit)</label>
          <input v-model.number="form.session_timeout_minutes" type="number" class="input" />
        </div>
        <div>
          <label class="label">Max. Percobaan Login Gagal</label>
          <input v-model.number="form.max_login_attempts" type="number" class="input" />
        </div>
      </div>

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
  max_freshness_days_default: 7, delivery_globally_enabled: true, maintenance_mode: false,
  session_timeout_minutes: 60, max_login_attempts: 5, rate_limit_per_minute: 5,
})

async function fetchSettings() {
  const { data } = await api.get('/superadmin/system-settings')
  form.value = { ...form.value, ...data.settings }
}

async function save() {
  saving.value = true
  try {
    await api.put('/superadmin/system-settings', form.value)
    alert('Pengaturan sistem berhasil disimpan!')
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
