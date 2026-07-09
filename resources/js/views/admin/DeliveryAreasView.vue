<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h2 class="font-bold text-ink">Area Pengiriman</h2>
      <button @click="openCreate" class="bg-primary text-white px-4 py-2.5 rounded-full text-sm font-medium">+ Tambah Wilayah</button>
    </div>

    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/70">
          <tr><th class="p-3 text-left">Kabupaten/Kota</th><th class="p-3 text-left">Kecamatan</th><th class="p-3 text-left">Ongkir</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">Aksi</th></tr>
        </thead>
        <tbody>
          <tr v-for="r in regions" :key="r.id" class="border-t border-ink/5">
            <td class="p-3">{{ r.regency }}</td>
            <td class="p-3 font-medium">{{ r.district }}</td>
            <td class="p-3">Rp {{ formatPrice(r.shipping_cost) }}</td>
            <td class="p-3">
              <button @click="toggle(r)" :class="r.is_active ? 'text-success' : 'text-danger'">● {{ r.is_active ? 'Aktif' : 'Nonaktif' }}</button>
            </td>
            <td class="p-3">
              <div class="flex gap-2">
                <ActionButton variant="primary" @click="openEdit(r)">Edit</ActionButton>
                <ActionButton variant="danger" @click="remove(r)">Hapus</ActionButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-ink/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl2 w-full max-w-md p-6">
        <h2 class="font-bold text-lg mb-4">{{ form.id ? 'Edit' : 'Tambah' }} Wilayah</h2>
        <form @submit.prevent="save" class="space-y-3">
          <select v-model="form.regency" required class="input">
            <option value="Kota Palu">Kota Palu</option>
            <option value="Kabupaten Sigi">Kabupaten Sigi</option>
            <option value="Kabupaten Donggala">Kabupaten Donggala</option>
          </select>
          <input v-model="form.district" placeholder="Nama Kecamatan" required class="input" />
          <input v-model.number="form.shipping_cost" type="number" placeholder="Ongkos Kirim (Rp)" required class="input" />
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

const regions = ref([])
const showModal = ref(false)
const form = ref({ id: null, regency: 'Kota Palu', district: '', shipping_cost: 0, is_active: true })

async function fetchRegions() {
  const { data } = await api.get('/admin/delivery-areas')
  regions.value = data.regions
}

function openCreate() {
  form.value = { id: null, regency: 'Kota Palu', district: '', shipping_cost: 0, is_active: true }
  showModal.value = true
}

function openEdit(r) {
  form.value = { ...r }
  showModal.value = true
}

async function save() {
  if (form.value.id) {
    await api.put(`/admin/delivery-areas/${form.value.id}`, form.value)
  } else {
    await api.post('/admin/delivery-areas', form.value)
  }
  showModal.value = false
  fetchRegions()
}

async function toggle(r) {
  await api.patch(`/admin/delivery-areas/${r.id}/toggle`)
  fetchRegions()
}

async function remove(r) {
  if (!confirm(`Hapus wilayah "${r.district}"?`)) return
  await api.delete(`/admin/delivery-areas/${r.id}`)
  fetchRegions()
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

onMounted(fetchRegions)
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm; }
</style>
