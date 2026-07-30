<template>
  <div>
    <div class="flex gap-2 mb-5">
      <button @click="tab = 'banner'" class="tab-btn cursor-pointer" :class="tab === 'banner' && 'tab-active'">Banner Beranda</button>
      <button @click="tab = 'faq'" class="tab-btn cursor-pointer" :class="tab === 'faq' && 'tab-active'">FAQ</button>
    </div>

    <!-- ===== BANNER ===== -->
    <div v-if="tab === 'banner'">
      <button @click="showBannerForm = !showBannerForm" class="mb-4 bg-primary text-white px-4 py-2 rounded-full text-sm cursor-pointer hover:bg-primary-dark transition">
        {{ showBannerForm ? 'Tutup Form' : '+ Tambah Banner' }}
      </button>

      <form v-if="showBannerForm" @submit.prevent="saveBanner" class="glass-card rounded-xl2 p-5 space-y-3 mb-5">
        <div>
          <label class="text-xs font-semibold text-ink/60 mb-1 block">Judul Banner</label>
          <input v-model="bannerForm.title" placeholder="mis. Promo Minggu Ini" class="input" />
        </div>

        <div>
          <label class="text-xs font-semibold text-ink/60 mb-1 block">Gambar Banner *</label>
          <div class="flex items-center gap-3">
            <label class="shrink-0 bg-primary/10 text-primary text-sm font-semibold px-4 py-2.5 rounded-lg cursor-pointer hover:bg-primary/20 transition">
              Pilih File
              <input type="file" accept="image/*" @change="onBannerFile" class="hidden" />
            </label>
            <span class="text-sm text-ink/50 truncate">{{ bannerFileName || 'Belum ada file dipilih' }}</span>
          </div>
          <p class="text-[11px] text-ink/40 mt-1">Otomatis dikonversi ke WebP. Maks 5MB.</p>
        </div>

        <div v-if="bannerPreview" class="rounded-lg overflow-hidden border border-ink/10">
          <img :src="bannerPreview" class="w-full h-36 object-cover" />
        </div>

        <div class="flex items-center gap-3 pt-1">
          <button type="submit" :disabled="bannerSaving || !bannerFile" class="bg-primary text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-primary-dark transition cursor-pointer disabled:opacity-50">
            {{ bannerSaving ? 'Menyimpan...' : 'Simpan Banner' }}
          </button>
          <button type="button" @click="closeBannerForm" class="text-sm text-ink/50 hover:text-ink cursor-pointer">Batal</button>
        </div>

        <p v-if="bannerError" class="text-sm text-danger bg-danger/10 px-4 py-2 rounded-lg">{{ bannerError }}</p>
      </form>

      <!-- Grid Banner -->
      <div v-if="banners.length === 0 && !showBannerForm" class="glass-card rounded-xl2 p-8 text-center text-ink/40">
        <p class="text-sm">Belum ada banner. Klik tombol di atas untuk menambahkan.</p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-for="b in banners" :key="b.id" class="glass-card rounded-xl2 overflow-hidden group">
          <div class="relative">
            <img :src="b.image" class="w-full h-32 object-cover" @error="onImgError" />
            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
              <button @click="removeBanner(b)" class="bg-danger text-white text-xs font-semibold px-3 py-1.5 rounded-full cursor-pointer hover:bg-red-700 transition">Hapus</button>
            </div>
          </div>
          <div class="p-3">
            <p class="text-sm font-semibold text-ink truncate">{{ b.title || '(Tanpa judul)' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== FAQ ===== -->
    <div v-else>
      <button @click="showFaqForm = !showFaqForm" class="mb-4 bg-primary text-white px-4 py-2 rounded-full text-sm cursor-pointer hover:bg-primary-dark transition">
        {{ showFaqForm ? 'Tutup Form' : '+ Tambah FAQ' }}
      </button>
      <form v-if="showFaqForm" @submit.prevent="saveFaq" class="glass-card rounded-xl2 p-5 space-y-3 mb-5">
        <div>
          <label class="text-xs font-semibold text-ink/60 mb-1 block">Pertanyaan *</label>
          <input v-model="faqForm.question" placeholder="mis. Bagaimana cara memesan?" required class="input" />
        </div>
        <div>
          <label class="text-xs font-semibold text-ink/60 mb-1 block">Jawaban *</label>
          <textarea v-model="faqForm.answer" placeholder="Tulis jawaban..." required rows="3" class="input"></textarea>
        </div>
        <div class="flex items-center gap-3 pt-1">
          <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-primary-dark transition cursor-pointer">Simpan FAQ</button>
          <button type="button" @click="showFaqForm = false; faqForm = { question: '', answer: '' }" class="text-sm text-ink/50 hover:text-ink cursor-pointer">Batal</button>
        </div>
      </form>
      <div v-if="faqs.length === 0 && !showFaqForm" class="glass-card rounded-xl2 p-8 text-center text-ink/40">
        <p class="text-sm">Belum ada FAQ.</p>
      </div>
      <div class="space-y-3">
        <div v-for="f in faqs" :key="f.id" class="glass-card rounded-xl2 p-4">
          <p class="font-semibold text-sm text-ink">{{ f.question }}</p>
          <p class="text-ink/60 text-sm mt-1 mb-2">{{ f.answer }}</p>
          <button @click="removeFaq(f)" class="text-danger text-xs font-semibold cursor-pointer hover:underline">Hapus</button>
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
const bannerForm = ref({ title: '' })
const bannerFile = ref(null)
const bannerFileName = ref('')
const bannerPreview = ref(null)
const bannerSaving = ref(false)
const bannerError = ref('')
const faqForm = ref({ question: '', answer: '' })

async function fetchBanners() { banners.value = (await api.get('/admin/content/banners')).data.banners }
async function fetchFaqs() { faqs.value = (await api.get('/admin/content/faqs')).data.faqs }

function onBannerFile(e) {
  const file = e.target.files[0]
  if (!file) return
  bannerFile.value = file
  bannerFileName.value = file.name
  bannerPreview.value = URL.createObjectURL(file)
}

function closeBannerForm() {
  showBannerForm.value = false
  bannerForm.value = { title: '' }
  bannerFile.value = null
  bannerFileName.value = ''
  bannerPreview.value = null
  bannerError.value = ''
}

async function saveBanner() {
  bannerSaving.value = true
  bannerError.value = ''
  try {
    const fd = new FormData()
    if (bannerForm.value.title) fd.append('title', bannerForm.value.title)
    fd.append('image_file', bannerFile.value)
    await api.post('/admin/content/banners', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    closeBannerForm()
    fetchBanners()
  } catch (err) {
    if (err.response?.status === 422) {
      const errors = err.response.data.errors || {}
      bannerError.value = Object.values(errors).flat().join(' ') || 'Data tidak valid.'
    } else {
      bannerError.value = err.response?.data?.message || 'Gagal menyimpan banner.'
    }
  } finally {
    bannerSaving.value = false
  }
}

async function removeBanner(b) {
  if (!confirm('Hapus banner ini?')) return
  await api.delete(`/admin/content/banners/${b.id}`)
  fetchBanners()
}

async function saveFaq() {
  await api.post('/admin/content/faqs', faqForm.value)
  faqForm.value = { question: '', answer: '' }
  showFaqForm.value = false
  fetchFaqs()
}

async function removeFaq(f) {
  if (!confirm('Hapus FAQ ini?')) return
  await api.delete(`/admin/content/faqs/${f.id}`)
  fetchFaqs()
}

function onImgError(e) {
  e.target.src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 200"><rect fill="%23f1f5f9" width="400" height="200"/><text x="200" y="105" text-anchor="middle" fill="%2394a3b8" font-family="Arial" font-size="14">Gambar tidak tersedia</text></svg>'
}

onMounted(() => { fetchBanners(); fetchFaqs() })
</script>

<style scoped>
.input { @apply w-full border border-ink/15 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none transition; }
.tab-btn { @apply px-4 py-2 rounded-full text-sm font-medium text-ink/60 border border-ink/10; }
.tab-active { @apply bg-primary text-white border-primary; }
</style>
