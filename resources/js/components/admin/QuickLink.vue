<template>
  <router-link :to="to" class="glass-card rounded-xl2 p-4 hover:shadow-md hover:border-primary/20 transition cursor-pointer">
    <div class="w-9 h-9 rounded-lg bg-ink/5 flex items-center justify-center mb-2.5">
      <component :is="icon" class="w-5 h-5 text-ink/60" stroke-width="1.75" />
    </div>
    <p class="font-medium text-sm text-ink">{{ label }}</p>
    <p class="text-xs text-ink/45 mt-0.5">{{ desc }}</p>
  </router-link>
</template>

<script setup>
defineProps({ to: String, icon: [Object, Function], label: String, desc: String })
</script>