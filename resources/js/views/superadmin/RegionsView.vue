<template>
  <div>
    <h2 class="font-bold text-ink mb-2">Wilayah Pengiriman Global</h2>
    <p class="text-ink/60 text-sm mb-5">Aktifkan/nonaktifkan seluruh layanan delivery secara global (mis. bencana alam)</p>

    <div class="bg-white rounded-xl2 border border-ink/5 p-5 mb-5 flex items-center justify-between">
      <div>
        <p class="font-semibold text-sm">Status Layanan Delivery Global</p>
        <p class="text-xs text-ink/50">Jika dimatikan, SEMUA pengiriman ke rumah akan dinonaktifkan sementara di seluruh wilayah.</p>
      </div>
      <button
        @click="toggleGlobal"
        class="px-4 py-2 rounded-full text-sm font-semibold"
        :class="settings.delivery_globally_enabled ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger'"
      >
        ● {{ settings.delivery_globally_enabled ? 'AKTIF' : 'NONAKTIF' }}
      </button>
    </div>

    <p class="font-semibold text-ink mb-3">Detail per Kecamatan</p>
    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/70">
          <tr><th class="p-3 text-left">Kabupaten/Kota</th><th class="p-3 text-left">Kecamatan</th><th class="p-3 text-left">Ongkir</th><th class="p-3 text-left">Status</th></tr>
        </thead>
        <tbody>
          <tr v-for="r in regions" :key="r.id" class="border-t border-ink/5">
            <td class="p-3">{{ r.regency }}</td>
            <td class="p-3 font-medium">{{ r.district }}</td>
            <td class="p-3">Rp {{ formatPrice(r.shipping_cost) }}</td>
            <td class="p-3"><span :class="r.is_active ? 'text-success' : 'text-danger'">● {{ r.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>
    <p class="text-xs text-ink/40 mt-2">*Untuk mengatur per-kecamatan secara detail, gunakan menu Admin Toko → Area Pengiriman.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const settings = ref({ delivery_globally_enabled: true })
const regions = ref([])

async function fetchData() {
  const [settingsRes, regionsRes] = await Promise.all([
    api.get('/superadmin/system-settings'),
    api.get('/admin/delivery-areas'),
  ])
  settings.value = settingsRes.data.settings
  regions.value = regionsRes.data.regions
}

async function toggleGlobal() {
  const { data } = await api.patch('/superadmin/system-settings/toggle-global-delivery')
  settings.value = data.settings
}

function formatPrice(v) { return new Intl.NumberFormat('id-ID').format(v) }

onMounted(fetchData)
</script>
