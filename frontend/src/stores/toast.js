import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const message = ref('')
  const type = ref('success')
  const visible = ref(false)
  let timer = null

  function show(msg, kind = 'success') {
    message.value = msg
    type.value = kind
    visible.value = true
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => {
      visible.value = false
    }, 4000)
  }

  return { message, type, visible, show }
})
