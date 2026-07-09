<template>
  <router-link :to="to" class="bg-white rounded-xl2 border border-ink/5 p-4 hover:shadow-sm hover:border-primary/20 transition">
    <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center mb-2.5">
      <component :is="icon" class="w-5 h-5 text-primary" stroke-width="1.75" />
    </div>
    <p class="font-medium text-sm text-ink">{{ label }}</p>
    <p class="text-xs text-ink/45 mt-0.5">{{ desc }}</p>
  </router-link>
</template>

<script setup>
defineProps({ to: String, icon: [Object, Function], label: String, desc: String })
</script>
