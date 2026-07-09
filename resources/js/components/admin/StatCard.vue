<template>
  <div class="bg-white rounded-xl2 border border-ink/5 p-4">
    <div class="flex items-center justify-between mb-3">
      <p class="text-xs text-ink/45">{{ label }}</p>
      <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="bgClass">
        <component :is="icon" class="w-4.5 h-4.5" :class="colorClass" stroke-width="1.75" />
      </div>
    </div>
    <p class="text-xl font-semibold text-ink">{{ value }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({ label: String, value: [String, Number], icon: [Object, Function], color: String })

const colorClass = computed(() => 'text-ink/60')

const bgClass = computed(() => 'bg-ink/5')
</script>