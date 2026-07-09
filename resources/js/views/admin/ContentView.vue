<template>
  <div>
    <div class="flex gap-2 mb-5">
      <button @click="tab = 'banner'" class="tab-btn" :class="tab === 'banner' && 'tab-active'">Banner Beranda</button>
      <button @click="tab = 'faq'" class="tab-btn" :class="tab === 'faq' && 'tab-active'">FAQ</button>
    </div>

    <div v-if="tab === 'banner'">
      <button @click="showBannerForm = !showBannerForm" class="mb-4 bg-primary text-white px-4 py-2 rounded-full text-sm">+ Tambah Banner</button>
      <form v-if="showBannerForm" @submit.prevent="saveBanner" class="bg-white rounded-xl2 border border-ink/5 p-4 space-y-2 mb-4">
        <input v-model="bannerForm.title" placeholder="Judul Banner" class="input" />
        <input v-model="bannerForm.image" placeholder="URL Gambar" required class="input" />
        <button type="submit" class="bg-accent text-white px-4 py-2 rounded-full text-sm">Simpan</button>
      </form>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div v-for="b in banners" :key="b.id" class="bg-white rounded-xl2 border border-ink/5 p-3">
          <img :src="b.image" class="w-full h-24 object-cover rounded-lg mb-2" />
          <p class="text-sm font-medium">{{ b.title }}</p>
          <button @click="removeBanner(b)" class="text-danger text-xs">Hapus</button>
        </div>
      </div>
    </div>

    <div v-else>
      <button @click="showFaqForm = !showFaqForm" class="mb-4 bg-primary text-white px-4 py-2 rounded-full text-sm">+ Tambah FAQ</button>
      <form v-if="showFaqForm" @submit.prevent="saveFaq" class="bg-white rounded-xl2 border border-ink/5 p-4 space-y-2 mb-4">
        <input v-model="faqForm.question" placeholder="Pertanyaan" required class="input" />
        <textarea v-model="faqForm.answer" placeholder="Jawaban" required rows="2" class="input"></textarea>
        <button type="submit" class="bg-accent text-white px-4 py-2 rounded-full text-sm">Simpan</button>
      </form>
      <div class="space-y-2">
        <div v-for="f in faqs" :key="f.id" class="bg-white rounded-xl2 border border-ink/5 p-4">
          <p class="font-medium text-sm">{{ f.question }}</p>
          <p class="text-ink/60 text-sm mb-2">{{ f.answer }}</p>
          <button @click="removeFaq(f)" class="text-danger text-xs">Hapus</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const tab = ref('banner')
const banners = ref([])
const faqs = ref([])
const showBannerForm = ref(false)
const showFaqForm = ref(false)
const bannerForm = ref({ title: '', image: '' })
const faqForm = ref({ question: '', answer: '' })

async function fetchBanners() { banners.value = (await api.get('/admin/content/banners')).data.banners }
async function fetchFaqs() { faqs.value = (await api.get('/admin/content/faqs')).data.faqs }

async function saveBanner() {
  await api.post('/admin/content/banners', bannerForm.value)
  bannerForm.value = { title: '', image: '' }
  showBannerForm.value = false
  fetchBanners()
}
async function removeBanner(b) { await api.delete(`/admin/content/banners/${b.id}`); fetchBanners() }

async function saveFaq() {
  await api.post('/admin/content/faqs', faqForm.value)
  faqForm.value = { question: '', answer: '' }
  showFaqForm.value = false
  fetchFaqs()
}
async function removeFaq(f) { await api.delete(`/admin/content/faqs/${f.id}`); fetchFaqs() }

onMounted(() => { fetchBanners(); fetchFaqs() })
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm; }
.tab-btn { @apply px-4 py-2 rounded-full text-sm font-medium text-ink/60 border border-ink/10; }
.tab-active { @apply bg-primary text-white border-primary; }
</style>
