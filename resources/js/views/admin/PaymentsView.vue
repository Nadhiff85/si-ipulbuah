<template>
  <div>
    <h2 class="font-semibold text-ink text-[15px] mb-5">Verifikasi Pembayaran</h2>

    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/60">
          <tr>
            <th class="p-3 text-left font-medium">Invoice</th><th class="p-3 text-left font-medium">Metode</th>
            <th class="p-3 text-left font-medium">Jumlah</th><th class="p-3 text-left font-medium">Bukti</th>
            <th class="p-3 text-left font-medium">Status</th><th class="p-3 text-left font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in payments" :key="p.id" class="border-t border-ink/5">
            <td class="p-3 font-medium text-ink">{{ p.order?.order_number }}</td>
            <td class="p-3 uppercase text-ink/70 text-xs">{{ p.method }}</td>
            <td class="p-3 text-ink/70">Rp {{ formatPrice(p.amount) }}</td>
            <td class="p-3">
              <ActionButton v-if="p.proof_image" variant="primary" @click="window.open(p.proof_image, '_blank')">Lihat Bukti</ActionButton>
              <span v-else class="text-ink/40 text-xs">-</span>
            </td>
            <td class="p-3">
              <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="statusBadge(p.status)">{{ p.status }}</span>
            </td>
            <td class="p-3" v-if="p.status === 'pending'">
              <div class="flex gap-2">
                <ActionButton variant="success" @click="confirm(p, 'confirmed')">Konfirmasi</ActionButton>
                <ActionButton variant="danger" @click="confirm(p, 'rejected')">Tolak</ActionButton>
              </div>
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

const payments = ref([])

async function fetchPayments() {
  const { data } = await api.get('/admin/payments')
  payments.value = data.data
}

async function confirm(payment, status) {
  await api.patch(`/admin/payments/${payment.id}/status`, { status })
  fetchPayments()
}

function statusBadge(s) {
  return { pending: 'bg-warning/15 text-warning', confirmed: 'bg-success/15 text-success', rejected: 'bg-danger/10 text-danger' }[s] || 'bg-ink/5 text-ink/60'
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}

onMounted(fetchPayments)
</script>
