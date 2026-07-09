<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h2 class="font-bold text-ink">Manajemen Kategori</h2>
      <button @click="openCreate" class="bg-primary text-white px-4 py-2.5 rounded-full text-sm font-medium">+ Tambah Kategori</button>
    </div>

    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/70">
          <tr><th class="p-3 text-left">Nama</th><th class="p-3 text-left">Tipe</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">Aksi</th></tr>
        </thead>
        <tbody>
          <tr v-for="c in categories" :key="c.id" class="border-t border-ink/5">
            <td class="p-3 font-medium">{{ c.name }}</td>
            <td class="p-3 capitalize">{{ c.type }}</td>
            <td class="p-3">
              <span :class="c.is_active ? 'text-success' : 'text-danger'">● {{ c.is_active ? 'Aktif' : 'Nonaktif' }}</span>
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
        <h2 class="font-bold text-lg mb-4">{{ form.id ? 'Edit' : 'Tambah' }} Kategori</h2>
        <form @submit.prevent="save" class="space-y-3">
          <input v-model="form.name" placeholder="Nama kategori" required class="input" />
          <select v-model="form.type" class="input">
            <option value="lokal">Lokal</option>
            <option value="impor">Impor</option>
            <option value="musiman">Musiman</option>
          </select>
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

const categories = ref([])
const showModal = ref(false)
const form = ref({ id: null, name: '', type: 'lokal', is_active: true })

async function fetchCategories() {
  const { data } = await api.get('/admin/categories')
  categories.value = data.categories
}

function openCreate() {
  form.value = { id: null, name: '', type: 'lokal', is_active: true }
  showModal.value = true
}

function openEdit(c) {
  form.value = { ...c }
  showModal.value = true
}

async function save() {
  if (form.value.id) {
    await api.put(`/admin/categories/${form.value.id}`, form.value)
  } else {
    await api.post('/admin/categories', form.value)
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
