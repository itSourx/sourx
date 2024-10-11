<template>
  <div class="flex items-center text-xs text-white">
    <BadgeCheck class="w-4 h-4 text-white mx-1" />
    {{ role }}
  </div>
</template>

<script setup>
import { BadgeCheck } from 'lucide-vue-next'
const props = defineProps({
  role: {
    type: String,
    required: true
  }
})
</script>
