<template>
  <div :class="statusClass" class="flex items-center status-tag text-xs">
    <component v-if="icon" :is="icon" class="mr-2 w-5 h-5" />
    {{ status }}
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { CircleX, CircleCheckBig, Hourglass, Send } from 'lucide-vue-next'

const props = defineProps({
  status: {
    type: String,
    required: true
  }
})

const statusClass = computed(() => {
  let bgClass = ''

  switch (props.status) {
    case 'Refusée':
      bgClass = 'bg-red text-white'
      break
    case 'Approuvée':
      bgClass = 'bg-green text-white'
      break
    case 'Soumis':
      bgClass = 'bg-oxford-blue text-white' 
      break
    default:
      bgClass = 'bg-yellow text-white'
      break
  }

  return `flex items-center px-4 py-2 rounded-full ${bgClass}`
})

const icon = computed(() => {
  switch (props.status) {
    case 'Refusée':
      return CircleX
    case 'Approuvée':
      return CircleCheckBig
    case 'Soumis':
      return Send 
    default:
      return Hourglass
  }
})
</script>
