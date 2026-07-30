<template>
  <div class="flex items-center justify-center gap-2 sm:gap-2.5" @paste="handlePaste">
    <input
      v-for="i in length"
      :key="i"
      :ref="(el) => (boxes[i - 1] = el)"
      v-model="digits[i - 1]"
      type="text"
      inputmode="numeric"
      autocomplete="one-time-code"
      maxlength="1"
      :disabled="disabled"
      class="otp-box w-11 h-12 sm:w-12 sm:h-14 text-center text-xl font-bold rounded-xl border-2 bg-white focus:outline-none transition disabled:opacity-50"
      :class="error ? 'border-danger text-danger' : 'border-ink/15 focus:border-primary text-ink'"
      @input="onInput(i - 1, $event)"
      @keydown="onKeydown(i - 1, $event)"
    />
  </div>
</template>

<script setup>
import { reactive, nextTick, onMounted } from 'vue'

const props = defineProps({
  length: { type: Number, default: 6 },
  modelValue: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  error: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue', 'complete'])

const boxes = reactive([])
const digits = reactive(Array.from({ length: props.length }, (_, i) => props.modelValue[i] || ''))

function syncValue() {
  const value = digits.join('')
  emit('update:modelValue', value)
  if (value.length === props.length) emit('complete', value)
}

function onInput(i, e) {
  const val = e.target.value.replace(/[^0-9]/g, '')
  digits[i] = val.slice(-1)
  syncValue()
  if (digits[i] && i < props.length - 1) {
    nextTick(() => boxes[i + 1]?.focus())
  }
}

function onKeydown(i, e) {
  if (e.key === 'Backspace' && !digits[i] && i > 0) {
    nextTick(() => boxes[i - 1]?.focus())
  }
}

function handlePaste(e) {
  const text = (e.clipboardData?.getData('text') || '').replace(/[^0-9]/g, '')
  if (!text) return
  e.preventDefault()
  for (let i = 0; i < props.length; i++) digits[i] = text[i] || ''
  syncValue()
  const lastFilled = Math.min(text.length, props.length) - 1
  nextTick(() => boxes[Math.max(lastFilled, 0)]?.focus())
}

function focusFirst() {
  nextTick(() => boxes[0]?.focus())
}

function clear() {
  for (let i = 0; i < props.length; i++) digits[i] = ''
  emit('update:modelValue', '')
  focusFirst()
}

onMounted(focusFirst)
defineExpose({ focusFirst, clear })
</script>

<style scoped>
.otp-box::-webkit-outer-spin-button,
.otp-box::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
</style>
