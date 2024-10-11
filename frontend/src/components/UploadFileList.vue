<template>
  <div class="border border-gray-light bg-white shadow-xs rounded-md p-4 mb-4 flex items-center justify-between">
    <div class="flex-shrink-0 mr-4">
      <DocumentIcon :fileName="fileName" class="text-xl mr-4" />
    </div>
    <div class="flex-grow">
      {{ fileName }}
    </div>
    <button @click="removeFile(fileName)" class="text-red focus:outline-none">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
        stroke-linejoin="round">
        <path d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  fileName: {
    type: String,
    required: true
  },
  removeFile: Function
})
</script>
