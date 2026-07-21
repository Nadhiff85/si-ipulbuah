<template>
  <Transition name="toast-fade">
    <div
      v-if="toast.visible"
      class="fixed bottom-5 left-1/2 -translate-x-1/2 z-[100] max-w-sm w-[calc(100%-2.5rem)] sm:w-auto"
      role="alert"
    >
      <div
        class="glass-card rounded-xl2 px-4 py-3 flex items-start gap-2.5 shadow-lg"
        :class="borderClass"
      >
        <ExclamationTriangleIcon v-if="toast.type === 'error'" class="w-5 h-5 shrink-0 text-danger" stroke-width="1.75" />
        <CheckCircleIcon v-else-if="toast.type === 'success'" class="w-5 h-5 shrink-0 text-success" stroke-width="1.75" />
        <InformationCircleIcon v-else class="w-5 h-5 shrink-0 text-primary" stroke-width="1.75" />
        <p class="text-sm text-ink flex-1">{{ toast.message }}</p>
        <button @click="toast.hide()" class="shrink-0 text-ink/40 hover:text-ink cursor-pointer" aria-label="Tutup notifikasi">
          <XMarkIcon class="w-4 h-4" stroke-width="1.75" />
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'
import { useToastStore } from '../../stores/toast'
import { ExclamationTriangleIcon, CheckCircleIcon, InformationCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const toast = useToastStore()

const borderClass = computed(() => ({
  error: 'border-danger/30',
  success: 'border-success/30',
  info: 'border-primary/30',
}[toast.type] || ''))
</script>

<style scoped>
.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translate(-50%, 8px);
}
</style>
