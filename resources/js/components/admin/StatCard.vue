<template>
  <div class="glass-card rounded-xl2 p-4">
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

const colorMap = {
  primary: { bg: 'bg-primary/10', text: 'text-primary' },
  accent: { bg: 'bg-accent/10', text: 'text-accent' },
  badge: { bg: 'bg-badge/15', text: 'text-badge' },
  danger: { bg: 'bg-danger/10', text: 'text-danger' },
}

const colorClass = computed(() => colorMap[props.color]?.text || 'text-ink/60')

const bgClass = computed(() => colorMap[props.color]?.bg || 'bg-ink/5')
</script>