<template>
  <div class="mt-5 first:mt-0">
    <p class="px-5 text-[10px] font-semibold uppercase tracking-wider text-white/30 mb-1.5">{{ title }}</p>
    <slot />
  </div>
</template>

<script setup>
defineProps({ title: { type: String, required: true } })
</script>
