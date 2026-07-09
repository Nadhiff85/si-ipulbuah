<template>
  <div class="border border-ink/15 rounded-lg overflow-hidden">
    <div class="flex gap-1 bg-ink/5 border-b border-ink/10 p-1.5">
      <button type="button" @mousedown.prevent="exec('bold')" class="toolbar-btn font-bold">B</button>
      <button type="button" @mousedown.prevent="exec('italic')" class="toolbar-btn italic">I</button>
      <button type="button" @mousedown.prevent="exec('underline')" class="toolbar-btn underline">U</button>
      <button type="button" @mousedown.prevent="exec('insertUnorderedList')" class="toolbar-btn">• List</button>
      <button type="button" @mousedown.prevent="exec('formatBlock', 'H3')" class="toolbar-btn">H3</button>
      <button type="button" @mousedown.prevent="exec('formatBlock', 'P')" class="toolbar-btn">P</button>
    </div>
    <div
      ref="editor"
      contenteditable="true"
      class="p-3 text-sm min-h-[150px] focus:outline-none"
      @input="onInput"
      v-html="modelValue"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({ modelValue: { type: String, default: '' } })
const emit = defineEmits(['update:modelValue'])
const editor = ref(null)

function exec(command, value = null) {
  document.execCommand(command, false, value)
  editor.value?.focus()
}

function onInput() {
  emit('update:modelValue', editor.value.innerHTML)
}

onMounted(() => {
  if (editor.value && props.modelValue) editor.value.innerHTML = props.modelValue
})
</script>

<style scoped>
.toolbar-btn {
  @apply px-2.5 py-1 text-xs rounded hover:bg-ink/10 text-ink/70;
}
</style>
