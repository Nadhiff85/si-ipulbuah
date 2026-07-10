<template>
  <div class="max-w-3xl">
    <h2 class="font-bold text-ink mb-5">Pengaturan Toko</h2>

    <div class="flex gap-2 mb-5">
      <button @click="tab = 'umum'" class="tab-btn" :class="tab === 'umum' && 'tab-active'">Info Umum</button>
      <button @click="tab = 'pembayaran'" class="tab-btn" :class="tab === 'pembayaran' && 'tab-active'">Rekening & QRIS</button>
      <button @click="tab = 'tentang'" class="tab-btn" :class="tab === 'tentang' && 'tab-active'">Tentang Kami</button>
    </div>

    <!-- Tab Info Umum -->
    <form v-if="tab === 'umum'" @submit.prevent="save" class="bg-white rounded-xl2 border border-ink/5 p-6 space-y-4">
      <div>
        <label class="label">Nama Toko</label>
        <input v-model="form.store_name" class="input" />
      </div>
      <div>
        <label class="label">Tagline</label>
        <input v-model="form.tagline" class="input" />
      </div>
      <div>
        <label class="label">Alamat</label>
        <textarea v-model="form.address" rows="2" class="input"></textarea>
      </div>
      <div>
        <label class="label">No. WhatsApp Bisnis</label>
        <input v-model="form.whatsapp_number" class="input" />
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label">Jam Buka</label>
          <input v-model="form.operating_hours.buka" type="time" class="input" />
        </div>
        <div>
          <label class="label">Jam Tutup</label>
          <input v-model="form.operating_hours.tutup" type="time" class="input" />
        </div>
      </div>
      <div>
        <label class="label">Minimum Order Delivery (Rp)</label>
        <input v-model.number="form.min_order_delivery" type="number" class="input" />
      </div>

      <button type="submit" :disabled="saving" class="bg-primary text-white px-5 py-2.5 rounded-full text-sm font-medium">
        {{ saving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
      </button>
    </form>

    <!-- Tab Rekening & QRIS -->
    <div v-if="tab === 'pembayaran'" class="space-y-5">
      <div class="bg-white rounded-xl2 border border-ink/5 p-6">
        <div class="flex justify-between items-center mb-3">
          <p class="font-semibold text-sm">Rekening Bank (Transfer Manual)</p>
          <button type="button" @click="addBank" class="text-primary text-xs font-medium">+ Tambah Rekening</button>
        </div>
        <div v-for="(bank, i) in form.bank_accounts" :key="i" class="grid grid-cols-3 gap-2 mb-2">
          <input v-model="bank.bank" placeholder="Nama Bank (BSI/BNI)" class="input" />
          <input v-model="bank.no_rek" placeholder="No. Rekening" class="input" />
          <div class="flex gap-1">
            <input v-model="bank.atas_nama" placeholder="Atas Nama" class="input flex-1" />
            <button type="button" @click="form.bank_accounts.splice(i, 1)" class="text-danger px-2">
              <XMarkIcon class="w-4 h-4" stroke-width="2" />
            </button>
          </div>
        </div>
        <button @click="saveBankAccounts" :disabled="saving" class="bg-primary text-white px-5 py-2 rounded-full text-sm font-medium mt-2">
          Simpan Rekening
        </button>
      </div>

      <div class="bg-white rounded-xl2 border border-ink/5 p-6">
        <p class="font-semibold text-sm mb-3">Gambar QRIS Statis Toko (fitur B.21)</p>
        <div class="flex items-center gap-4">
          <div class="w-32 h-32 rounded-lg border-2 border-dashed border-ink/20 flex items-center justify-center overflow-hidden">
            <img v-if="currentQris" :src="currentQris" class="w-full h-full object-cover" />
            <span v-else><DevicePhoneMobileIcon class="w-8 h-8 text-ink/25" stroke-width="1.5" /></span>
          </div>
          <div>
            <input type="file" accept="image/*" @change="onQrisSelected" class="text-sm mb-2" />
            <button @click="uploadQris" :disabled="!qrisFile || uploadingQris" class="bg-accent text-white px-4 py-2 rounded-full text-sm font-medium block">
              {{ uploadingQris ? 'Mengunggah...' : 'Unggah QRIS' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab Tentang Kami -->
    <form v-if="tab === 'tentang'" @submit.prevent="save" class="bg-white rounded-xl2 border border-ink/5 p-6 space-y-4">
      <p class="font-semibold text-sm mb-1">Konten Halaman "Tentang Kami" (fitur B.11)</p>
      <RichTextEditor v-model="form.about_content" />
      <button type="submit" :disabled="saving" class="bg-primary text-white px-5 py-2.5 rounded-full text-sm font-medium">
        {{ saving ? 'Menyimpan...' : 'Simpan Konten' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import RichTextEditor from '../../components/admin/RichTextEditor.vue'
import { XMarkIcon, DevicePhoneMobileIcon } from '@heroicons/vue/24/outline'

const tab = ref('umum')
const saving = ref(false)
const uploadingQris = ref(false)
const qrisFile = ref(null)
const currentQris = ref(null)

const form = ref({
  store_name: 'IPUL BUAH', tagline: '', about_content: '', address: '', whatsapp_number: '',
  operating_hours: { buka: '08:00', tutup: '17:00' }, min_order_delivery: 50000,
  bank_accounts: [],
})

async function fetchSettings() {
  const { data } = await api.get('/admin/store-settings')
  form.value = {
    ...form.value, ...data.settings,
    operating_hours: data.settings.operating_hours || form.value.operating_hours,
    bank_accounts: data.settings.bank_accounts || [],
  }
  currentQris.value = data.settings.qris_image
}

function addBank() {
  form.value.bank_accounts.push({ bank: '', no_rek: '', atas_nama: '' })
}

async function save() {
  saving.value = true
  try {
    await api.put('/admin/store-settings', form.value)
    alert('Pengaturan berhasil disimpan!')
  } finally {
    saving.value = false
  }
}

async function saveBankAccounts() {
  saving.value = true
  try {
    await api.put('/admin/store-settings', form.value)
    alert('Rekening bank berhasil disimpan!')
  } finally {
    saving.value = false
  }
}

function onQrisSelected(e) {
  qrisFile.value = e.target.files[0]
}

async function uploadQris() {
  if (!qrisFile.value) return
  uploadingQris.value = true
  try {
    const formData = new FormData()
    formData.append('qris_image', qrisFile.value)
    const { data } = await api.post('/admin/store-settings/qris', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    currentQris.value = data.settings.qris_image
    qrisFile.value = null
    alert('Gambar QRIS berhasil diperbarui!')
  } finally {
    uploadingQris.value = false
  }
}

onMounted(fetchSettings)
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm; }
.label { @apply text-xs font-medium text-ink/60 block mb-1; }
.tab-btn { @apply px-4 py-2 rounded-full text-sm font-medium text-ink/60 border border-ink/10; }
.tab-active { @apply bg-primary text-white border-primary; }
</style>
