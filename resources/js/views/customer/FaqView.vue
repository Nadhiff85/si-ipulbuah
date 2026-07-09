<template>
  <div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-ink mb-6">FAQ & Bantuan</h1>

    <p v-if="loading" class="text-ink/50 text-sm">Memuat...</p>

    <div v-else class="space-y-3">
      <details v-for="faq in faqs" :key="faq.id" class="bg-white rounded-xl2 border border-ink/5 p-4 group">
        <summary class="font-medium text-ink cursor-pointer flex justify-between items-center">
          {{ faq.question }}
          <span class="text-ink/40 group-open:rotate-180 transition">⌄</span>
        </summary>
        <p class="text-ink/60 text-sm mt-3">{{ faq.answer }}</p>
      </details>
    </div>

    <div class="mt-8 bg-primary/5 rounded-xl2 p-5 text-center">
      <p class="text-sm text-ink/70 mb-2">Masih ada pertanyaan lain?</p>
      <a :href="storeInfo.whatsappUrl" target="_blank" class="text-success font-medium hover:underline">💬 Chat via WhatsApp</a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { useStoreInfoStore } from '../../stores/store'

const storeInfo = useStoreInfoStore()
const faqs = ref([])
const loading = ref(true)

onMounted(async () => {
  storeInfo.fetchStoreInfo()
  try {
    const { data } = await api.get('/faqs')
    faqs.value = data.faqs
  } finally {
    loading.value = false
  }
})
</script>
