<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h2 class="font-bold text-ink">Paket / Hampers</h2>
      <button @click="openCreate" class="bg-primary text-white px-4 py-2.5 rounded-full text-sm font-medium">+ Tambah Paket</button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div v-for="h in hampers" :key="h.id" class="bg-white rounded-xl2 border border-ink/5 p-4">
        <p class="font-semibold text-sm">{{ h.name }}</p>
        <p class="text-xs text-ink/50 mb-2">{{ h.items?.map(i => i.product.name).join(', ') }}</p>
        <p class="text-accent font-bold text-sm mb-2">Rp {{ formatPrice(h.base_price) }}</p>
        <p class="text-xs mb-2" v-if="h.is_custom_allowed">✅ Izinkan kustom</p>
        <div class="flex gap-2">
          <button @click="openEdit(h)" class="text-primary text-xs hover:underline">Edit</button>
          <button @click="remove(h)" class="text-danger text-xs hover:underline">Hapus</button>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-ink/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl2 w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
        <h2 class="font-bold text-lg mb-4">{{ form.id ? 'Edit' : 'Tambah' }} Paket</h2>
        <form @submit.prevent="save" class="space-y-3">
          <input v-model="form.name" placeholder="Nama Paket" required class="input" />
          <textarea v-model="form.description" placeholder="Deskripsi" rows="2" class="input"></textarea>
          <input v-model.number="form.base_price" type="number" placeholder="Harga Dasar" required class="input" />
          <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="form.is_custom_allowed" /> Izinkan pelanggan kustom sendiri</label>
          <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="form.is_active" /> Aktif</label>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-ink/60">Batal</button>
            <button type="submit" class="bg-primary text-white px-5 py-2 rounded-full text-sm font-medium">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const hampers = ref([])
const showModal = ref(false)
const emptyForm = () => ({ id: null, name: '', description: '', base_price: 0, is_custom_allowed: false, is_active: true })
const form = ref(emptyForm())

async function fetchHampers() {
  const { data } = await api.get('/admin/hampers')
  hampers.value = data.hampers
}

function openCreate() { form.value = emptyForm(); showModal.value = true }
function openEdit(h) { form.value = { ...h }; showModal.value = true }

async function save() {
  if (form.value.id) await api.put(`/admin/hampers/${form.value.id}`, form.value)
  else await api.post('/admin/hampers', form.value)
  showModal.value = false
  fetchHampers()
}

async function remove(h) {
  if (!confirm(`Hapus paket "${h.name}"?`)) return
  await api.delete(`/admin/hampers/${h.id}`)
  fetchHampers()
}

function formatPrice(v) { return new Intl.NumberFormat('id-ID').format(v) }

onMounted(fetchHampers)
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm; }
</style>
