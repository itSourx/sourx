<template>
  <img :src="iconSrc" alt="Document Icon" class="w-6 h-6 mr-2" />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  fileName: {
    type: String,
    required: true
  },
  isFolder: {
    type: Boolean,
    default: false
  }
})

const useDocumentIcon = (fileName, isFolder) => {
  if (isFolder) {
    return '/src/assets/DocumentIcons/folder_document_icon.svg' // Icône pour les dossiers
  }

  const fileExtension = fileName.split('.').pop().toLowerCase()

  switch (fileExtension) {
    case 'pdf':
      return '/src/assets/DocumentIcons/pdf.png'
    case 'doc':
    case 'docx':
      return '/src/assets/DocumentIcons/doc.png'
    case 'xls':
    case 'xlsx':
      return '/src/assets/DocumentIcons/xls.png'
    case 'ppt':
    case 'pptx':
      return '/src/assets/DocumentIcons/office.png'
    case 'txt':
      return '/src/assets/DocumentIcons/txt.png'
    default:
      return '/src/assets/DocumentIcons/default.png'
  }
}

const iconSrc = computed(() => {
  return useDocumentIcon(props.fileName, props.isFolder)
})
</script>
