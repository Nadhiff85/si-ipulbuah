<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h2 class="font-bold text-ink">Promo & Diskon</h2>
      <button @click="openCreate" class="bg-primary text-white px-4 py-2.5 rounded-full text-sm font-medium">+ Tambah Promo</button>
    </div>

    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/70">
          <tr><th class="p-3 text-left">Nama</th><th class="p-3 text-left">Diskon</th><th class="p-3 text-left">Periode</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">Aksi</th></tr>
        </thead>
        <tbody>
          <tr v-for="p in promotions" :key="p.id" class="border-t border-ink/5">
            <td class="p-3 font-medium">{{ p.name }}</td>
            <td class="p-3">{{ p.discount_type === 'percent' ? p.discount_value + '%' : 'Rp ' + formatPrice(p.discount_value) }}</td>
            <td class="p-3">{{ p.start_date }} s/d {{ p.end_date }}</td>
            <td class="p-3"><span :class="p.is_active ? 'text-success' : 'text-danger'">● {{ p.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td class="p-3">
              <div class="flex gap-2">
                <ActionButton variant="primary" @click="openEdit(p)">Edit</ActionButton>
                <ActionButton variant="danger" @click="remove(p)">Hapus</ActionButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-ink/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl2 w-full max-w-md p-6">
        <h2 class="font-bold text-lg mb-4">{{ form.id ? 'Edit' : 'Tambah' }} Promo</h2>
        <form @submit.prevent="save" class="space-y-3">
          <input v-model="form.name" placeholder="Nama Promo" required class="input" />
          <div class="grid grid-cols-2 gap-2">
            <select v-model="form.discount_type" class="input">
              <option value="percent">Persen (%)</option>
              <option value="nominal">Nominal (Rp)</option>
            </select>
            <input v-model.number="form.discount_value" type="number" placeholder="Nilai" required class="input" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <select v-model="form.scope" class="input">
              <option value="product">Produk</option>
              <option value="category">Kategori</option>
            </select>
            <input v-model.number="form.target_id" type="number" placeholder="ID Produk/Kategori" required class="input" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <input v-model="form.start_date" type="date" required class="input" />
            <input v-model="form.end_date" type="date" required class="input" />
          </div>
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

const promotions = ref([])
const showModal = ref(false)
const emptyForm = () => ({ id: null, name: '', discount_type: 'percent', discount_value: 0, scope: 'product', target_id: null, start_date: '', end_date: '', is_active: true })
const form = ref(emptyForm())

async function fetchPromotions() {
  const { data } = await api.get('/admin/promotions')
  promotions.value = data.promotions
}

function openCreate() { form.value = emptyForm(); showModal.value = true }
function openEdit(p) { form.value = { ...p }; showModal.value = true }

async function save() {
  if (form.value.id) await api.put(`/admin/promotions/${form.value.id}`, form.value)
  else await api.post('/admin/promotions', form.value)
  showModal.value = false
  fetchPromotions()
}

const deletingId = ref(null)

async function remove(p) {
  if (deletingId.value) return
  if (!confirm(`Hapus promo "${p.name}"?`)) return

  deletingId.value = p.id
  try {
    await api.delete(`/admin/promotions/${p.id}`)
    fetchPromotions()
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus promo.')
  } finally {
    deletingId.value = null
  }
}

function formatPrice(v) { return new Intl.NumberFormat('id-ID').format(v) }

onMounted(fetchPromotions)
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm; }
</style>