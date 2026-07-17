<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h2 class="font-semibold text-ink text-[15px]">Manajemen Akun Admin Toko</h2>
      <button @click="showForm = true" class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-full text-sm font-medium transition">
        + Tambah Admin
      </button>
    </div>

    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/60">
          <tr><th class="p-3 text-left font-medium">Nama</th><th class="p-3 text-left font-medium">Email</th><th class="p-3 text-left font-medium">Status</th><th class="p-3 text-left font-medium">Aksi</th></tr>
        </thead>
        <tbody>
          <tr v-for="a in admins" :key="a.id" class="border-t border-ink/5">
            <td class="p-3 font-medium text-ink">{{ a.name }}</td>
            <td class="p-3 text-ink/70">{{ a.email }}</td>
            <td class="p-3">
              <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full" :class="a.is_active ? 'bg-success/15 text-success' : 'bg-danger/10 text-danger'">
                <span class="w-1.5 h-1.5 rounded-full" :class="a.is_active ? 'bg-success' : 'bg-danger'"></span>
                {{ a.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="p-3">
              <div class="flex gap-2">
                <ActionButton :variant="a.is_active ? 'danger' : 'success'" @click="toggle(a)">
                  {{ a.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </ActionButton>
                <ActionButton variant="accent" @click="resetPassword(a)">Reset Password</ActionButton>
                <ActionButton variant="danger" @click="remove(a)">Hapus</ActionButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-ink/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl2 w-full max-w-md p-6">
        <h2 class="font-semibold text-ink mb-4">Tambah Admin Toko</h2>
        <form @submit.prevent="save" class="space-y-3">
          <input v-model="form.name" placeholder="Nama Lengkap" required class="input" />
          <input v-model="form.email" type="email" placeholder="Email" required class="input" />
          <input v-model="form.phone" placeholder="No. WhatsApp" required class="input" />
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showForm = false" class="px-4 py-2 text-sm text-ink/60">Batal</button>
            <button type="submit" class="bg-primary text-white px-5 py-2 rounded-full text-sm font-medium">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Password Sementara -->
    <div v-if="tempPassword" class="fixed inset-0 bg-ink/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl2 w-full max-w-sm p-6 text-center">
        <p class="font-semibold text-ink mb-2">Password Sementara</p>
        <p class="bg-primary/5 rounded-lg py-2 font-mono text-lg mb-3">{{ tempPassword }}</p>
        <p class="text-xs text-ink/50 mb-4">Sampaikan password ini ke admin secara aman. Wajib diganti saat login pertama.</p>
        <button @click="tempPassword = null" class="bg-primary text-white px-5 py-2 rounded-full text-sm">Tutup</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import ActionButton from '../../components/shared/ActionButton.vue'

const admins = ref([])
const showForm = ref(false)
const tempPassword = ref(null)
const form = ref({ name: '', email: '', phone: '' })

async function fetchAdmins() {
  const { data } = await api.get('/superadmin/admins')
  admins.value = data.admins
}

async function save() {
  const { data } = await api.post('/superadmin/admins', form.value)
  tempPassword.value = data.temporary_password
  showForm.value = false
  form.value = { name: '', email: '', phone: '' }
  fetchAdmins()
}

async function toggle(a) {
  await api.patch(`/superadmin/admins/${a.id}/toggle-active`)
  fetchAdmins()
}

async function resetPassword(a) {
  if (!confirm(`Reset password untuk ${a.name}?`)) return
  const { data } = await api.post(`/superadmin/admins/${a.id}/reset-password`)
  tempPassword.value = data.temporary_password
}

const deletingId = ref(null)

async function remove(a) {
  if (deletingId.value) return
  if (!confirm(`Hapus akun admin "${a.name}"?`)) return

  deletingId.value = a.id
  try {
    await api.delete(`/superadmin/admins/${a.id}`)
    fetchAdmins()
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus akun admin.')
  } finally {
    deletingId.value = null
  }
}

onMounted(fetchAdmins)
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm; }
</style>