<template>
  <div class="w-28 h-28 md:w-36 md:h-36 rounded-2xl overflow-hidden shrink-0 bg-white/70 shadow-sm flex items-center justify-center">
    <img
      v-if="!failed"
      :src="`/images/marquee/${fruit.file}`"
      :alt="fruit.name"
      class="w-full h-full object-cover"
      @error="failed = true"
    />
    <span v-else class="text-5xl md:text-6xl">{{ fruit.emoji }}</span>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// fruit: { name, file, emoji } - lihat daftar & lokasi foto di HomeView.vue
defineProps({ fruit: { type: Object, required: true } })

// Kalau file foto belum ada di public/images/marquee/, otomatis tampil emoji
// sebagai pengganti sementara - tidak akan menampilkan ikon "gambar rusak".
const failed = ref(false)
</script>
