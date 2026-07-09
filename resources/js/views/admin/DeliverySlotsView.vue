<template>
  <div>
    <h2 class="font-bold text-ink mb-2">Jadwal & Kuota Pengiriman</h2>
    <p class="text-ink/60 text-sm mb-5">Atur kuota maksimal pesanan per slot waktu agar operasional tidak overload</p>

    <div class="grid sm:grid-cols-3 gap-4">
      <div v-for="slot in slots" :key="slot.id" class="bg-white rounded-xl2 border border-ink/5 p-5">
        <p class="font-semibold text-ink mb-1">{{ slot.name }}</p>
        <p class="text-xs text-ink/50 mb-3">{{ slot.start_time }} - {{ slot.end_time }}</p>

        <label class="text-xs font-medium text-ink/60 block mb-1">Kuota Maks. per Hari</label>
        <input v-model.number="slot.quota_per_day" type="number" class="input mb-3" />

        <label class="flex items-center gap-2 text-sm mb-3">
          <input type="checkbox" v-model="slot.is_active" /> Slot Aktif
        </label>

        <button @click="save(slot)" :disabled="savingId === slot.id" class="w-full bg-primary text-white text-sm font-medium py-2 rounded-full">
          {{ savingId === slot.id ? 'Menyimpan...' : 'Simpan' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const slots = ref([])
const savingId = ref(null)

async function fetchSlots() {
  const { data } = await api.get('/admin/delivery-slots')
  slots.value = data.slots
}

async function save(slot) {
  savingId.value = slot.id
  try {
    await api.put(`/admin/delivery-slots/${slot.id}`, slot)
    alert(`Slot ${slot.name} berhasil diperbarui!`)
  } finally {
    savingId.value = null
  }
}

onMounted(fetchSlots)
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm; }
</style>
