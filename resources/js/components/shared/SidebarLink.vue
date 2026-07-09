<template>
  <router-link :to="to" custom v-slot="{ isActive, navigate }">
    <a
      @click="navigate"
      href="#"
      class="flex items-center gap-3 px-5 py-2.5 cursor-pointer transition-colors duration-150 text-sm"
      :class="isActive || (exact && $route.path === to)
        ? 'bg-white/10 text-white border-r-2 border-accent font-medium'
        : 'text-white/55 hover:bg-white/5 hover:text-white/90'"
    >
      <!-- Ikon monokrom (currentColor) - otomatis mengikuti warna teks di atas, tidak berwarna sendiri -->
      <component :is="icon" class="w-5 h-5 shrink-0" stroke-width="1.75" />
      <span>{{ label }}</span>
    </a>
  </router-link>
</template>

<script setup>
defineProps({
  to: { type: String, required: true },
  icon: { type: [Object, Function], required: true },
  label: { type: String, required: true },
  exact: { type: Boolean, default: false },
})
</script>
