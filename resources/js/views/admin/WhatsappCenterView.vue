<template>
  <div>
    <h2 class="font-semibold text-ink text-[15px] mb-2">WhatsApp Center</h2>
    <p class="text-ink/55 text-sm mb-5">Chat dengan pelanggan & kirim notifikasi broadcast</p>

    <div class="bg-white rounded-xl2 border border-ink/5 p-8 text-center text-ink/50">
      <ChatBubbleLeftRightIcon class="w-10 h-10 mx-auto mb-3 text-ink/30" stroke-width="1.5" />
      <p class="text-sm">Integrasi WhatsApp API (WaBlas) akan aktif setelah kredensial API dikonfigurasi di Pengaturan Sistem oleh Superadmin.</p>
      <router-link to="/admin/pengaturan-toko" class="text-primary text-sm font-medium hover:underline mt-2 inline-block">
        Buka Pengaturan Toko →
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline'
</script>
