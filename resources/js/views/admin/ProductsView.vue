<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <div class="relative w-72">
        <input v-model="search" @keyup.enter="fetchProducts" placeholder="Cari produk..." class="input" />
      </div>
      <button @click="openCreate" class="bg-primary text-white px-4 py-2.5 rounded-full text-sm font-medium">+ Tambah Produk</button>
    </div>

    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/70">
          <tr>
            <th class="p-3 text-left">Foto</th>
            <th class="p-3 text-left">Nama</th>
            <th class="p-3 text-left">Kategori</th>
            <th class="p-3 text-left">Harga</th>
            <th class="p-3 text-left">Stok</th>
            <th class="p-3 text-left">Label</th>
            <th class="p-3 text-left">Status</th>
            <th class="p-3 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in products" :key="p.id" class="border-t border-ink/5">
            <td class="p-3">
              <div class="w-12 h-12 rounded-lg bg-primary/5 overflow-hidden flex items-center justify-center">
                <img v-if="p.images?.[0]" :src="p.images[0].image_path" class="w-full h-full object-cover" />
                <span v-else><PhotoIcon class="w-5 h-5 text-ink/25" stroke-width="1.5" /></span>
              </div>
            </td>
            <td class="p-3 font-medium">{{ p.name }}</td>
            <td class="p-3">{{ p.category?.name }}</td>
            <td class="p-3">Rp {{ formatPrice(p.price_unit) }}/{{ p.unit }}</td>
            <td class="p-3" :class="p.stock <= p.min_stock_alert ? 'text-danger font-semibold' : ''">{{ p.stock }}</td>
            <td class="p-3">
              <span v-for="l in p.labels" :key="l" class="bg-badge/20 text-accent text-[10px] font-bold px-1.5 py-0.5 rounded mr-1">{{ l }}</span>
              <span v-if="p.is_featured" class="bg-primary/15 text-primary text-[10px] font-bold px-1.5 py-0.5 rounded">Terlaris</span>
            </td>
            <td class="p-3">
              <span :class="p.is_active ? 'text-success' : 'text-danger'">● {{ p.is_active ? 'Aktif' : 'Nonaktif' }}</span>
            </td>
            <td class="p-3 whitespace-nowrap">
              <div class="flex gap-2">
                <ActionButton variant="primary" @click="openEdit(p)">Edit</ActionButton>
                <ActionButton variant="danger" @click="remove(p)">Hapus</ActionButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ===== Modal Form ===== -->
    <div v-if="showModal" class="fixed inset-0 bg-ink/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl2 w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
        <h2 class="font-bold text-lg mb-4">{{ form.id ? 'Edit Produk' : 'Tambah Produk' }}</h2>

        <form @submit.prevent="saveProduct" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="label">Nama Produk</label>
              <input v-model="form.name" required class="input" />
            </div>
            <div>
              <label class="label">Kategori</label>
              <select v-model="form.category_id" required class="input">
                <option value="" disabled>Pilih kategori</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="label">Asal</label>
              <select v-model="form.origin_type" class="input">
                <option value="lokal">Lokal</option>
                <option value="impor">Impor</option>
              </select>
            </div>
            <div>
              <label class="label">Satuan</label>
              <select v-model="form.unit" class="input">
                <option value="kg">kg</option>
                <option value="pcs">pcs</option>
              </select>
            </div>
            <div>
              <label class="label">Asal Daerah</label>
              <input v-model="form.origin_region" class="input" placeholder="mis. Palu" />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="label">Harga Satuan</label>
              <input v-model.number="form.price_unit" type="number" required class="input" />
            </div>
            <div>
              <label class="label">Harga Grosir</label>
              <input v-model.number="form.price_wholesale" type="number" class="input" />
            </div>
            <div>
              <label class="label">Min. Qty Grosir</label>
              <input v-model.number="form.wholesale_min_qty" type="number" class="input" />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="label">Stok</label>
              <input v-model.number="form.stock" type="number" required class="input" />
            </div>
            <div>
              <label class="label">Batas Stok Menipis</label>
              <input v-model.number="form.min_stock_alert" type="number" class="input" />
            </div>
            <div>
              <label class="label">Estimasi Kesegaran (hari)</label>
              <input v-model.number="form.freshness_days" type="number" class="input" />
            </div>
          </div>

          <div v-if="form.id">
            <label class="label">Alasan Perubahan Stok (jika stok diubah)</label>
            <input v-model="form.stock_change_reason" placeholder="mis. Restock dari petani, Koreksi data, dll" class="input" />
          </div>

          <div>
            <label class="label">Tips Penyimpanan</label>
            <textarea v-model="form.storage_tips" rows="2" class="input"></textarea>
          </div>

          <div>
            <label class="label">Label</label>
            <div class="flex gap-3 flex-wrap">
              <label v-for="l in ['segar', 'best_seller', 'musiman', 'promo']" :key="l" class="flex items-center gap-1.5 text-sm">
                <input type="checkbox" :value="l" v-model="form.labels" /> {{ l }}
              </label>
            </div>
          </div>

          <div>
            <label class="label">Foto Produk (bisa lebih dari satu)</label>
            <input type="file" multiple accept="image/*" @change="onFileChange" class="text-sm" />
          </div>

          <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" v-model="form.is_active" /> Produk Aktif
          </label>

          <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" v-model="form.is_featured" /> Tampilkan di "Produk Terlaris" (Beranda Publik)
          </label>

          <div class="flex justify-end gap-2 pt-3">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-ink/60">Batal</button>
            <button type="submit" :disabled="saving" class="bg-primary text-white px-5 py-2 rounded-full text-sm font-medium">
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import ActionButton from '../../components/shared/ActionButton.vue'
import { PhotoIcon } from '@heroicons/vue/24/outline'

