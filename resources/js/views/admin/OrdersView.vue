<template>
  <div>
    <div class="flex justify-between items-center mb-5">
      <h2 class="font-bold text-ink">Pesanan Masuk</h2>
      <select v-model="statusFilter" @change="fetchOrders" class="input w-48 cursor-pointer">
        <option value="">Semua Status</option>
        <option value="menunggu_bayar">Menunggu Bayar</option>
        <option value="dikonfirmasi">Dikonfirmasi</option>
        <option value="diproses">Diproses</option>
        <option value="dikirim_siap_ambil">Dikirim/Siap Ambil</option>
        <option value="selesai">Selesai</option>
      </select>
    </div>

    <div class="bg-white rounded-xl2 border border-ink/5 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/70">
          <tr>
            <th class="p-3 text-left">No. Invoice</th><th class="p-3 text-left">Pelanggan</th>
            <th class="p-3 text-left">Total</th><th class="p-3 text-left">Tipe</th>
            <th class="p-3 text-left">Status</th><th class="p-3 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in orders" :key="o.id" class="border-t border-ink/5 hover:bg-primary/[0.03] transition-colors">
            <td class="p-3 font-medium">{{ o.order_number }}</td>
            <td class="p-3">{{ o.user?.name }}</td>
            <td class="p-3 tabular-nums">Rp {{ formatPrice(o.total) }}</td>
            <td class="p-3">{{ o.fulfillment_type === 'delivery' ? 'Delivery' : 'Pickup' }}</td>
            <td class="p-3">
              <select :value="o.status" @change="updateStatus(o, $event.target.value)" class="input-sm cursor-pointer">
                <option value="menunggu_bayar">Menunggu Bayar</option>
                <option value="dikonfirmasi">Dikonfirmasi</option>
                <option value="diproses">Diproses</option>
                <option value="dikirim_siap_ambil">Dikirim/Siap Ambil</option>
                <option value="selesai">Selesai</option>
                <option value="dibatalkan">Dibatalkan</option>
              </select>
            </td>
            <td class="p-3">
              <ActionButton variant="primary" @click="window.open(`/api/admin/orders/${o.id}/invoice`, '_blank')">
                <span class="inline-flex items-center gap-1"><PrinterIcon class="w-3.5 h-3.5" stroke-width="1.75" /> Cetak Invoice</span>
              </ActionButton>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import ActionButton from '../../components/shared/ActionButton.vue'
import { PrinterIcon } from '@heroicons/vue/24/outline'

const orders = ref([])
const statusFilter = ref('')

async function fetchOrders() {
  const { data } = await api.get('/admin/orders', { params: { status: statusFilter.value } })
  orders.value = data.data
}

async function updateStatus(order, status) {
  await api.patch(`/admin/orders/${order.id}/status`, { status })
  fetchOrders()
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

onMounted(fetchOrders)
</script>

<style scoped>
.input { @apply border border-ink/15 rounded-lg px-3 py-2 text-sm; }
.input-sm { @apply border border-ink/15 rounded-lg px-2 py-1 text-xs; }
</style>
