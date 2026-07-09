<template>
  <div>
    <h2 class="font-semibold text-ink text-[15px] mb-5">Manajemen Pelanggan</h2>

    <div class="bg-white rounded-xl2 border border-ink/5 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-primary/5 text-ink/60">
          <tr><th class="p-3 text-left font-medium">Nama</th><th class="p-3 text-left font-medium">Email</th><th class="p-3 text-left font-medium">Total Pesanan</th><th class="p-3 text-left font-medium">Status</th><th class="p-3 text-left font-medium">Aksi</th></tr>
        </thead>
        <tbody>
          <tr v-for="c in customers" :key="c.id" class="border-t border-ink/5">
            <td class="p-3 font-medium text-ink">{{ c.name }}</td>
            <td class="p-3 text-ink/70">{{ c.email }}</td>
            <td class="p-3 text-ink/70">{{ c.orders_count }}</td>
            <td class="p-3">
              <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full" :class="c.is_active ? 'bg-success/15 text-success' : 'bg-danger/10 text-danger'">
                <span class="w-1.5 h-1.5 rounded-full" :class="c.is_active ? 'bg-success' : 'bg-danger'"></span>
                {{ c.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="p-3">
              <ActionButton :variant="c.is_active ? 'danger' : 'success'" @click="toggle(c)">
                {{ c.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
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

const customers = ref([])

async function fetchCustomers() {
  const { data } = await api.get('/admin/customers')
  customers.value = data.data
}

async function toggle(c) {
  await api.patch(`/admin/customers/${c.id}/toggle-active`)
  fetchCustomers()
}

onMounted(fetchCustomers)
</script>
