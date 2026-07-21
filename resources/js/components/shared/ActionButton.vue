<template>
  <button
    @click="$emit('click')"
    type="button"
    class="text-xs font-medium px-3 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer"
    :class="colorClass"
  >
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

// variant menentukan warna latar tombol sesuai makna aksinya:
// primary (netral/edit), success (aktifkan/konfirmasi), accent (peringatan/reset), danger (hapus/tolak)
const props = defineProps({
  variant: { type: String, default: 'primary' },
})

const colorClass = computed(() => ({
  primary: 'bg-primary/10 text-primary hover:bg-primary/20',
  success: 'bg-success/15 text-success hover:bg-success/25',
  accent: 'bg-accent/10 text-accent hover:bg-accent/20',
  danger: 'bg-danger/10 text-danger hover:bg-danger/20',
}[props.variant] || 'bg-ink/5 text-ink/60 hover:bg-ink/10'))
</script>
