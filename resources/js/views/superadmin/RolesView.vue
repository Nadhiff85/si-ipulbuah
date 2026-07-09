<template>
  <div>
    <h2 class="font-bold text-ink mb-5">Role & Permission</h2>

    <div class="grid md:grid-cols-3 gap-5">
      <div v-for="role in roles" :key="role.id" class="bg-white rounded-xl2 border border-ink/5 p-4">
        <p class="font-semibold text-sm mb-3 capitalize">{{ role.name }}</p>
        <div class="space-y-1.5 max-h-64 overflow-y-auto">
          <label v-for="perm in permissions" :key="perm.id" class="flex items-center gap-2 text-xs">
            <input
              type="checkbox"
              :value="perm.name"
              :checked="role.permissions.some(p => p.id === perm.id)"
              @change="togglePermission(role, perm, $event.target.checked)"
            />
            {{ perm.name }}
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const roles = ref([])
const permissions = ref([])

async function fetchData() {
  const { data } = await api.get('/superadmin/roles-permissions')
  roles.value = data.roles
  permissions.value = data.permissions
}

async function togglePermission(role, perm, checked) {
  const current = role.permissions.map((p) => p.name)
  const updated = checked ? [...current, perm.name] : current.filter((n) => n !== perm.name)

  const { data } = await api.patch(`/superadmin/roles/${role.id}/permissions`, { permissions: updated })
  const idx = roles.value.findIndex((r) => r.id === role.id)
  roles.value[idx] = data.role
}

onMounted(fetchData)
</script>
