<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h2 class="font-semibold text-ink text-[15px]">Manajemen Kategori</h2>
      <button @click="openCreate" class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-full text-sm font-medium transition">+ Tambah Kategori</button>
    </div>

    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/60">
          <tr><th class="p-3 text-left font-medium">Foto</th><th class="p-3 text-left font-medium">Nama</th><th class="p-3 text-left font-medium">Tipe</th><th class="p-3 text-left font-medium">Status</th><th class="p-3 text-left font-medium">Aksi</th></tr>
        </thead>
        <tbody>
          <tr v-for="c in categories" :key="c.id" class="border-t border-ink/5">
            <td class="p-3">
              <div class="w-10 h-10 rounded-lg bg-primary/5 overflow-hidden flex items-center justify-center">
                <img v-if="c.image" :src="c.image" class="w-full h-full object-cover" />
                <TagIcon v-else class="w-5 h-5 text-ink/25" stroke-width="1.5" />
              </div>
            </td>
            <td class="p-3 font-medium text-ink">{{ c.name }}</td>
            <td class="p-3 capitalize text-ink/70">{{ c.type }}</td>
            <td class="p-3">
              <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full" :class="c.is_active ? 'bg-success/15 text-success' : 'bg-danger/10 text-danger'">
                <span class="w-1.5 h-1.5 rounded-full" :class="c.is_active ? 'bg-success' : 'bg-danger'"></span>
                {{ c.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="p-3">
              <div class="flex gap-2">
                <ActionButton variant="primary" @click="openEdit(c)">Edit</ActionButton>
                <ActionButton variant="danger" @click="remove(c)">Hapus</ActionButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-ink/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl2 w-full max-w-md p-6">
        <h2 class="font-semibold text-ink mb-4">{{ form.id ? 'Edit' : 'Tambah' }} Kategori</h2>
        <form @submit.prevent="save" class="space-y-3">
          <input v-model="form.name" placeholder="Nama kategori" required class="input" />
          <select v-model="form.type" class="input">
            <option value="lokal">Lokal</option>
            <option value="impor">Impor</option>
            <option value="musiman">Musiman</option>
          </select>

          <div>
            <label class="text-xs font-medium text-ink/60 block mb-1">Foto/Ikon Kategori</label>
            <div class="flex items-center gap-3">
              <div class="w-14 h-14 rounded-lg bg-primary/5 overflow-hidden flex items-center justify-center shrink-0">
                <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover" />
                <TagIcon v-else class="w-6 h-6 text-ink/25" stroke-width="1.5" />
              </div>
              <input type="file" accept="image/*" @change="onImageSelected" class="text-sm" />
            </div>
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
import ActionButton from '../../components/shared/ActionButton.vue'
import { TagIcon } from '@heroicons/vue/24/outline'

const categories = ref([])
const showModal = ref(false)
const form = ref({ id: null, name: '', type: 'lokal', is_active: true })
const imageFile = ref(null)
const previewImage = ref(null)

async function fetchCategories() {
  const { data } = await api.get('/admin/categories')
  categories.value = data.categories
}

function openCreate() {
  form.value = { id: null, name: '', type: 'lokal', is_active: true }
  imageFile.value = null
  previewImage.value = null
  showModal.value = true
}

function openEdit(c) {
  form.value = { ...c }
  imageFile.value = null
  previewImage.value = c.image || null
  showModal.value = true
}

function onImageSelected(e) {
  const file = e.target.files[0]
  if (!file) return
  imageFile.value = file
  previewImage.value = URL.createObjectURL(file)
}

async function save() {
  const payload = new FormData()
  payload.append('name', form.value.name)
  payload.append('type', form.value.type)
  payload.append('is_active', form.value.is_active ? 1 : 0)
  if (imageFile.value) payload.append('image_file', imageFile.value)

  if (form.value.id) {
    payload.append('_method', 'PUT')
    await api.post(`/admin/categories/${form.value.id}`, payload, { headers: { 'Content-Type': 'multipart/form-data' } })
  } else {
    await api.post('/admin/categories', payload, { headers: { 'Content-Type': 'multipart/form-data' } })
  }

  showModal.value = false
  fetchCategories()
}

async function remove(c) {
  if (!confirm(`Hapus kategori "${c.name}"?`)) return
  await api.delete(`/admin/categories/${c.id}`)
  fetchCategories()
}

onMounted(fetchCategories)
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm; }
</style>
