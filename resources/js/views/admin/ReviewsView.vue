<template>
  <div>
    <h2 class="font-bold text-ink mb-5">Ulasan & Rating Pelanggan</h2>

    <div class="space-y-3">
      <div v-for="r in reviews" :key="r.id" class="bg-white rounded-xl2 border border-ink/5 p-4">
        <div class="flex justify-between items-start mb-2">
          <div>
            <p class="font-medium text-sm">{{ r.user?.name }} — {{ r.product?.name }}</p>
            <p class="text-badge text-xs">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}</p>
          </div>
          <span class="text-xs px-2 py-1 rounded-full" :class="statusClass(r.status)">{{ r.status }}</span>
        </div>
        <p class="text-sm text-ink/70 mb-3">{{ r.comment }}</p>

        <div v-if="r.status === 'pending'" class="flex gap-2 mb-2">
          <ActionButton variant="success" @click="moderate(r, 'approved')">Setujui</ActionButton>
          <ActionButton variant="danger" @click="moderate(r, 'rejected')">Tolak</ActionButton>
        </div>

        <div class="flex gap-2 items-center">
          <input v-model="replyText[r.id]" placeholder="Balas ulasan..." class="flex-1 border border-ink/15 rounded-lg px-3 py-1.5 text-xs" />
          <button @click="reply(r)" class="text-primary text-xs font-medium">Kirim</button>
        </div>
        <p v-if="r.admin_reply" class="text-xs bg-primary/5 rounded-lg p-2 mt-2">💬 {{ r.admin_reply }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import ActionButton from '../../components/shared/ActionButton.vue'

const reviews = ref([])
const replyText = ref({})

async function fetchReviews() {
  const { data } = await api.get('/admin/reviews')
  reviews.value = data.data
}

async function moderate(review, status) {
  await api.patch(`/admin/reviews/${review.id}/moderate`, { status })
  fetchReviews()
}

async function reply(review) {
  if (!replyText.value[review.id]) return
  await api.patch(`/admin/reviews/${review.id}/reply`, { admin_reply: replyText.value[review.id] })
  fetchReviews()
}

function statusClass(s) {
  return { pending: 'bg-warning/15 text-warning', approved: 'bg-success/15 text-success', rejected: 'bg-danger/15 text-danger' }[s]
}

onMounted(fetchReviews)
</script>
