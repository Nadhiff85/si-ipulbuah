<template>
  <div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-ink mb-6">Akun Saya</h1>

    <div class="flex gap-2 mb-6">
      <button @click="tab = 'profil'" class="tab-btn" :class="tab === 'profil' && 'tab-active'">Profil</button>
      <button @click="tab = 'alamat'" class="tab-btn" :class="tab === 'alamat' && 'tab-active'">Alamat Pengiriman</button>
    </div>

    <!-- Tab Profil -->
    <div v-if="tab === 'profil'" class="space-y-5">
      <form @submit.prevent="saveProfile" class="bg-white rounded-xl2 border border-ink/5 p-6 space-y-4">
        <p class="font-semibold text-ink">Data Diri</p>
        <div>
          <label class="text-sm font-medium block mb-1">Nama</label>
          <input v-model="profileForm.name" class="input" />
        </div>
        <div>
          <label class="text-sm font-medium block mb-1">Email</label>
          <input :value="auth.user.email" class="input" disabled />
          <p class="text-xs text-ink/40 mt-1">Email tidak dapat diubah.</p>
        </div>
        <div>
          <label class="text-sm font-medium block mb-1">No. WhatsApp</label>
          <input v-model="profileForm.phone" class="input" />
        </div>
        <button type="submit" :disabled="savingProfile" class="bg-primary text-white px-5 py-2.5 rounded-full text-sm font-medium">
          {{ savingProfile ? 'Menyimpan...' : 'Simpan Perubahan' }}
        </button>
      </form>

      <form @submit.prevent="changePassword" class="bg-white rounded-xl2 border border-ink/5 p-6 space-y-4">
        <p class="font-semibold text-ink">Ubah Password</p>
        <div>
          <label class="text-sm font-medium block mb-1">Password Saat Ini</label>
          <input v-model="passwordForm.current_password" type="password" required class="input" />
        </div>
        <div>
          <label class="text-sm font-medium block mb-1">Password Baru</label>
          <input v-model="passwordForm.password" type="password" required minlength="8" class="input" />
        </div>
        <div>
          <label class="text-sm font-medium block mb-1">Konfirmasi Password Baru</label>
          <input v-model="passwordForm.password_confirmation" type="password" required class="input" />
        </div>
        <p v-if="passwordError" class="text-danger text-sm">{{ passwordError }}</p>
        <button type="submit" :disabled="savingPassword" class="bg-accent text-white px-5 py-2.5 rounded-full text-sm font-medium">
          {{ savingPassword ? 'Menyimpan...' : 'Ubah Password' }}
        </button>
      </form>

      <button @click="logout" class="text-danger text-sm font-medium hover:underline">Keluar dari Akun</button>
    </div>

    <!-- Tab Alamat -->
    <div v-else>
      <button @click="showForm = !showForm" class="mb-4 bg-primary text-white px-4 py-2 rounded-full text-sm font-medium">
        {{ showForm ? 'Batal' : '+ Tambah Alamat' }}
      </button>

      <form v-if="showForm" @submit.prevent="saveAddress" class="bg-white rounded-xl2 border border-ink/5 p-5 space-y-3 mb-5">
        <input v-model="form.label" placeholder="Label (Rumah/Kantor)" class="input" />
        <input v-model="form.recipient_name" placeholder="Nama Penerima" required class="input" />
        <input v-model="form.phone" placeholder="No. WhatsApp" required class="input" />
        <textarea v-model="form.full_address" placeholder="Alamat Lengkap" required rows="2" class="input"></textarea>
        <select v-model="form.delivery_region_id" required class="input">
          <option value="" disabled selected>Pilih Kecamatan</option>
          <option v-for="r in regions" :key="r.id" :value="r.id">{{ r.regency }} - {{ r.district }}</option>
        </select>
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="form.is_default" /> Jadikan alamat utama
        </label>
        <button type="submit" class="bg-accent text-white px-5 py-2 rounded-full text-sm font-medium">Simpan Alamat</button>
      </form>

      <div class="space-y-3">
        <div v-for="addr in addresses" :key="addr.id" class="bg-white rounded-xl2 border border-ink/5 p-4 flex justify-between">
          <div class="text-sm">
            <p class="font-medium">{{ addr.label }} — {{ addr.recipient_name }} <span v-if="addr.is_default" class="text-xs text-primary">(Utama)</span></p>
            <p class="text-ink/60">{{ addr.full_address }}</p>
          </div>
          <button @click="removeAddress(addr)" class="text-danger text-xs h-fit hover:underline">Hapus</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const router = useRouter()
const auth = useAuthStore()
const tab = ref('profil')
const showForm = ref(false)
const addresses = ref([])
const regions = ref([])

const profileForm = ref({ name: auth.user?.name || '', phone: auth.user?.phone || '' })
const savingProfile = ref(false)

const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' })
const savingPassword = ref(false)
const passwordError = ref('')

async function saveProfile() {
  savingProfile.value = true
  try {
    const { data } = await api.put('/profile', profileForm.value)
    auth.user = { ...auth.user, ...data.user }
    localStorage.setItem('ipulbuah_user', JSON.stringify(auth.user))
    alert('Profil berhasil diperbarui!')
  } finally {
    savingProfile.value = false
  }
}

async function changePassword() {
  passwordError.value = ''
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    passwordError.value = 'Konfirmasi password baru tidak cocok.'
    return
  }
  savingPassword.value = true
  try {
    await api.put('/profile/password', passwordForm.value)
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    alert('Password berhasil diubah!')
  } catch (e) {
    passwordError.value = e.response?.data?.message || 'Gagal mengubah password.'
  } finally {
    savingPassword.value = false
  }
}

const form = ref({
  label: '', recipient_name: '', phone: '', full_address: '', delivery_region_id: '', is_default: false,
})

async function fetchAddresses() {
  const { data } = await api.get('/addresses')
  addresses.value = data.addresses
}

async function fetchRegions() {
  const { data } = await api.get('/delivery-regions')
  regions.value = data.regions
}

async function saveAddress() {
  await api.post('/addresses', form.value)
  showForm.value = false
  form.value = { label: '', recipient_name: '', phone: '', full_address: '', delivery_region_id: '', is_default: false }
  fetchAddresses()
}

async function removeAddress(addr) {
  await api.delete(`/addresses/${addr.id}`)
  fetchAddresses()
}

async function logout() {
  await auth.logout()
  router.push('/')
}

onMounted(() => {
  fetchAddresses()
  fetchRegions()
})
</script>

<style scoped>
.input {
  @apply w-full border border-ink/15 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40;
}
.tab-btn {
  @apply px-4 py-2 rounded-full text-sm font-medium text-ink/60 border border-ink/10;
}
.tab-active {
  @apply bg-primary text-white border-primary;
}
</style>
