<template>
  <div class="max-w-2xl">
    <h2 class="font-bold text-ink mb-2">Pengaturan Notifikasi</h2>
    <p class="text-ink/60 text-sm mb-5">Konfigurasi API WhatsApp (WaBlas) untuk notifikasi otomatis ke pelanggan</p>

    <form @submit.prevent="save" class="bg-white rounded-xl2 border border-ink/5 p-6 space-y-4">
      <div>
        <label class="label">WaBlas API Key</label>
        <input v-model="form.whatsapp_api_key" type="password" placeholder="Masukkan API Key WaBlas" class="input" />
        <p class="text-xs text-ink/40 mt-1">Dapatkan API Key dari dashboard WaBlas Anda.</p>
      </div>

      <div class="bg-primary/5 rounded-xl2 p-4 text-xs text-ink/60">
        <p class="font-semibold mb-1 flex items-center gap-1.5"><EnvelopeIcon class="w-4 h-4" stroke-width="1.75" /> Konfigurasi Email (SMTP)</p>
        <p>Pengaturan SMTP (host, port, username, password) dikonfigurasi lewat file <code class="bg-white px-1 rounded">.env</code> di server untuk keamanan kredensial, bukan lewat panel ini.</p>
      </div>

      <div class="bg-white border border-ink/10 rounded-xl2 p-4">
        <p class="font-semibold text-sm mb-2">Template Notifikasi Otomatis (7 jenis)</p>
        <ul class="text-xs text-ink/60 space-y-1">
          <li>1. Pesanan Dibuat</li>
          <li>2. Pembayaran Dikonfirmasi</li>
          <li>3. Pesanan Diproses</li>
          <li>4. Siap Kirim</li>
          <li>5. Pesanan Dikirim/Siap Ambil</li>
          <li>6. Pesanan Selesai</li>
          <li>7. Undangan Memberi Ulasan</li>
        </ul>
        <p class="text-xs text-ink/40 mt-2">Template pesan sudah terintegrasi otomatis di sistem sesuai status pesanan.</p>
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
import { EnvelopeIcon } from '@heroicons/vue/24/outline'

const saving = ref(false)
const form = ref({ whatsapp_api_key: '' })

async function fetchSettings() {
  const { data } = await api.get('/admin/store-settings')
  form.value.whatsapp_api_key = data.settings.whatsapp_api_key || ''
}

async function save() {
  saving.value = true
  try {
    await api.put('/admin/store-settings', form.value)
    alert('Pengaturan notifikasi berhasil disimpan!')
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
