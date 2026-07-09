<template>
  <div>
    <h2 class="font-semibold text-ink text-[15px] mb-5">Monitoring Kinerja Sistem</h2>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
      <StatCard label="Waktu Respon Server" :value="`${data.waktu_respon_server_ms} ms`" :icon="BoltIcon" color="primary" />
      <StatCard label="Total Pengguna" :value="data.total_pengguna" :icon="UsersIcon" color="primary" />
      <StatCard label="Aktif 24 Jam Terakhir" :value="data.pengguna_aktif_24jam" :icon="SignalIcon" color="accent" />
      <StatCard label="Total Admin Toko" :value="data.total_admin" :icon="UserCircleIcon" color="badge" />
      <StatCard label="Notifikasi Terkirim" :value="data.notifikasi_terkirim_hari_ini" :icon="PaperAirplaneIcon" color="primary" />
      <StatCard label="Notifikasi Gagal" :value="data.notifikasi_gagal_hari_ini" :icon="ExclamationTriangleIcon" color="danger" />
      <StatCard label="Storage Terpakai" :value="`${data.storage_terpakai_mb} MB`" :icon="CircleStackIcon" color="accent" />
    </div>

    <router-link to="/superadmin/pengaturan-sistem" class="text-primary text-sm font-medium hover:underline">
      → Lihat/Ubah Pengaturan Sistem
    </router-link>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import StatCard from '../../components/admin/StatCard.vue'
import {
  BoltIcon, UsersIcon, SignalIcon, UserCircleIcon, PaperAirplaneIcon,
  ExclamationTriangleIcon, CircleStackIcon,
} from '@heroicons/vue/24/outline'

const data = ref({})

onMounted(async () => {
  const res = await api.get('/superadmin/monitoring')
  data.value = res.data
})
</script>
