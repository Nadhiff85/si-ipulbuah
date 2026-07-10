<template>
  <div>
    <h2 class="font-semibold text-ink text-[15px] mb-2">Audit Trail Pelanggan</h2>
    <p class="text-ink/55 text-sm mb-5">Log aktivitas lengkap untuk keamanan, deteksi anomali & kepatuhan UU PDP</p>

    <input v-model="search" @keyup.enter="fetchCustomers" placeholder="Cari nama pelanggan..." class="input w-72 mb-4" />

    <div class="grid md:grid-cols-3 gap-5">
      <div class="bg-white rounded-xl2 border border-ink/5 p-4">
        <p class="font-semibold text-ink text-sm mb-3">Daftar Pelanggan</p>
        <button
          v-for="c in customers"
          :key="c.id"
          @click="selectCustomer(c)"
          class="w-full text-left px-3 py-2 rounded-lg text-sm mb-1 transition"
          :class="selected?.id === c.id ? 'bg-primary/10 text-primary font-medium' : 'text-ink/70 hover:bg-ink/5'"
        >
          {{ c.name }}
        </button>
      </div>

      <div v-if="trail" class="md:col-span-2 space-y-4">
        <div class="bg-white rounded-xl2 border border-ink/5 p-4">
          <p class="font-semibold text-ink text-sm mb-2 flex items-center gap-2">
            <LockClosedIcon class="w-4 h-4 text-ink/40" stroke-width="1.75" /> Login Terakhir
          </p>
          <div v-for="l in trail.login_logs?.slice(0, 5)" :key="l.id" class="text-xs text-ink/60 py-1 border-b border-ink/5">
            {{ l.login_at || 'Gagal login' }} — IP: {{ l.ip_address }}
          </div>
        </div>

        <div class="bg-white rounded-xl2 border border-ink/5 p-4">
          <p class="font-semibold text-ink text-sm mb-2 flex items-center gap-2">
            <EyeIcon class="w-4 h-4 text-ink/40" stroke-width="1.75" /> Riwayat Browsing
          </p>
          <div v-for="v in trail.product_views?.slice(0, 8)" :key="v.id" class="text-xs text-ink/60 py-1 border-b border-ink/5">
            {{ v.product?.name }} — {{ v.viewed_at }}
          </div>
        </div>

        <div class="bg-white rounded-xl2 border border-ink/5 p-4">
          <p class="font-semibold text-ink text-sm mb-2 flex items-center gap-2">
            <ShoppingCartIcon class="w-4 h-4 text-ink/40" stroke-width="1.75" /> Pesanan & Pembayaran
          </p>
          <div v-for="o in trail.orders?.slice(0, 5)" :key="o.id" class="text-xs text-ink/60 py-1 border-b border-ink/5">
            {{ o.order_number }} — Rp {{ formatPrice(o.total) }} ({{ o.payment?.method }}, {{ o.status }})
          </div>
        </div>

        <div class="bg-white rounded-xl2 border border-ink/5 p-4">
          <p class="font-semibold text-ink text-sm mb-2 flex items-center gap-2">
            <DocumentTextIcon class="w-4 h-4 text-ink/40" stroke-width="1.75" /> Aktivitas Lainnya
          </p>
          <div v-for="a in trail.activities?.slice(0, 8)" :key="a.id" class="text-xs text-ink/60 py-1 border-b border-ink/5">
            {{ a.action }} — {{ a.created_at }} (IP: {{ a.ip_address }})
          </div>
        </div>
      </div>

      <div v-else class="md:col-span-2 flex items-center justify-center text-ink/40 text-sm">
        Pilih pelanggan untuk melihat riwayat aktivitas.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { LockClosedIcon, EyeIcon, ShoppingCartIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'

const search = ref('')
const customers = ref([])
const selected = ref(null)
const trail = ref(null)

async function fetchCustomers() {
  const { data } = await api.get('/superadmin/audit-trail/customers', { params: { search: search.value } })
  customers.value = data.customers
}

async function selectCustomer(c) {
  selected.value = c
  const { data } = await api.get(`/superadmin/audit-trail/customers/${c.id}`)
  trail.value = data
}

function formatPrice(v) { return new Intl.NumberFormat('id-ID').format(v) }

onMounted(fetchCustomers)
</script>

<style scoped>
.input { @apply border border-ink/15 rounded-lg px-3 py-2 text-sm; }
</style>