const products = ref([])
const categories = ref([])
const search = ref('')
const showModal = ref(false)
const saving = ref(false)
const files = ref([])

const emptyForm = () => ({
  id: null, category_id: '', name: '', origin_type: 'lokal', unit: 'kg', origin_region: '',
  price_unit: 0, price_wholesale: null, wholesale_min_qty: null,
  stock: 0, min_stock_alert: 5, freshness_days: null, storage_tips: '',
  labels: [], is_active: true, is_featured: false, stock_change_reason: '',
})
const form = ref(emptyForm())

async function fetchProducts() {
  const { data } = await api.get('/admin/products', { params: { search: search.value } })
  products.value = data.data
}

async function fetchCategories() {
  const { data } = await api.get('/admin/categories')
  categories.value = data.categories
}

function openCreate() {
  form.value = emptyForm()
  files.value = []
  showModal.value = true
}

function openEdit(p) {
  form.value = { ...emptyForm(), ...p }
  files.value = []
  showModal.value = true
}

function onFileChange(e) {
  files.value = Array.from(e.target.files)
}

async function saveProduct() {
  saving.value = true
  try {
    const payload = new FormData()
    Object.entries(form.value).forEach(([key, val]) => {
      if (key === 'labels') {
        val.forEach((l) => payload.append('labels[]', l))
      } else if (val !== null && val !== undefined) {
        payload.append(key, typeof val === 'boolean' ? (val ? 1 : 0) : val)
      }
    })
    files.value.forEach((f) => payload.append('images[]', f))

    if (form.value.id) {
      payload.append('_method', 'PUT')
      await api.post(`/admin/products/${form.value.id}`, payload, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      await api.post('/admin/products', payload, { headers: { 'Content-Type': 'multipart/form-data' } })
    }

    showModal.value = false
    fetchProducts()
  } finally {
    saving.value = false
  }
}

async function remove(p) {
  if (!confirm(`Hapus produk "${p.name}"?`)) return
  await api.delete(`/admin/products/${p.id}`)
  fetchProducts()
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

onMounted(() => {
  fetchProducts()
  fetchCategories()
})
</script>

<style scoped>
.input {
  @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40;
}
.label {
  @apply text-xs font-medium text-ink/60 block mb-1;
}
</style>
